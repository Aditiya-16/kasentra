<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Cetak struk PDF transaksi ini (buka di tab baru).
            Action::make('struk')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->url(fn () => route('receipt', $this->record))
                ->openUrlInNewTab(),
        ];
    }
}
