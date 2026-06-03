<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Peran pengguna (PRD bagian 3). Backed enum 'string' agar tersimpan
 * sebagai 'admin'/'kasir' di kolom enum database.
 *
 * Mengimplementasikan HasLabel agar Filament otomatis menampilkan label
 * ramah ini di select/badge.
 */
enum UserRole: string implements HasLabel
{
    case Admin = 'admin';
    case Kasir = 'kasir';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin / Pemilik',
            self::Kasir => 'Kasir',
        };
    }
}
