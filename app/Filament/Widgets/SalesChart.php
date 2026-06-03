<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Grafik omzet 7 hari terakhir. ChartWidget Filament memakai Chart.js yang
 * sudah memberi animasi "muncul halus" secara bawaan.
 */
class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Omzet 7 Hari Terakhir';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $labels = [];
        $totals = [];

        // Loop 7 hari ke belakang hingga hari ini.
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->translatedFormat('d M');
            $totals[] = (float) Transaction::whereDate('created_at', $day)->sum('total');
        }

        return [
            'datasets' => [[
                'label' => 'Omzet (Rp)',
                'data' => $totals,
                'borderColor' => '#0d9488',
                'backgroundColor' => 'rgba(13, 148, 136, 0.15)',
                'fill' => true,
                'tension' => 0.35,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
