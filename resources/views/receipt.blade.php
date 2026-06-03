<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    {{-- CSS inline: dompdf tidak memproses Tailwind/Vite, jadi gaya ditulis langsung. --}}
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #000;
            width: 100%;
            padding: 6px 8px;
        }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .store { font-size: 15px; font-weight: bold; letter-spacing: 1px; }
        .muted { color: #444; font-size: 9px; }
        hr { border: none; border-top: 1px dashed #555; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 1px 0; }
        .item-name { font-weight: bold; }
        .totals td { padding: 2px 0; }
        .grand { font-size: 12px; font-weight: bold; }
        .footer { margin-top: 8px; font-size: 9px; }
    </style>
</head>
<body>
    {{-- Kepala struk: nama toko + info transaksi --}}
    <div class="center">
        <div class="store">KASENTRA</div>
        <div class="muted">Sistem POS Toko Ritel</div>
    </div>

    <hr>

    <table>
        <tr><td>No. Invoice</td><td class="right bold">{{ $trx->invoice_number }}</td></tr>
        <tr><td>Tanggal</td><td class="right">{{ $trx->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="right">{{ $trx->user->name }}</td></tr>
        <tr><td>Bayar</td><td class="right">{{ $trx->payment_method->getLabel() }}</td></tr>
    </table>

    <hr>

    {{-- Daftar item: nama, qty × harga, subtotal --}}
    <table>
        @foreach ($trx->items as $item)
            <tr>
                <td colspan="2" class="item-name">{{ $item->product_name }}</td>
            </tr>
            <tr>
                <td>{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    <hr>

    {{-- Total, dibayar, kembalian --}}
    <table class="totals">
        <tr class="grand">
            <td>TOTAL</td>
            <td class="right">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Dibayar</td>
            <td class="right">Rp {{ number_format($trx->paid, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="right">Rp {{ number_format($trx->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    <hr>

    <div class="center footer">
        Terima kasih telah berbelanja!<br>
        Barang yang sudah dibeli tidak dapat ditukar tanpa struk.
    </div>
</body>
</html>
