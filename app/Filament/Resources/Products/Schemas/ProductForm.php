<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    // Admin bisa menambah kategori baru langsung dari form produk.
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->unique('categories', 'name'),
                    ]),

                TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255),

                TextInput::make('sku')
                    ->label('SKU / Barcode')
                    ->helperText('Opsional. Harus unik bila diisi.')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('price')
                    ->label('Harga Jual')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    // PRD 4.3: harga tidak boleh negatif.
                    ->minValue(0)
                    ->step(100),

                TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    // PRD 4.3: stok tidak boleh negatif.
                    ->minValue(0)
                    ->default(0)
                    ->helperText('Stok di bawah ' . \App\Models\Product::LOW_STOCK_THRESHOLD . ' akan ditandai "menipis".'),

                FileUpload::make('image')
                    ->label('Foto Produk')
                    ->image()
                    // Batasi ke raster aman; tolak SVG (bisa menyimpan skrip → XSS).
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->imageEditor()
                    ->directory('products')
                    ->maxSize(2048) // KB
                    ->helperText('Opsional. Maks 2 MB.'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->helperText('Nonaktifkan untuk menyembunyikan produk dari kasir.'),
            ]);
    }
}
