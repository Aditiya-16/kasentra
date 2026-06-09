@php
    // Helper format Rupiah lokal untuk view ini.
    $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
@endphp

<div class="min-h-screen">
    {{-- ===================== TOP BAR ===================== --}}
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/80 backdrop-blur dark:border-white/10 dark:bg-ink-950/80">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-2 font-bold">
                {{-- Light mode: logo gelap; dark mode: logo terang (.dark di <html>). --}}
                <img src="{{ asset('images/logo.svg') }}" alt="Kasentra" class="h-9 w-auto dark:hidden">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="Kasentra" class="hidden h-9 w-auto dark:block">
                <span class="font-normal text-slate-400">· Kasir</span>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden text-slate-500 sm:block dark:text-slate-400">
                    {{ auth()->user()->name }}
                </span>
                @if (auth()->user()->isAdmin())
                    <a href="/admin" class="btn-ghost px-3 py-1.5">Panel Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-ghost px-3 py-1.5">Keluar</button>
                </form>
            </div>
        </div>
    </header>

    <div class="mx-auto grid max-w-7xl gap-5 px-4 py-5 lg:grid-cols-5">
        {{-- ===================== KIRI: PRODUK ===================== --}}
        <section class="lg:col-span-3">
            {{-- Pencarian cepat (live, debounce agar tidak membebani server) --}}
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input
                    wire:model.live.debounce.250ms="search"
                    type="text"
                    autofocus
                    placeholder="Cari produk — ketik nama atau scan barcode…"
                    class="input pl-10 text-base"
                >
            </div>

            {{-- Grid produk --}}
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
                @forelse ($this->products as $product)
                    @php $isOut = $product->stock < 1; @endphp
                    <button
                        type="button"
                        wire:key="prod-{{ $product->id }}"
                        @disabled($isOut)
                        wire:click="addToCart({{ $product->id }})"
                        class="card group relative flex flex-col items-stretch gap-2 p-2.5 text-left transition hover:-translate-y-0.5 hover:shadow-[var(--shadow-glow)] disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <div class="relative">
                            <x-product-thumb
                                :id="$product->id"
                                :name="$product->name"
                                :image="$product->image"
                                :category="$product->category?->name"
                                rounded="rounded-lg"
                            />
                            @if ($isOut)
                                <div class="absolute inset-0 grid place-items-center rounded-lg bg-slate-900/45 text-xs font-bold text-white">
                                    Stok Habis
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col gap-0.5 px-0.5">
                            <span class="line-clamp-2 text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $product->name }}</span>
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-brand-700 dark:text-brand-400 font-bold">{{ $rp($product->price) }}</span>
                                <span class="text-xs font-semibold {{ $product->stock < \App\Models\Product::LOW_STOCK_THRESHOLD ? 'text-red-500' : 'text-slate-400' }}">
                                    {{ $product->stock }}
                                </span>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-slate-300 py-12 text-center text-slate-400 dark:border-white/10">
                        Tidak ada produk ditemukan.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- ===================== KANAN: KERANJANG ===================== --}}
        <aside class="lg:col-span-2">
            <div class="card sticky top-20 p-0">
                <div class="flex items-center justify-between border-b border-slate-100 p-4 dark:border-white/10">
                    <h2 class="flex items-center gap-2 font-semibold text-slate-800 dark:text-white">
                        🛒 Keranjang
                        @if (count($cart))
                            <span class="badge bg-brand-100 text-brand-700 dark:bg-brand-500/15 dark:text-brand-300">{{ count($cart) }}</span>
                        @endif
                    </h2>
                    @if (count($cart))
                        <button wire:click="clearCart" class="text-sm text-slate-400 transition hover:text-red-500">Kosongkan</button>
                    @endif
                </div>

                {{-- Daftar item --}}
                <div class="max-h-[40vh] overflow-y-auto p-4">
                    @forelse ($cart as $id => $item)
                        <div wire:key="cart-{{ $id }}" class="cart-row mb-2 flex items-center gap-3 rounded-xl bg-slate-50 p-2.5 dark:bg-white/5">
                            <div class="h-10 w-10 shrink-0">
                                <x-product-thumb
                                    :id="$id"
                                    :name="$item['name']"
                                    :image="$item['image'] ?? null"
                                    :category="$item['category'] ?? null"
                                    rounded="rounded-lg"
                                />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-slate-800 dark:text-slate-100">{{ $item['name'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $rp($item['price']) }} × {{ $item['qty'] }}</p>
                            </div>

                            {{-- Kontrol qty --}}
                            <div class="flex items-center gap-1">
                                <button wire:click="decrementQty({{ $id }})" class="grid h-7 w-7 place-items-center rounded-lg bg-white text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-100 dark:bg-white/10 dark:text-slate-200 dark:ring-white/10">−</button>
                                <span class="w-7 text-center text-sm font-semibold dark:text-white">{{ $item['qty'] }}</span>
                                <button wire:click="incrementQty({{ $id }})" @disabled($item['qty'] >= $item['stock']) class="grid h-7 w-7 place-items-center rounded-lg bg-white text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-100 disabled:opacity-40 dark:bg-white/10 dark:text-slate-200 dark:ring-white/10">+</button>
                            </div>

                            <div class="w-20 text-right text-sm font-semibold text-slate-800 dark:text-white">{{ $rp($item['price'] * $item['qty']) }}</div>

                            <button wire:click="removeItem({{ $id }})" class="text-slate-300 transition hover:text-red-500" aria-label="Hapus">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @empty
                        <div class="py-10 text-center text-sm text-slate-400">
                            Keranjang kosong.<br>Pilih produk di sebelah kiri untuk memulai.
                        </div>
                    @endforelse
                </div>

                @error('cart')
                    <p class="px-4 text-sm text-red-500">{{ $message }}</p>
                @enderror

                {{-- Ringkasan & pembayaran --}}
                <div class="space-y-4 border-t border-slate-100 p-4 dark:border-white/10">
                    <div class="flex items-center justify-between text-lg font-bold text-slate-800 dark:text-white">
                        <span>Total</span>
                        <span>{{ $rp($this->total) }}</span>
                    </div>

                    {{-- Metode pembayaran --}}
                    <div>
                        <label class="label">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (\App\Enums\PaymentMethod::cases() as $pm)
                                <button type="button" wire:click="$set('paymentMethod', '{{ $pm->value }}')"
                                    class="btn px-2 py-2 text-xs ring-1 transition
                                        {{ $paymentMethod === $pm->value
                                            ? 'bg-brand-500 text-white ring-brand-500'
                                            : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10' }}">
                                    {{ $pm->getLabel() }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @if ($paymentMethod === 'tunai')
                        {{-- ===== TUNAI: input uang + kembalian ===== --}}
                        <div>
                            <label class="label">Uang Dibayar</label>
                            <input wire:model.live="paid" type="number" min="0" step="any" placeholder="0"
                                class="input text-right text-lg font-semibold">
                            @error('paid') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror

                            <div class="mt-2 flex flex-wrap gap-2">
                                <button wire:click="exactCash" class="btn-ghost px-3 py-1 text-xs">Uang Pas</button>
                                <button wire:click="quickCash(20000)" class="btn-ghost px-3 py-1 text-xs">+20rb</button>
                                <button wire:click="quickCash(50000)" class="btn-ghost px-3 py-1 text-xs">+50rb</button>
                                <button wire:click="quickCash(100000)" class="btn-ghost px-3 py-1 text-xs">+100rb</button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 dark:bg-white/5">
                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Kembalian</span>
                            @if ($paid !== '' && $this->change < 0)
                                <span class="font-bold text-red-500">Kurang {{ $rp(abs($this->change)) }}</span>
                            @else
                                <span class="text-xl font-extrabold text-brand-600 dark:text-brand-400">{{ $rp(max(0, $this->change)) }}</span>
                            @endif
                        </div>
                    @elseif ($paymentMethod === 'qris')
                        {{-- ===== QRIS: tampilkan QR toko untuk di-scan pelanggan ===== --}}
                        <div class="rounded-xl bg-slate-50 p-4 text-center dark:bg-white/5">
                            @if ($this->qrisImageUrl)
                                <p class="mb-2 text-sm text-slate-500 dark:text-slate-400">Pindai QRIS untuk membayar {{ $rp($this->total) }}</p>
                                <img src="{{ $this->qrisImageUrl }}" alt="QRIS Toko"
                                    class="mx-auto w-full max-w-[220px] rounded-lg bg-white p-2 ring-1 ring-slate-200">
                                <p class="mt-2 text-xs text-slate-400">Klik "Bayar" setelah pembayaran diterima.</p>
                            @else
                                <p class="text-sm text-amber-600 dark:text-amber-400">
                                    ⚠ QRIS belum diatur. Admin dapat mengunggahnya di
                                    <span class="font-semibold">Panel Admin → Pengaturan Toko</span>.
                                </p>
                            @endif
                        </div>
                    @else
                        {{-- ===== TRANSFER ===== --}}
                        <div class="rounded-xl bg-slate-50 p-4 text-center text-sm text-slate-500 dark:bg-white/5 dark:text-slate-400">
                            Pelanggan mentransfer {{ $rp($this->total) }} ke rekening toko.
                            Klik "Bayar" setelah dana diterima.
                        </div>
                    @endif

                    {{-- Tombol Bayar --}}
                    <button
                        wire:click="checkout"
                        wire:loading.attr="disabled"
                        wire:target="checkout"
                        {{-- Tunai: wajib uang cukup. Non-tunai: cukup keranjang terisi. --}}
                        @disabled(count($cart) === 0 || ($paymentMethod === 'tunai' && $this->change < 0))
                        class="btn-primary w-full py-3 text-base disabled:opacity-50">
                        <span wire:loading.remove wire:target="checkout">Bayar &amp; Selesaikan</span>
                        <span wire:loading wire:target="checkout">Memproses…</span>
                    </button>
                </div>
            </div>
        </aside>
    </div>

    {{-- ===================== OVERLAY SUKSES (Lottie) ===================== --}}
    {{-- x-show + @entangle membuat modal muncul/hilang halus, dikendalikan
         oleh properti $showSuccess di server. --}}
    <div
        x-data="{ show: @entangle('showSuccess') }"
        x-show="show"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-50 grid place-items-center bg-slate-900/50 p-4 backdrop-blur-sm"
        style="display:none"
    >
        <div x-show="show" x-transition.scale class="card w-full max-w-sm text-center">
            {{-- Container Lottie diisi oleh app.js saat event 'transaction-success'.
                 Bila Lottie gagal, fallback centang CSS di bawahnya tetap tampil. --}}
            <div class="relative mx-auto h-32 w-32">
                {{-- Fallback di belakang; Lottie (di atas) akan menutupinya saat berhasil dimuat. --}}
                <div class="check-fallback absolute inset-0 grid place-items-center text-6xl">✅</div>
                <div id="success-lottie" class="absolute inset-0"></div>
            </div>

            <h2 class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">Transaksi Berhasil!</h2>

            @if ($lastTransaction)
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">No. {{ $lastTransaction['invoice'] }}</p>

                <div class="mt-4 space-y-1 rounded-xl bg-slate-50 p-4 text-sm dark:bg-white/5">
                    <div class="flex justify-between"><span class="text-slate-500">Total</span><span class="font-semibold dark:text-white">{{ $rp($lastTransaction['total']) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Dibayar</span><span class="font-semibold dark:text-white">{{ $rp($lastTransaction['paid']) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Kembalian</span><span class="font-bold text-brand-600 dark:text-brand-400">{{ $rp($lastTransaction['change']) }}</span></div>
                </div>
            @endif

            <div class="mt-5 flex gap-2">
                @if ($lastTransaction)
                    {{-- Buka struk PDF di tab baru (bisa langsung di-print dari browser). --}}
                    <a href="{{ route('receipt', $lastTransaction['id']) }}" target="_blank"
                        class="btn-ghost flex-1 py-2.5">🧾 Cetak Struk</a>
                @endif
                <button wire:click="newTransaction" class="btn-primary flex-1 py-2.5">Transaksi Baru</button>
            </div>
        </div>
    </div>
</div>
