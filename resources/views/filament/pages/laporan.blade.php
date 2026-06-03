<x-filament-panels::page>
    {{-- Filter rentang tanggal --}}
    <x-filament::section>
        <div style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:1rem;">
            <div>
                <label style="display:block; font-size:.8rem; color:#64748b; margin-bottom:.25rem;">Dari Tanggal</label>
                <input type="date" wire:model.live="dari"
                    style="border:1px solid #cbd5e1; border-radius:.5rem; padding:.45rem .6rem; background:transparent; color:inherit; color-scheme:light dark;">
            </div>
            <div>
                <label style="display:block; font-size:.8rem; color:#64748b; margin-bottom:.25rem;">Sampai Tanggal</label>
                <input type="date" wire:model.live="sampai"
                    style="border:1px solid #cbd5e1; border-radius:.5rem; padding:.45rem .6rem; background:transparent; color:inherit; color-scheme:light dark;">
            </div>
            <div style="margin-left:auto;">
                <x-filament::button wire:click="exportExcel" icon="heroicon-o-table-cells" color="primary">
                    Ekspor Excel
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>

    {{-- Ringkasan --}}
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem;">
        <x-filament::section>
            <div style="font-size:.8rem; color:#64748b;">Total Omzet</div>
            <div style="margin-top:.35rem; font-size:1.8rem; font-weight:800; color:#0d9488;">
                Rp {{ number_format($this->omzet, 0, ',', '.') }}
            </div>
        </x-filament::section>
        <x-filament::section>
            <div style="font-size:.8rem; color:#64748b;">Jumlah Transaksi</div>
            <div style="margin-top:.35rem; font-size:1.8rem; font-weight:800; color:#4f46e5;">
                {{ number_format($this->jumlahTransaksi, 0, ',', '.') }}
            </div>
        </x-filament::section>
    </div>

    {{-- Produk terlaris --}}
    <x-filament::section>
        <x-slot name="heading">Produk Terlaris</x-slot>

        @if ($this->produkTerlaris->isEmpty())
            <p style="color:#94a3b8;">Belum ada penjualan pada rentang ini.</p>
        @else
            <table style="width:100%; border-collapse:collapse; font-size:.9rem;">
                <thead>
                    <tr style="text-align:left; color:#64748b; border-bottom:1px solid rgba(100,116,139,.35);">
                        <th style="padding:.5rem;">#</th>
                        <th style="padding:.5rem;">Produk</th>
                        <th style="padding:.5rem; text-align:right;">Qty Terjual</th>
                        <th style="padding:.5rem; text-align:right;">Omzet</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->produkTerlaris as $i => $row)
                        <tr style="border-bottom:1px solid rgba(100,116,139,.18);">
                            <td style="padding:.5rem; color:#94a3b8;">{{ $i + 1 }}</td>
                            <td style="padding:.5rem; font-weight:600;">{{ $row->product_name }}</td>
                            <td style="padding:.5rem; text-align:right;">{{ number_format($row->qty_total, 0, ',', '.') }}</td>
                            <td style="padding:.5rem; text-align:right;">Rp {{ number_format($row->omzet_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-filament::section>
</x-filament-panels::page>
