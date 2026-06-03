<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Mengisi data awal Kasentra: 1 admin, 1 kasir, beberapa kategori,
     * dan ~10 produk dummy (PRD Tahap 1).
     */
    public function run(): void
    {
        // --- Akun pengguna -------------------------------------------------
        // Password diambil dari .env agar tidak ada kredensial default yang
        // ter-hardcode. Fallback 'password' hanya untuk kemudahan dev lokal.
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'password');
        $kasirPassword = env('SEED_KASIR_PASSWORD', 'password');

        // Cegah seeding akun berpassword default di produksi (account takeover).
        if (app()->isProduction() && ($adminPassword === 'password' || $kasirPassword === 'password')) {
            throw new \RuntimeException(
                'Seeding ditolak: set SEED_ADMIN_PASSWORD & SEED_KASIR_PASSWORD di .env sebelum seeding di produksi.'
            );
        }

        User::updateOrCreate(
            ['email' => 'admin@kasentra.test'],
            [
                'name' => 'Admin Kasentra',
                'password' => Hash::make($adminPassword),
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'kasir@kasentra.test'],
            [
                'name' => 'Kasir Kasentra',
                'password' => Hash::make($kasirPassword),
                'role' => UserRole::Kasir,
                'is_active' => true,
            ],
        );

        // --- Kategori ------------------------------------------------------
        $categories = collect(['Makanan', 'Minuman', 'Snack', 'Kebutuhan Rumah'])
            ->mapWithKeys(fn (string $name) => [
                $name => Category::firstOrCreate(['name' => $name]),
            ]);

        // --- Produk dummy --------------------------------------------------
        // Format: [nama, kategori, harga, stok, sku]. Sebagian stok sengaja
        // dibuat < 5 untuk menguji indikator "stok menipis" (PRD 4.3).
        $products = [
            ['Indomie Goreng', 'Makanan', 3500, 120, 'MIE-001'],
            ['Beras Premium 5kg', 'Kebutuhan Rumah', 68000, 15, 'BRS-005'],
            ['Minyak Goreng 1L', 'Kebutuhan Rumah', 18000, 3, 'MYK-001'],
            ['Teh Botol 350ml', 'Minuman', 4000, 80, 'MIN-010'],
            ['Air Mineral 600ml', 'Minuman', 3000, 200, 'MIN-006'],
            ['Kopi Sachet', 'Minuman', 2000, 4, 'MIN-002'],
            ['Chitato 68g', 'Snack', 9500, 40, 'SNK-068'],
            ['Biskuit Roma', 'Snack', 7000, 2, 'SNK-007'],
            ['Telur Ayam 1kg', 'Makanan', 28000, 25, 'TLR-001'],
            ['Gula Pasir 1kg', 'Kebutuhan Rumah', 15000, 30, 'GLA-001'],
            ['Sabun Mandi', 'Kebutuhan Rumah', 5000, 18, 'SBN-001'],
        ];

        foreach ($products as [$name, $categoryName, $price, $stock, $sku]) {
            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $categories[$categoryName]->id,
                    'name' => $name,
                    'price' => $price,
                    'stock' => $stock,
                    'is_active' => true,
                ],
            );
        }
    }
}
