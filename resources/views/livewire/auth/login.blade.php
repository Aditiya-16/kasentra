<div class="grid min-h-screen lg:grid-cols-2">
    {{-- ============ Panel kiri: branding sinematik ============ --}}
    <div class="relative hidden overflow-hidden bg-gradient-to-br from-ink-900 via-brand-900 to-brand-700 lg:block">
        {{-- Blob dekoratif --}}
        <div class="pointer-events-none absolute -left-20 top-1/3 h-96 w-96 rounded-full bg-brand-400/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-10 bottom-10 h-72 w-72 rounded-full bg-ink-500/30 blur-3xl"></div>

        <div class="relative z-10 flex h-full flex-col justify-between p-12 text-white">
            {{-- Panel ini selalu berlatar gelap (gradient), jadi pakai logo terang. --}}
            <a href="/" class="inline-flex w-fit items-center">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="Kasentra" class="h-8 w-auto">
            </a>

            <div>
                <h1 class="text-4xl font-extrabold leading-tight">Selamat datang kembali.</h1>
                <p class="mt-4 max-w-md text-white/70">
                    Masuk untuk mulai memproses transaksi dan memantau penjualan toko Anda.
                </p>
            </div>

            <p class="text-sm text-white/50">© {{ date('Y') }} aditiya-16 · Kasentra</p>
        </div>
    </div>

    {{-- ============ Panel kanan: form ============ --}}
    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-sm">
            {{-- Logo untuk layar kecil: ikut tema (light/dark) halaman. --}}
            <a href="/" class="mb-8 inline-flex items-center lg:hidden">
                <img src="{{ asset('images/logo.svg') }}" alt="Kasentra" class="h-9 w-auto dark:hidden">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="Kasentra" class="hidden h-9 w-auto dark:block">
            </a>

            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Masuk ke akun Anda</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Gunakan email dan kata sandi Anda.</p>

            {{-- wire:submit mencegah reload; .prevent default form submit --}}
            <form wire:submit="authenticate" class="mt-8 space-y-5">
                <div>
                    <label for="email" class="label">Email</label>
                    <input wire:model="email" id="email" type="email" autocomplete="email"
                        autofocus placeholder="anda@toko.test" class="input @error('email') ring-2 ring-red-400 @enderror">
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">Kata sandi</label>
                    <input wire:model="password" id="password" type="password"
                        autocomplete="current-password" placeholder="••••••••" class="input">
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input wire:model="remember" type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Ingat saya
                </label>

                <button type="submit" class="btn-primary w-full py-3"
                    wire:loading.attr="disabled" wire:target="authenticate">
                    {{-- Indikator loading saat proses login (micro-interaction). --}}
                    <span wire:loading.remove wire:target="authenticate">Masuk</span>
                    <span wire:loading wire:target="authenticate" class="flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                        </svg>
                        Memproses…
                    </span>
                </button>
            </form>

            <a href="/" class="mt-6 inline-block text-sm text-slate-500 transition hover:text-brand-600 dark:text-slate-400">
                ← Kembali ke beranda
            </a>
        </div>
    </div>
</div>
