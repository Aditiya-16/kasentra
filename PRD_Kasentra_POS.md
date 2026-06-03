# Product Requirements Document (PRD)
# Kasentra — Sistem POS / Kasir Sederhana

**Versi:** 1.0
**Tech Stack:** Laravel 11 + Livewire 3 + Filament 3 + Tailwind CSS + MySQL
**Status:** MVP (Minimum Viable Product)

---

## 1. Ringkasan Produk

**Kasentra** adalah aplikasi web Point of Sale (POS) sederhana untuk toko ritel skala kecil–menengah (warung, toko kelontong, kedai, butik). Aplikasi ini membantu pemilik toko dan kasir mencatat produk, memproses transaksi penjualan dengan cepat, dan melihat laporan penjualan secara otomatis.

Fokus produk: **cepat dipakai di meja kasir, mudah dipahami pemilik toko, dan menghasilkan laporan tanpa hitung manual.**

---

## 2. Tujuan Produk

1. Kasir bisa menyelesaikan satu transaksi penjualan dalam waktu di bawah 30 detik.
2. Pemilik toko bisa melihat omzet harian tanpa menghitung manual.
3. Stok produk berkurang otomatis setiap kali terjadi penjualan.
4. Tidak memerlukan pelatihan khusus untuk digunakan (intuitif).

---

## 3. Peran Pengguna (User Roles)

| Peran | Hak Akses |
|-------|-----------|
| **Admin / Pemilik** | Kelola produk, kategori, pengguna, lihat semua laporan, akses panel admin penuh, dan bisa melakukan transaksi. |
| **Kasir** | Hanya akses halaman kasir (transaksi) dan melihat riwayat transaksinya sendiri. Tidak bisa mengubah produk/harga. |

---

## 4. Fitur & Requirement

### 4.1 Autentikasi & Manajemen Pengguna
- Login dengan email + password.
- Logout.
- Role-based access: Admin dan Kasir punya tampilan/akses berbeda.
- Admin dapat membuat, mengedit, dan menonaktifkan akun kasir.

### 4.2 Manajemen Kategori Produk (Admin)
- CRUD kategori (nama kategori).
- Satu produk wajib punya satu kategori.

### 4.3 Manajemen Produk (Admin)
- CRUD produk dengan field: nama, SKU/barcode (opsional), kategori, harga jual, stok, foto (opsional), status aktif/nonaktif.
- Validasi: harga dan stok tidak boleh negatif.
- Pencarian dan filter produk berdasarkan nama/kategori.
- Indikator stok menipis (misalnya stok < 5 ditandai).

### 4.4 Halaman Kasir / Transaksi (Inti)
Ini fitur paling penting. Harus cepat dan ringan.
- Pencarian produk cepat (ketik nama atau scan/ketik barcode).
- Klik produk untuk menambah ke keranjang.
- Atur jumlah (qty) tiap item; bisa menghapus item dari keranjang.
- Total harga terhitung otomatis (qty × harga).
- Input jumlah uang dibayar pelanggan → **kembalian terhitung otomatis**.
- Pilih metode pembayaran (Tunai / QRIS / Transfer — minimal Tunai untuk MVP).
- Tombol "Bayar / Selesaikan Transaksi".
- Setelah transaksi selesai:
  - Stok produk berkurang otomatis sesuai qty.
  - Transaksi tersimpan dengan nomor invoice unik.
  - Tampilkan struk yang bisa dicetak (print) atau disimpan PDF.
  - Keranjang dikosongkan untuk transaksi berikutnya.

### 4.5 Riwayat Transaksi
- Daftar semua transaksi (tanggal, nomor invoice, kasir, total).
- Detail tiap transaksi (item yang dibeli, qty, subtotal, pembayaran, kembalian).
- Filter berdasarkan rentang tanggal.

### 4.6 Laporan Penjualan (Admin)
- **Dashboard ringkas:** omzet hari ini, jumlah transaksi hari ini, produk terlaris.
- Laporan penjualan per rentang tanggal (harian/bulanan).
- Total omzet dan jumlah transaksi pada rentang tersebut.
- Daftar produk terlaris.
- Ekspor laporan ke Excel/CSV (opsional, bagus untuk nilai jual).

### 4.7 Cetak Struk
- Format struk: nama toko, tanggal/jam, nomor invoice, daftar item + qty + harga, total, dibayar, kembalian, nama kasir.
- Bisa di-print atau di-download sebagai PDF.

---

## 5. Alur Utama: Proses Transaksi (Happy Path)

1. Kasir login → masuk ke halaman kasir.
2. Kasir cari/pilih produk → produk masuk keranjang.
3. Kasir sesuaikan qty bila perlu → total terupdate otomatis.
4. Kasir masukkan jumlah uang dibayar pelanggan.
5. Sistem menghitung dan menampilkan kembalian.
6. Kasir klik "Bayar".
7. Sistem menyimpan transaksi, mengurangi stok, dan menampilkan struk.
8. Keranjang reset, siap untuk transaksi berikutnya.

---

## 6. Model Data (Skema Database)

**users**
- id, name, email, password, role (enum: admin/kasir), is_active, timestamps

**categories**
- id, name, timestamps

**products**
- id, category_id (FK), name, sku (nullable), price (decimal), stock (integer), image (nullable), is_active (boolean), timestamps

**transactions**
- id, invoice_number (unik), user_id (FK, kasir), total (decimal), paid (decimal), change (decimal), payment_method (enum), timestamps

**transaction_items**
- id, transaction_id (FK), product_id (FK), product_name (snapshot), price (snapshot), qty (integer), subtotal (decimal), timestamps

> Catatan: `product_name` dan `price` di tabel transaction_items disimpan sebagai snapshot agar riwayat transaksi tetap akurat meskipun harga produk diubah di kemudian hari.

---

## 7. Spesifikasi Teknis

- **Backend:** Laravel 11
- **Panel Admin:** Filament 3 — untuk manajemen produk, kategori, pengguna, dan laporan (cepat dibangun).
- **Halaman Kasir:** Komponen **Livewire 3** custom (karena UI kasir butuh interaksi real-time tanpa reload).
- **Styling:** Tailwind CSS.
- **Database:** MySQL.
- **Autentikasi:** Laravel built-in (di-handle Filament untuk admin).
- **Cetak struk/laporan PDF:** library seperti `barryvdh/laravel-dompdf`.

---

## 8. Batasan MVP (Out of Scope untuk Versi 1.0)

Agar proyek cepat selesai dan tidak melebar, hal berikut **TIDAK** dikerjakan dulu:
- Multi-toko / multi-cabang.
- Integrasi pembayaran QRIS/payment gateway nyata (cukup pilihan metode manual).
- Manajemen supplier & pembelian stok (purchasing).
- Program diskon/promo/loyalty point.
- Aplikasi mobile.

Fitur-fitur ini bisa jadi "fase 2" untuk ditawarkan ke klien sebagai pengembangan lanjutan (peluang tambahan penghasilan).

---

## 9. Kriteria Selesai (Definition of Done)

Proyek dianggap selesai untuk MVP jika:
- [ ] Admin & kasir bisa login sesuai perannya.
- [ ] Admin bisa CRUD produk & kategori.
- [ ] Kasir bisa menyelesaikan transaksi penuh dari pilih produk sampai cetak struk.
- [ ] Stok berkurang otomatis setelah transaksi.
- [ ] Dashboard menampilkan omzet & transaksi hari ini.
- [ ] Laporan penjualan per tanggal bisa ditampilkan.
- [ ] Aplikasi sudah di-deploy dan bisa diakses lewat link hidup.
