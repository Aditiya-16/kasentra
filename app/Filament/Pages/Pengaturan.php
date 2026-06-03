<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman setelan toko (admin). Di sinilah QRIS bisa diunggah/diganti kapan
 * saja tanpa menyentuh kode — nilainya tersimpan di tabel settings.
 */
class Pengaturan extends Page
{
    protected string $view = 'filament.pages.pengaturan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $title = 'Pengaturan Toko';

    /** State form (di-bind lewat statePath('data')). */
    public ?array $data = [];

    /** Hanya admin yang boleh membuka halaman ini. */
    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return (bool) $user?->isAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([
            'store_name' => Setting::get('store_name', 'Kasentra'),
            'qris_image' => Setting::get('qris_image'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Toko')
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Nama Toko')
                            ->maxLength(255),
                    ]),

                Section::make('Pembayaran QRIS')
                    ->description('Unggah gambar QRIS toko Anda. Gambar ini akan ditampilkan di halaman kasir saat pelanggan memilih metode QRIS. Bisa diganti kapan saja.')
                    ->schema([
                        FileUpload::make('qris_image')
                            ->label('Gambar QRIS')
                            ->image()
                            // Batasi ke raster aman; tolak SVG (bisa menyimpan skrip → XSS).
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->directory('settings')
                            ->disk('public')
                            ->imagePreviewHeight('250')
                            ->maxSize(4096),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('store_name', $data['store_name'] ?? 'Kasentra');
        Setting::set('qris_image', $data['qris_image'] ?? null);

        Notification::make()
            ->title('Pengaturan tersimpan')
            ->success()
            ->send();
    }
}
