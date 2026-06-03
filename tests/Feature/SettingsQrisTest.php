<?php

namespace Tests\Feature;

use App\Actions\CreateTransaction;
use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Filament\Pages\Pengaturan;
use App\Livewire\Cashier;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsQrisTest extends TestCase
{
    use RefreshDatabase;

    private function user(UserRole $role): User
    {
        return User::create([
            'name' => $role->value, 'email' => uniqid() . '@test.local',
            'password' => Hash::make('password'), 'role' => $role, 'is_active' => true,
        ]);
    }

    public function test_setting_dapat_disimpan_dan_dibaca(): void
    {
        Setting::set('store_name', 'Toko Maju');
        $this->assertEquals('Toko Maju', Setting::get('store_name'));

        // Perubahan nilai harus tercermin (cache ikut diperbarui).
        Setting::set('store_name', 'Toko Jaya');
        $this->assertEquals('Toko Jaya', Setting::get('store_name'));

        // Default dipakai bila key belum ada.
        $this->assertEquals('x', Setting::get('belum_ada', 'x'));
    }

    public function test_halaman_pengaturan_hanya_untuk_admin(): void
    {
        $this->actingAs($this->user(UserRole::Admin))->get('/admin/pengaturan')->assertOk();
        $this->actingAs($this->user(UserRole::Kasir))->get('/admin/pengaturan')->assertForbidden();
    }

    public function test_admin_dapat_menyimpan_nama_toko_lewat_halaman_pengaturan(): void
    {
        Livewire::actingAs($this->user(UserRole::Admin))
            ->test(Pengaturan::class)
            ->set('data.store_name', 'Warung Kita')
            ->call('save');

        $this->assertEquals('Warung Kita', Setting::get('store_name'));
    }

    public function test_checkout_qris_menetapkan_uang_dibayar_sama_dengan_total(): void
    {
        $cat = Category::create(['name' => 'Umum']);
        $product = Product::create([
            'category_id' => $cat->id, 'name' => 'Barang', 'price' => 7500, 'stock' => 10, 'is_active' => true,
        ]);

        Livewire::actingAs($this->user(UserRole::Kasir))
            ->test(Cashier::class)
            ->call('addToCart', $product->id)        // total 7500
            ->set('paymentMethod', 'qris')
            ->call('checkout')                        // tanpa input uang
            ->assertSet('showSuccess', true);

        // Non-tunai: uang dibayar = total, kembalian 0.
        $trx = \App\Models\Transaction::first();
        $this->assertEquals(7500, $trx->total);
        $this->assertEquals(7500, $trx->paid);
        $this->assertEquals(0, $trx->change);
        $this->assertEquals(PaymentMethod::Qris, $trx->payment_method);
    }
}
