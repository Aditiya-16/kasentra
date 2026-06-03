<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Filament\Widgets\Widget;

/**
 * Dashboard ringkas (PRD 4.6): omzet hari ini, jumlah transaksi hari ini,
 * dan produk terlaris. Angka dianimasikan "count-up" via Alpine di view.
 */
class SalesStats extends Widget
{
    protected string $view = 'filament.widgets.sales-stats';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    protected function getViewData(): array
    {
        $today = today();

        $top = TransactionItem::query()
            ->selectRaw('product_name, SUM(qty) as qty_total')
            ->groupBy('product_name')
            ->orderByDesc('qty_total')
            ->first();

        return [
            'omzetHariIni' => (float) Transaction::whereDate('created_at', $today)->sum('total'),
            'transaksiHariIni' => Transaction::whereDate('created_at', $today)->count(),
            'topProductName' => $top?->product_name ?? '—',
            'topProductQty' => (int) ($top?->qty_total ?? 0),
        ];
    }
}
