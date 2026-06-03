<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Hasilkan struk PDF (PRD 4.7).
     *
     * Otorisasi: hanya admin atau kasir yang membuat transaksi tersebut yang
     * boleh melihat struknya.
     *
     * ?download=1 untuk mengunduh; default ditampilkan inline (bisa langsung
     * di-print dari browser).
     */
    public function show(Request $request, Transaction $transaction)
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $transaction->user_id === $user->id, 403);

        $transaction->load(['items', 'user']);

        $pdf = Pdf::loadView('receipt', ['trx' => $transaction])
            // Lebar 80mm (kertas struk termal); tinggi dibuat longgar.
            ->setPaper([0, 0, 226.77, 650]);

        $filename = $transaction->invoice_number . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
