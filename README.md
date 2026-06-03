<div align="center">

# Kasentra

**Point of Sale (POS) untuk toko ritel kecil–menengah.**

Catat produk, proses transaksi dalam hitungan detik, dan pantau penjualan tanpa hitung manual — untuk warung, toko kelontong, kedai, dan butik.

[**Buka Demo Langsung →**](https://kasentra-production.up.railway.app)

[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-4-FDAE4B?logo=laravel&logoColor=white)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-22C55E.svg)](LICENSE)

</div>

---

## Tentang

Kasentra adalah aplikasi kasir berbasis web dengan dua sisi: panel admin untuk pemilik toko mengelola produk dan membaca laporan, serta halaman kasir yang ringan dan real-time untuk melayani transaksi.

Dibangun mengikuti satu prinsip sederhana — kasir bisa menyelesaikan satu transaksi di bawah 30 detik, dan pemilik toko tahu omzetnya tanpa menghitung manual.

## Coba Demo Online

Demo berjalan di **[kasentra-production.up.railway.app](https://kasentra-production.up.railway.app)**. Login dan jelajahi kedua sisi aplikasi:

| Peran | Email | Password |
|-------|-------|----------|
| Admin | `admin@kasentra.test` | `admin12345` |
| Kasir | `kasir@kasentra.test` | `kasir12345` |

> Ini lingkungan demo berisi data contoh. Mohon tidak menyimpan data penting — isinya dapat direset sewaktu-waktu.

## Fitur

**Panel Admin**

- Dashboard omzet, jumlah transaksi, dan produk terlaris.
- Manajemen produk (SKU, harga, stok, foto) dengan indikator stok menipis, pencarian, dan filter.
- Manajemen kategori dan pengguna (kelola akun kasir).
- Laporan penjualan per rentang tanggal, lengkap dengan ekspor Excel (`.xlsx`) berisi grafik tren harian, komposisi metode bayar, dan produk terlaris.
- Pengaturan toko, termasuk unggah gambar QRIS langsung dari panel.

**Halaman Kasir**

- Pencarian produk cepat dan keranjang real-time tanpa reload halaman.
- Total dan kembalian dihitung otomatis.
- Metode pembayaran: tunai, QRIS, atau transfer.
- Stok berkurang otomatis setiap transaksi; nomor invoice dibuat unik.
- Cetak atau unduh struk PDF dengan format kertas termal 80mm.

## Teknologi

- **Laravel 13** (PHP 8.3)
- **Filament 4** — panel admin
- **Livewire 3** — halaman kasir & login
- **Tailwind CSS 4** + Vite
- **MySQL**
- **PhpSpreadsheet** untuk ekspor Excel, **dompdf** untuk struk PDF

## Menjalankan Secara Lokal

Prasyarat: PHP 8.3+ (dengan ekstensi `zip` dan `gd`), Composer, Node.js, dan MySQL — misalnya lewat [Laragon](https://laragon.org/).

```bash
git clone https://github.com/Aditiya-16/kasentra.git
cd kasentra

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_DATABASE=kasentra
DB_USERNAME=root
DB_PASSWORD=
```

Lalu jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Aplikasi berjalan di `http://localhost:8000`. Untuk pengembangan dengan hot-reload, gunakan `composer dev` (server, queue, dan Vite sekaligus).

## Akun Bawaan (Instalasi Lokal)

Setelah `migrate --seed`, dua akun tersedia di instalasi lokalmu:

| Peran | Email | Password |
|-------|-------|----------|
| Admin | `admin@kasentra.test` | `password` |
| Kasir | `kasir@kasentra.test` | `password` |

Untuk produksi, atur `SEED_ADMIN_PASSWORD` dan `SEED_KASIR_PASSWORD` di `.env` — seeder akan menolak password default di lingkungan produksi.

## Catatan Keamanan

- Akses berbasis peran; panel admin hanya untuk admin yang aktif.
- Rate limiting pada login, regenerasi sesi, dan pesan error generik untuk mencegah brute-force serta enumerasi akun.
- Otorisasi struk per transaksi untuk mencegah akses lintas pengguna.
- Unggah berkas dibatasi ke `jpeg/png/webp`.
- Harga dan nama produk disimpan sebagai snapshot di tiap transaksi agar riwayat tetap akurat meski produk berubah.

## Lisensi

Dirilis di bawah lisensi [MIT](LICENSE).
