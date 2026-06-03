<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('role')
                    ->label('Peran')
                    ->options(UserRole::class)
                    ->default(UserRole::Kasir)
                    ->required(),

                TextInput::make('password')
                    ->label('Kata Sandi')
                    ->password()
                    ->revealable()
                    // Wajib hanya saat membuat akun baru.
                    ->required(fn (string $operation): bool => $operation === 'create')
                    // Saat edit: kalau dikosongkan, password lama tidak ditimpa.
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    // Hashing otomatis oleh cast 'password' => 'hashed' di model User.
                    ->helperText('Kosongkan saat mengedit bila tidak ingin mengganti.')
                    ->minLength(8),

                Toggle::make('is_active')
                    ->label('Akun Aktif')
                    ->default(true)
                    ->helperText('Nonaktifkan untuk mencegah pengguna login.'),
            ]);
    }
}
