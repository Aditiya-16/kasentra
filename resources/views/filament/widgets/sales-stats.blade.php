{{--
    Widget statistik dashboard dengan animasi count-up (Alpine).
    Memakai <x-filament::section> agar konsisten dengan tema panel, dan inline
    style untuk angka besar supaya tidak bergantung pada utility CSS yang bisa
    saja ter-purge dari bundle Filament.

    Alpine helper countUp(target): menaikkan angka dari 0 ke target dalam ~0.9s.
--}}
<div
    x-data="{
        countUp(el, target, format) {
            let start = performance.now();
            let dur = 900;
            let step = (now) => {
                let p = Math.min((now - start) / dur, 1);
                // easeOutCubic untuk gerak yang halus.
                let eased = 1 - Math.pow(1 - p, 3);
                let val = Math.floor(eased * target);
                el.textContent = format === 'rp'
                    ? 'Rp ' + val.toLocaleString('id-ID')
                    : val.toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }
    }"
    style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem;"
>
    {{-- Omzet hari ini --}}
    <x-filament::section>
        <div style="font-size:.8rem; color:#64748b; font-weight:500;">Omzet Hari Ini</div>
        <div
            x-init="countUp($el, {{ (int) $omzetHariIni }}, 'rp')"
            style="margin-top:.35rem; font-size:1.9rem; font-weight:800; color:#0d9488;"
        >Rp 0</div>
    </x-filament::section>

    {{-- Jumlah transaksi hari ini --}}
    <x-filament::section>
        <div style="font-size:.8rem; color:#64748b; font-weight:500;">Transaksi Hari Ini</div>
        <div
            x-init="countUp($el, {{ (int) $transaksiHariIni }}, 'num')"
            style="margin-top:.35rem; font-size:1.9rem; font-weight:800; color:#4f46e5;"
        >0</div>
    </x-filament::section>

    {{-- Produk terlaris --}}
    <x-filament::section>
        <div style="font-size:.8rem; color:#64748b; font-weight:500;">Produk Terlaris</div>
        {{-- Tanpa warna eksplisit: mewarisi warna teks section Filament agar ikut light/dark. --}}
        <div style="margin-top:.35rem; font-size:1.15rem; font-weight:700;">
            {{ $topProductName }}
        </div>
        <div style="font-size:.8rem; color:#64748b;">
            terjual <span x-init="countUp($el, {{ (int) $topProductQty }}, 'num')">0</span> unit
        </div>
    </x-filament::section>
</div>
