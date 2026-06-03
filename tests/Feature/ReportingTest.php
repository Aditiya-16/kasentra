<?php

namespace Tests\Feature;

use App\Actions\CreateTransaction;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Filament\Pages\Laporan;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ReportingTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        return User::create([
            'name' => $role->value . '-' . uniqid(),
            'email' => uniqid() . '@test.local',
            'password' => Hash::make('password'),
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function transaksi(User $kasir): Transaction
    {
        $cat = Category::create(['name' => 'Umum ' . uniqid()]);
        $product = Product::create([
            'category_id' => $cat->id, 'name' => 'Produk ' . uniqid(),
            'price' => 5000, 'stock' => 50, 'is_active' => true,
        ]);

        return (new CreateTransaction)->handle($kasir, [['id' => $product->id, 'qty' => 2]], 10000, PaymentMethod::Tunai);
    }

    public function test_struk_pdf_dapat_diakses_oleh_kasir_pembuat(): void
    {
        $kasir = $this->user(UserRole::Kasir);
        $trx = $this->transaksi($kasir);

        $this->actingAs($kasir)
            ->get(route('receipt', $trx))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_struk_pdf_ditolak_untuk_kasir_lain(): void
    {
        $pembuat = $this->user(UserRole::Kasir);
        $trx = $this->transaksi($pembuat);

        $this->actingAs($this->user(UserRole::Kasir)) // kasir berbeda
            ->get(route('receipt', $trx))
            ->assertForbidden();
    }

    public function test_admin_boleh_mengakses_struk_siapa_pun(): void
    {
        $trx = $this->transaksi($this->user(UserRole::Kasir));

        $this->actingAs($this->user(UserRole::Admin))
            ->get(route('receipt', $trx))
            ->assertOk();
    }

    public function test_dashboard_dan_laporan_dapat_dibuka_admin(): void
    {
        $admin = $this->user(UserRole::Admin);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/laporan')->assertOk();
        $this->actingAs($admin)->get('/admin/transactions')->assertOk();
    }

    public function test_laporan_menghitung_omzet_dan_transaksi_pada_rentang(): void
    {
        $kasir = $this->user(UserRole::Kasir);
        $this->transaksi($kasir); // total 10.000
        $this->transaksi($kasir); // total 10.000

        Livewire::actingAs($this->user(UserRole::Admin))
            ->test(Laporan::class)
            ->assertSet('dari', today()->startOfMonth()->toDateString())
            ->assertSee('20.000')   // total omzet 2 × 10.000
            ->assertSee('Produk Terlaris');
    }
}
