<div align="center">

# 🛒 Kasentra

### Sistem Point of Sale (POS) / Kasir untuk Toko Ritel

Aplikasi web kasir yang **cepat di meja kasir, mudah dipahami pemilik toko, dan menghasilkan laporan tanpa hitung manual.** Dibuat untuk warung, toko kelontong, kedai, dan butik skala kecil–menengah.

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-4-FDAE4B?logo=laravel&logoColor=white)](https://filamentphp.com)
[![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-22C55E.svg)](LICENSE)

### [🔗 Lihat Demo Langsung](https://kasentra-production.up.railway.app)

</div>

---

## ✨ Sorotan Fitur

Kasentra punya **dua wajah**: panel admin yang lengkap untuk pemilik toko, dan halaman kasir yang ringan & real-time untuk transaksi cepat.

### 🧑‍💼 Untuk Admin / Pemilik
- **Dashboard ringkas** — omzet hari ini, jumlah transaksi, dan produk terlaris dalam sekejap.
- **Manajemen Produk** — CRUD lengkap (nama, SKU, kategori, harga, stok, foto), indikator **stok menipis**, pencarian & filter.
- **Manajemen Kategori** & **Manajemen Pengguna** (buat/edit/nonaktifkan akun kasir).
- **Laporan Penjualan** per rentang tanggal — omzet, jumlah transaksi, produk terlaris.
- **Ekspor Excel** profesional (`.xlsx`) berisi 4 sheet + **grafik** (pie metode bayar, line tren harian, bar produk terlaris).
- **Pengaturan Toko** — unggah gambar QRIS tanpa menyentuh kode.

### 🧾 Untuk Kasir
- **Halaman kasir real-time** (Livewire) — pencarian produk cepat, klik untuk tambah ke keranjang.
- Atur qty, hapus item, **total & kembalian terhitung otomatis**.
- Metode pembayaran: **Tunai / QRIS / Transfer**.
- Selesaikan transaksi → **stok berkurang otomatis**, nomor invoice unik dibuat, keranjang reset.
- **Cetak struk PDF** (80mm, format kertas termal) — bisa di-print atau di-download.

---

## 🔐 Keamanan Bawaan

Kasentra dibangun dengan praktik keamanan sejak awal:

- **Role-based access** — Admin & Kasir punya akses berbeda; panel admin hanya untuk Admin aktif (`canAccessPanel`).
- **Rate limiting login** (5 percobaan / email+IP) + regenerasi sesi (anti session-fixation) + pesan error generik (anti user-enumeration).
- **Anti-IDOR** pada struk — hanya admin atau kasir pemilik transaksi yang bisa melihatnya.
- **Validasi upload** ketat (hanya `jpeg/png/webp`, batas ukuran) — menutup celah SVG-XSS.
- **Snapshot harga & nama produk** di setiap item transaksi → riwayat tetap akurat meski harga diubah.

---

## 🧰 Tech Stack

| Lapisan | Teknologi |
|---------|-----------|
| Backend | **Laravel 13** (PHP 8.3) |
| Panel Admin | **Filament 4** |
| Halaman Kasir & Login | **Livewire 3** |
| Styling | **Tailwind CSS 4** + Vite |
| Database | **MySQL** |
| Laporan Excel | **PhpSpreadsheet** |
| Struk PDF | **barryvdh/laravel-dompdf** |
| Animasi landing | GSAP · Three.js · Lottie |

---

## 🚀 Instalasi Lokal

### Prasyarat
- PHP **8.3+** (dengan ekstensi `zip`, `gd`)
- Composer
- Node.js & npm
- MySQL (mis. via [Laragon](https://laragon.org/) / XAMPP)

### Langkah

```bash
# 1. Clone repository
git clone https://github.com/Aditiya-16/kasentra.git
cd kasentra

# 2. Install dependency PHP & JS
composer install
npm install

# 3. Siapkan environment
cp .env.example .env
php artisan key:generate
```

Edit `.env` dan sesuaikan koneksi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasentra
DB_USERNAME=root
DB_PASSWORD=

# (opsional) password akun awal — kosongkan untuk pakai default dev
SEED_ADMIN_PASSWORD=
SEED_KASIR_PASSWORD=
```

```bash
# 4. Migrasi + isi data awal
php artisan migrate --seed

# 5. Symlink storage (agar gambar QRIS & produk tampil)
php artisan storage:link

# 6. Build asset & jalankan
npm run build
php artisan serve
```

Buka **http://localhost:8000** 🎉

> 💡 Untuk mode pengembangan dengan hot-reload, jalankan `composer dev` (menjalankan server, queue, dan Vite sekaligus).

---

## 🔑 Akun Demo (hasil seeder)

| Peran | Email | Password |
|-------|-------|----------|
| Admin | `admin@kasentra.test` | `password` |
| Kasir | `kasir@kasentra.test` | `password` |

- **Admin** masuk ke panel `/admin`.
- **Kasir** masuk ke halaman `/kasir`.

> ⚠️ **Ganti password ini sebelum deploy ke produksi.** Seeder otomatis **menolak** password default di environment produksi — set `SEED_ADMIN_PASSWORD` & `SEED_KASIR_PASSWORD` di `.env`.

---

## 🗺️ Rute Utama

| Rute | Akses | Keterangan |
|------|-------|-----------|
| `/` | Publik | Landing page |
| `/login` | Tamu | Halaman login |
| `/kasir` | Terautentikasi | Halaman kasir (Livewire) |
| `/admin` | Admin aktif | Panel Filament |
| `/struk/{transaction}` | Admin / pemilik transaksi | Struk PDF |

---

## 🗄️ Model Data

```
users ──< transactions ──< transaction_items >── products >── categories
```

- **transaction_items** menyimpan *snapshot* `product_name` & `price` agar riwayat tetap akurat meski produk berubah.
- **settings** menyimpan konfigurasi toko (mis. gambar QRIS) sebagai key–value.

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi [MIT](LICENSE).

<div align="center">

Dibuat dengan ❤️ menggunakan Laravel & Filament

</div>
