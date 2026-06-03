<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use App\Models\TransactionItem;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title as ChartTitle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan penjualan per rentang tanggal (PRD 4.6):
 * total omzet, jumlah transaksi, dan daftar produk terlaris pada rentang itu,
 * lengkap dengan ekspor Excel (.xlsx) berisi ringkasan, tren harian, dan grafik.
 */
class Laporan extends Page
{
    protected string $view = 'filament.pages.laporan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Penjualan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Laporan Penjualan';

    /** Warna brand untuk header tabel pada workbook. */
    private const BRAND = '0D9488';   // teal-600
    private const INK = '0F172A';     // slate-900
    private const SOFT = 'F1F5F9';    // slate-100
    private const RP = '"Rp"#,##0';   // format mata uang

    /** Rentang tanggal aktif (default: awal bulan s/d hari ini). */
    public string $dari = '';
    public string $sampai = '';

    public function mount(): void
    {
        $this->dari = today()->startOfMonth()->toDateString();
        $this->sampai = today()->toDateString();
    }

    /** Query dasar transaksi dalam rentang tanggal terpilih. */
    protected function baseQuery()
    {
        return Transaction::query()
            ->whereDate('created_at', '>=', $this->dari)
            ->whereDate('created_at', '<=', $this->sampai);
    }

    #[Computed]
    public function omzet(): float
    {
        return (float) $this->baseQuery()->sum('total');
    }

    #[Computed]
    public function jumlahTransaksi(): int
    {
        return $this->baseQuery()->count();
    }

    /** Produk terlaris pada rentang (berdasarkan total qty terjual). */
    #[Computed]
    public function produkTerlaris(): Collection
    {
        return TransactionItem::query()
            ->whereHas('transaction', fn ($q) => $q
                ->whereDate('created_at', '>=', $this->dari)
                ->whereDate('created_at', '<=', $this->sampai))
            ->selectRaw('product_name, SUM(qty) as qty_total, SUM(subtotal) as omzet_total')
            ->groupBy('product_name')
            ->orderByDesc('qty_total')
            ->limit(10)
            ->get();
    }

    /** Ekspor laporan lengkap (ringkasan + tren + grafik) ke Excel .xlsx. */
    public function exportExcel(): StreamedResponse
    {
        // ---------- Kumpulkan data ----------
        $omzet = $this->omzet();
        $jumlah = $this->jumlahTransaksi();
        $rata = $jumlah > 0 ? $omzet / $jumlah : 0;

        $totalItem = (int) TransactionItem::query()
            ->whereHas('transaction', fn ($q) => $q
                ->whereDate('created_at', '>=', $this->dari)
                ->whereDate('created_at', '<=', $this->sampai))
            ->sum('qty');

        $byMethod = $this->baseQuery()
            ->selectRaw('payment_method, COUNT(*) as cnt, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $daily = $this->baseQuery()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as cnt, SUM(total) as total')
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $topProducts = $this->produkTerlaris();
        $transaksi = $this->baseQuery()->with('user')->orderBy('created_at')->get();

        // ---------- Bangun workbook ----------
        $ss = new Spreadsheet();
        $ss->getProperties()
            ->setCreator('Kasentra POS')
            ->setTitle("Laporan Penjualan {$this->dari} s/d {$this->sampai}");

        $this->buildRingkasan($ss->getActiveSheet(), $omzet, $jumlah, $rata, $totalItem, $byMethod);
        $this->buildTrenHarian($ss->createSheet(), $daily);
        $this->buildProdukTerlaris($ss->createSheet(), $topProducts);
        $this->buildTransaksi($ss->createSheet(), $transaksi);

        $ss->setActiveSheetIndex(0);

        $filename = "laporan-{$this->dari}-sd-{$this->sampai}.xlsx";

        return response()->streamDownload(function () use ($ss) {
            $writer = new Xlsx($ss);
            $writer->setIncludeCharts(true);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Beri gaya pada baris header tabel (latar brand, teks putih, tebal). */
    private function styleHeaderRow(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::BRAND);
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    /** ===================== Sheet 1: Ringkasan + grafik metode ===================== */
    private function buildRingkasan(Worksheet $s, float $omzet, int $jumlah, float $rata, int $totalItem, Collection $byMethod): void
    {
        $s->setTitle('Ringkasan');

        // Judul.
        $s->setCellValue('A1', 'LAPORAN PENJUALAN — KASENTRA');
        $s->mergeCells('A1:C1');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB(self::BRAND);

        $s->setCellValue('A2', "Periode: {$this->dari} s/d {$this->sampai}");
        $s->mergeCells('A2:C2');
        $s->setCellValue('A3', 'Dibuat: '.now()->format('d M Y H:i'));
        $s->mergeCells('A3:C3');
        $s->getStyle('A2:A3')->getFont()->getColor()->setRGB('64748B');

        // KPI ringkas.
        $kpi = [
            ['Total Omzet', $omzet, self::RP],
            ['Jumlah Transaksi', $jumlah, '#,##0'],
            ['Rata-rata / Transaksi', $rata, self::RP],
            ['Total Item Terjual', $totalItem, '#,##0'],
        ];
        $row = 5;
        foreach ($kpi as [$label, $value, $fmt]) {
            $s->setCellValue("A{$row}", $label);
            $s->getStyle("A{$row}")->getFont()->setBold(true);
            $s->setCellValue("C{$row}", $value);
            $s->getStyle("C{$row}")->getNumberFormat()->setFormatCode($fmt);
            $s->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setRGB(self::BRAND);
            $s->getStyle("A{$row}:C{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::SOFT);
            $row++;
        }

        // Tabel pembayaran per metode.
        $headRow = $row + 1;                 // baris header tabel metode
        $s->setCellValue("A{$headRow}", 'Metode');
        $s->setCellValue("B{$headRow}", 'Jumlah Transaksi');
        $s->setCellValue("C{$headRow}", 'Total');
        $this->styleHeaderRow($s, "A{$headRow}:C{$headRow}");

        $dataStart = $headRow + 1;
        $r = $dataStart;
        foreach ($byMethod as $m) {
            $s->setCellValue("A{$r}", $m->payment_method?->getLabel() ?? (string) $m->payment_method);
            $s->setCellValue("B{$r}", (int) $m->cnt);
            $s->setCellValue("C{$r}", (float) $m->total);
            $s->getStyle("C{$r}")->getNumberFormat()->setFormatCode(self::RP);
            $r++;
        }
        $dataEnd = $r - 1;

        foreach (['A' => 26, 'B' => 18, 'C' => 16] as $col => $w) {
            $s->getColumnDimension($col)->setWidth($w);
        }

        // Pie chart komposisi omzet per metode pembayaran.
        if ($byMethod->isNotEmpty()) {
            $labels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Ringkasan!\$C\${$headRow}", null, 1)];
            $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Ringkasan!\$A\${$dataStart}:\$A\${$dataEnd}", null, $byMethod->count())];
            $values = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Ringkasan!\$C\${$dataStart}:\$C\${$dataEnd}", null, $byMethod->count())];

            $series = new DataSeries(
                DataSeries::TYPE_PIECHART,
                null,
                range(0, count($values) - 1),
                $labels,
                $categories,
                $values
            );

            $chart = new Chart(
                'metode',
                new ChartTitle('Komposisi Omzet per Metode Bayar'),
                new Legend(Legend::POSITION_RIGHT, null, false),
                new PlotArea(null, [$series])
            );
            $chart->setTopLeftPosition('E5');
            $chart->setBottomRightPosition('M22');
            $s->addChart($chart);
        }
    }

    /** ===================== Sheet 2: Tren Harian + line chart ===================== */
    private function buildTrenHarian(Worksheet $s, Collection $daily): void
    {
        $s->setTitle('Tren Harian');

        $s->setCellValue('A1', 'Tren Omzet Harian');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB(self::INK);

        $s->setCellValue('A3', 'Tanggal');
        $s->setCellValue('B3', 'Omzet');
        $s->setCellValue('C3', 'Transaksi');
        $this->styleHeaderRow($s, 'A3:C3');

        $start = 4;
        $r = $start;
        foreach ($daily as $d) {
            $s->setCellValue("A{$r}", (string) $d->d);
            $s->setCellValue("B{$r}", (float) $d->total);
            $s->getStyle("B{$r}")->getNumberFormat()->setFormatCode(self::RP);
            $s->setCellValue("C{$r}", (int) $d->cnt);
            $r++;
        }
        $end = $r - 1;

        $s->getColumnDimension('A')->setWidth(14);
        $s->getColumnDimension('B')->setWidth(16);
        $s->getColumnDimension('C')->setWidth(12);

        if ($daily->isNotEmpty()) {
            $labels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Tren Harian'!\$B\$3", null, 1)];
            $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Tren Harian'!\$A\${$start}:\$A\${$end}", null, $daily->count())];
            $values = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Tren Harian'!\$B\${$start}:\$B\${$end}", null, $daily->count())];

            $series = new DataSeries(
                DataSeries::TYPE_LINECHART,
                DataSeries::GROUPING_STANDARD,
                range(0, count($values) - 1),
                $labels,
                $categories,
                $values
            );
            $series->setPlotStyle('marker');

            $chart = new Chart(
                'tren',
                new ChartTitle('Omzet per Hari'),
                new Legend(Legend::POSITION_BOTTOM, null, false),
                new PlotArea(null, [$series])
            );
            $chart->setTopLeftPosition('E3');
            $chart->setBottomRightPosition('N22');
            $s->addChart($chart);
        }
    }

    /** ===================== Sheet 3: Produk Terlaris + bar chart ===================== */
    private function buildProdukTerlaris(Worksheet $s, Collection $top): void
    {
        $s->setTitle('Produk Terlaris');

        $s->setCellValue('A1', 'Produk Terlaris (Top 10)');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB(self::INK);

        $s->setCellValue('A3', 'Produk');
        $s->setCellValue('B3', 'Qty Terjual');
        $s->setCellValue('C3', 'Omzet');
        $this->styleHeaderRow($s, 'A3:C3');

        $start = 4;
        $r = $start;
        foreach ($top as $p) {
            $s->setCellValue("A{$r}", $p->product_name);
            $s->setCellValue("B{$r}", (int) $p->qty_total);
            $s->setCellValue("C{$r}", (float) $p->omzet_total);
            $s->getStyle("C{$r}")->getNumberFormat()->setFormatCode(self::RP);
            $r++;
        }
        $end = $r - 1;

        $s->getColumnDimension('A')->setWidth(30);
        $s->getColumnDimension('B')->setWidth(14);
        $s->getColumnDimension('C')->setWidth(16);

        if ($top->isNotEmpty()) {
            $labels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Produk Terlaris'!\$B\$3", null, 1)];
            $categories = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Produk Terlaris'!\$A\${$start}:\$A\${$end}", null, $top->count())];
            $values = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Produk Terlaris'!\$B\${$start}:\$B\${$end}", null, $top->count())];

            $series = new DataSeries(
                DataSeries::TYPE_BARCHART,
                DataSeries::GROUPING_CLUSTERED,
                range(0, count($values) - 1),
                $labels,
                $categories,
                $values
            );
            $series->setPlotDirection(DataSeries::DIRECTION_BAR); // batang horizontal

            $chart = new Chart(
                'produk',
                new ChartTitle('Qty Terjual per Produk'),
                new Legend(Legend::POSITION_BOTTOM, null, false),
                new PlotArea(null, [$series])
            );
            $chart->setTopLeftPosition('E3');
            $chart->setBottomRightPosition('N24');
            $s->addChart($chart);
        }
    }

    /** ===================== Sheet 4: Detail Transaksi ===================== */
    private function buildTransaksi(Worksheet $s, Collection $rows): void
    {
        $s->setTitle('Transaksi');

        $headers = ['Invoice', 'Tanggal', 'Kasir', 'Metode', 'Total', 'Dibayar', 'Kembalian'];
        $s->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($s, 'A1:G1');

        $r = 2;
        foreach ($rows as $t) {
            $s->setCellValue("A{$r}", $t->invoice_number);
            $s->setCellValue("B{$r}", $t->created_at->format('Y-m-d H:i'));
            $s->setCellValue("C{$r}", $t->user?->name);
            $s->setCellValue("D{$r}", $t->payment_method?->getLabel() ?? (string) $t->payment_method);
            $s->setCellValue("E{$r}", (float) $t->total);
            $s->setCellValue("F{$r}", (float) $t->paid);
            $s->setCellValue("G{$r}", (float) $t->change);
            $r++;
        }
        $lastRow = max(1, $r - 1);

        // Format mata uang untuk kolom nominal.
        $s->getStyle("E2:G{$lastRow}")->getNumberFormat()->setFormatCode(self::RP);

        // Lebar kolom proporsional.
        foreach (['A' => 18, 'B' => 18, 'C' => 18, 'D' => 12, 'E' => 14, 'F' => 14, 'G' => 14] as $col => $w) {
            $s->getColumnDimension($col)->setWidth($w);
        }

        // Auto-filter + bekukan baris header agar mudah ditelusuri.
        $s->setAutoFilter("A1:G{$lastRow}");
        $s->freezePane('A2');
    }
}
