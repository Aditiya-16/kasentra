<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/favicon.ico')),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Product $r) => $r->sku ? "SKU: {$r->sku}" : null),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                // INDIKATOR STOK MENIPIS (PRD 4.3): badge merah + ikon bila < 5.
                TextColumn::make('stock')
                    ->label('Stok')
                    ->badge()
                    ->sortable()
                    ->color(fn (int $state) => $state < Product::LOW_STOCK_THRESHOLD ? 'danger' : 'success')
                    ->icon(fn (int $state) => $state < Product::LOW_STOCK_THRESHOLD ? 'heroicon-m-exclamation-triangle' : null)
                    ->formatStateUsing(fn (int $state) => $state < Product::LOW_STOCK_THRESHOLD ? "{$state} · menipis" : $state),

                // Toggle status aktif langsung dari tabel (cepat untuk admin).
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                // Filter cepat: hanya tampilkan produk yang stoknya menipis.
                Filter::make('low_stock')
                    ->label('Stok menipis')
                    ->query(fn ($query) => $query->where('stock', '<', Product::LOW_STOCK_THRESHOLD)),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
