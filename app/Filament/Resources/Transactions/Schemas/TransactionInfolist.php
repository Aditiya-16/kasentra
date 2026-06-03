<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('invoice_number')->label('No. Invoice')->weight('bold'),
                        TextEntry::make('created_at')->label('Tanggal')->dateTime('d M Y H:i'),
                        TextEntry::make('user.name')->label('Kasir'),
                        TextEntry::make('payment_method')->label('Metode Bayar')->badge(),
                    ]),

                Section::make('Item Dibeli')
                    ->schema([
                        // Menampilkan snapshot tiap item (nama & harga saat transaksi).
                        RepeatableEntry::make('items')
                            ->hiddenLabel()
                            ->columns(4)
                            ->schema([
                                TextEntry::make('product_name')->label('Produk')->columnSpan(2),
                                TextEntry::make('qty')->label('Qty'),
                                TextEntry::make('subtotal')->label('Subtotal')->money('IDR'),
                            ]),
                    ]),

                Section::make('Pembayaran')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('total')->label('Total')->money('IDR')->weight('bold'),
                        TextEntry::make('paid')->label('Dibayar')->money('IDR'),
                        TextEntry::make('change')->label('Kembalian')->money('IDR'),
                    ]),
            ]);
    }
}
