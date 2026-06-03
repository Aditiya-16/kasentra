<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(UserRole $role, bool $active = true): User
    {
        return User::create([
            'name' => $role->value,
            'email' => $role->value . '@test.local',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => $active,
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_kasir_tidak_boleh_masuk_panel(): void
    {
        $this->actingAs($this->makeUser(UserRole::Kasir));

        // canAccessPanel() menolak kasir -> 403.
        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_nonaktif_tidak_boleh_masuk_panel(): void
    {
        $this->actingAs($this->makeUser(UserRole::Admin, active: false));

        $this->get('/admin')->assertForbidden();
    }

    public function test_user_login_yang_membuka_login_dipantulkan_sesuai_peran(): void
    {
        // Kasir yang sudah login -> /kasir; admin -> /admin (bukan kembali ke landing).
        $this->actingAs($this->makeUser(UserRole::Kasir))
            ->get('/login')->assertRedirect(route('kasir'));

        $this->actingAs($this->makeUser(UserRole::Admin))
            ->get('/login')->assertRedirect('/admin');
    }

    public function test_admin_bisa_membuka_panel_dan_semua_resource(): void
    {
        $this->actingAs($this->makeUser(UserRole::Admin));

        $this->get('/admin')->assertSuccessful();
        $this->get('/admin/products')->assertSuccessful();
        $this->get('/admin/categories')->assertSuccessful();
        $this->get('/admin/users')->assertSuccessful();
    }
}
