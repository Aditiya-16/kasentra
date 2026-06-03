<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Metode pembayaran (PRD 4.4). Untuk MVP hanya Tunai yang benar-benar dipakai,
 * tapi opsi lain disiapkan agar mudah dikembangkan (fase 2).
 */
enum PaymentMethod: string implements HasLabel
{
    case Tunai = 'tunai';
    case Qris = 'qris';
    case Transfer = 'transfer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Tunai => 'Tunai',
            self::Qris => 'QRIS',
            self::Transfer => 'Transfer',
        };
    }
}
