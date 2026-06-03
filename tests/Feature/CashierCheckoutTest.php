<?php

namespace Tests\Feature;

use App\Actions\CreateTransaction;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Livewire\Cashier;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CashierCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function kasir(): User
    {
        return User::create([
            'name' => 'Kasir', 'email' => 'k@test.local',
            'password' => Hash::make('password'),
            'role' => UserRole::Kasir, 'is_active' => true,
        ]);
    }

    private function product(int $stock = 10, float $price = 5000): Product
    {
        $cat = Category::create(['name' => 'Umum ' . uniqid()]);

        return Product::create([
            'category_id' => $cat->id, 'name' => 'Produk ' . uniqid(),
            'price' => $price, 'stock' => $stock, 'is_active' => true,
        ]);
    }

    public function test_checkout_menyimpan_transaksi_mengurangi_stok_dan_menghitung_kembalian(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(stock: 10, price: 5000);

        $trx = (new CreateTransaction)->handle(
            cashier: $kasir,
            cart: [['id' => $product->id, 'qty' => 3]],
            paid: 20000,
            method: PaymentMethod::Tunai,
        );

        // Total = 3 × 5000 = 15000, kembalian = 20000 − 15000 = 5000.
        $this->assertEquals(15000, $trx->total);
        $this->assertEquals(5000, $trx->change);
        // Invoice unik berformat INV-YYYYMMDD-xxxxx.
        $this->assertStringStartsWith('INV-', $trx->invoice_number);

        // Stok berkurang otomatis: 10 − 3 = 7.
        $this->assertEquals(7, $product->fresh()->stock);

        // Item tersimpan dengan snapshot nama & harga.
        $this->assertDatabaseHas('transaction_items', [
            'transaction_id' => $trx->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'qty' => 3,
            'subtotal' => 15000,
        ]);
    }

    public function test_invoice_selalu_unik(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(stock: 10);

        $a = (new CreateTransaction)->handle($kasir, [['id' => $product->id, 'qty' => 1]], 5000, PaymentMethod::Tunai);
        $b = (new CreateTransaction)->handle($kasir, [['id' => $product->id, 'qty' => 1]], 5000, PaymentMethod::Tunai);

        $this->assertNotEquals($a->invoice_number, $b->invoice_number);
    }

    public function test_stok_kurang_ditolak_dan_tidak_mengubah_data(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(stock: 2, price: 5000);

        $this->expectException(ValidationException::class);

        try {
            (new CreateTransaction)->handle($kasir, [['id' => $product->id, 'qty' => 5]], 50000, PaymentMethod::Tunai);
        } finally {
            // Rollback: stok tetap 2, tidak ada transaksi tercipta.
            $this->assertEquals(2, $product->fresh()->stock);
            $this->assertEquals(0, Transaction::count());
        }
    }

    public function test_uang_dibayar_kurang_ditolak(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(stock: 10, price: 5000);

        $this->expectException(ValidationException::class);
        (new CreateTransaction)->handle($kasir, [['id' => $product->id, 'qty' => 2]], 5000, PaymentMethod::Tunai);
    }

    public function test_halaman_kasir_render_penuh_untuk_user_login(): void
    {
        // Menguji route + layout app + komponen Livewire dirender utuh via HTTP.
        $this->actingAs($this->kasir())
            ->get('/kasir')
            ->assertOk()
            ->assertSee('Keranjang');
    }

    public function test_komponen_livewire_checkout_mereset_keranjang_dan_memicu_event(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(stock: 10, price: 5000);

        Livewire::actingAs($kasir)
            ->test(Cashier::class)
            ->call('addToCart', $product->id)
            ->call('incrementQty', $product->id)   // qty = 2
            ->set('paid', '15000')
            ->call('checkout')
            ->assertSet('showSuccess', true)
            ->assertSet('cart', [])
            ->assertDispatched('transaction-success');

        $this->assertEquals(8, $product->fresh()->stock); // 10 − 2
        $this->assertEquals(1, Transaction::count());
    }
}
