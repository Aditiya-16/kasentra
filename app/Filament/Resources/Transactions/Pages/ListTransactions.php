<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    // Tanpa tombol "Buat": transaksi hanya lahir dari halaman kasir.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
