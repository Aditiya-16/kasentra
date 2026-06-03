@php
    // Tujuan tombol CTA: kalau sudah login arahkan ke aplikasi sesuai peran,
    // kalau belum ke halaman login.
    $appTarget = auth()->check()
        ? (auth()->user()->isAdmin() ? '/admin' : route('kasir'))
        : route('login');
    $ctaLabel = auth()->check() ? 'Buka Aplikasi' : 'Masuk';
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasentra — POS Kasir Modern untuk Toko Anda</title>
    <meta name="description" content="Kasentra: aplikasi kasir cepat & ringan. Catat produk, proses transaksi di bawah 30 detik, lihat laporan otomatis.">

    {{-- Favicon Kasentra --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}">

    {{-- Cegah "flash of light mode": set kelas .dark SEBELUM render, sebelum CSS. --}}
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/landing.js'])
</head>
<body class="overflow-x-hidden">

    {{-- ===================== NAVBAR ===================== --}}
    <header class="fixed inset-x-0 top-0 z-50 backdrop-blur-md">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="/" class="flex items-center">
                <img src="{{ asset('images/logo.svg') }}" alt="Kasentra" class="h-9 w-auto dark:hidden">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="Kasentra" class="hidden h-9 w-auto dark:block">
            </a>

            <div class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex dark:text-slate-300">
                <a href="#fitur" class="transition hover:text-brand-600">Fitur</a>
                <a href="#cara-kerja" class="transition hover:text-brand-600">Cara Kerja</a>
                <a href="#harga" class="transition hover:text-brand-600">Untuk Siapa</a>
            </div>

            <div class="flex items-center gap-3">
                {{-- Toggle dark mode (logika di landing.js) --}}
                <button id="theme-toggle" type="button" aria-label="Ganti tema"
                    class="grid h-9 w-9 place-items-center rounded-xl text-slate-600 ring-1 ring-slate-200 transition hover:bg-white dark:text-slate-300 dark:ring-white/10 dark:hover:bg-white/10">
                    <svg class="h-5 w-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                    <svg class="hidden h-5 w-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/></svg>
                </button>
                @auth
                    <a href="{{ $appTarget }}" class="btn-primary">Buka Aplikasi</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn-ghost px-3">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-primary">Masuk</a>
                @endauth
            </div>
        </nav>
    </header>

    {{-- ===================== HERO ===================== --}}
    <section class="relative isolate overflow-hidden">
        {{-- Latar gradien lembut + blob dekoratif --}}
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -top-32 left-1/2 h-[40rem] w-[40rem] -translate-x-1/2 rounded-full bg-gradient-to-br from-brand-200/50 to-ink-200/40 blur-3xl dark:from-brand-500/10 dark:to-ink-500/10"></div>
        </div>

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 pb-20 pt-36 lg:grid-cols-2 lg:pt-44">
            {{-- Kolom teks --}}
            <div data-reveal>
                <span class="badge bg-brand-50 text-brand-700 ring-1 ring-brand-200 dark:bg-brand-500/10 dark:text-brand-300 dark:ring-brand-500/20">
                    ✦ Point of Sale untuk toko ritel
                </span>
                <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl dark:text-white">
                    Kasir yang <span class="text-gradient">cepat</span>,<br>
                    laporan yang <span class="text-gradient">otomatis</span>.
                </h1>
                <p class="mt-6 max-w-lg text-lg text-slate-600 dark:text-slate-300">
                    Kasentra membantu pemilik toko dan kasir memproses transaksi di bawah
                    30 detik, mengurangi stok otomatis, dan melihat omzet harian tanpa
                    hitung manual.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a href="{{ $appTarget }}" class="btn-primary px-6 py-3 text-base">{{ auth()->check() ? 'Buka Aplikasi' : 'Mulai Sekarang' }}</a>
                    <a href="#fitur" class="btn-ghost px-6 py-3 text-base">Lihat Fitur</a>
                </div>
                <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">
                    Tanpa pelatihan khusus · Cocok untuk warung, toko kelontong, kedai &amp; butik
                </p>
            </div>

            {{-- Kolom 3D: kanvas diisi oleh Three.js (landing.js) --}}
            <div data-reveal class="relative">
                <div class="relative mx-auto aspect-square w-full max-w-lg">
                    <canvas id="hero-3d" class="h-full w-full"></canvas>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FITUR ===================== --}}
    <section id="fitur" class="mx-auto max-w-7xl px-6 py-24">
        <div class="mx-auto max-w-2xl text-center" data-reveal>
            <h2 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                Semua yang toko Anda butuhkan
            </h2>
            <p class="mt-4 text-slate-600 dark:text-slate-300">
                Fitur inti POS, tanpa kerumitan yang tidak perlu.
            </p>
        </div>

        <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $features = [
                    ['⚡', 'Transaksi Kilat', 'Cari produk, atur qty, hitung kembalian otomatis. Selesai di bawah 30 detik.'],
                    ['📦', 'Manajemen Produk', 'CRUD produk & kategori, foto, SKU/barcode, dengan indikator stok menipis.'],
                    ['📉', 'Stok Otomatis', 'Stok berkurang sendiri setiap transaksi. Tidak ada lagi hitung manual.'],
                    ['🧾', 'Struk & PDF', 'Cetak struk rapi atau simpan sebagai PDF lengkap dengan nomor invoice unik.'],
                    ['📊', 'Laporan Penjualan', 'Omzet harian, produk terlaris, dan laporan per rentang tanggal.'],
                    ['👥', 'Multi-Peran', 'Akses berbeda untuk Admin/Pemilik dan Kasir, aman dan rapi.'],
                ];
            @endphp

            @foreach ($features as [$icon, $title, $desc])
                <article data-reveal class="card transition duration-300 hover:-translate-y-1 hover:shadow-[var(--shadow-glow)]">
                    <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-2xl ring-1 ring-brand-100 dark:bg-brand-500/10 dark:ring-brand-500/20">
                        {{ $icon }}
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $desc }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- ===================== CARA KERJA ===================== --}}
    <section id="cara-kerja" class="relative overflow-hidden bg-gradient-to-br from-ink-900 to-brand-900 py-24 text-white">
        <div class="mx-auto max-w-7xl px-6">
            <div class="mx-auto max-w-2xl text-center" data-reveal>
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Tiga langkah, transaksi tuntas</h2>
                <p class="mt-4 text-white/70">Alur kasir yang dirancang untuk kecepatan.</p>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-3">
                @php
                    $steps = [
                        ['01', 'Pilih produk', 'Ketik nama atau scan barcode — produk langsung masuk keranjang.'],
                        ['02', 'Hitung & bayar', 'Total terhitung otomatis. Masukkan uang dibayar, kembalian muncul seketika.'],
                        ['03', 'Cetak struk', 'Transaksi tersimpan, stok berkurang, struk siap dicetak. Keranjang reset.'],
                    ];
                @endphp
                @foreach ($steps as [$num, $title, $desc])
                    <div data-reveal class="rounded-[var(--radius-card)] bg-white/5 p-8 ring-1 ring-white/10 backdrop-blur">
                        <span class="text-4xl font-extrabold text-brand-300">{{ $num }}</span>
                        <h3 class="mt-4 text-xl font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-white/70">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== UNTUK SIAPA / CTA ===================== --}}
    <section id="harga" class="mx-auto max-w-7xl px-6 py-24">
        <div data-reveal class="card relative overflow-hidden bg-gradient-to-br from-brand-500 to-ink-600 p-12 text-center text-white ring-0 sm:p-16">
            <h2 class="text-3xl font-bold sm:text-4xl">Siap mempercepat kasir toko Anda?</h2>
            <p class="mx-auto mt-4 max-w-xl text-white/85">
                Mulai gunakan Kasentra hari ini. Intuitif, cepat, dan tanpa pelatihan khusus.
            </p>
            <a href="{{ $appTarget }}" class="btn mt-8 bg-white px-7 py-3 text-base font-semibold text-brand-700 hover:-translate-y-0.5 hover:bg-slate-50">
                {{ auth()->check() ? 'Buka Aplikasi' : 'Masuk ke Kasentra' }}
            </a>
        </div>
    </section>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="border-t border-slate-200 py-10 dark:border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 text-sm text-slate-500 sm:flex-row dark:text-slate-400">
            <div class="flex items-center">
                <img src="{{ asset('images/logo.svg') }}" alt="Kasentra" class="h-8 w-auto dark:hidden">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="Kasentra" class="hidden h-8 w-auto dark:block">
            </div>
            <p>© {{ date('Y') }} Kasentra. Sistem POS untuk toko ritel.</p>
        </div>
    </footer>

</body>
</html>
