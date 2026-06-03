<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\SalesStats;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            // Sengaja TANPA ->login(): kita pakai satu halaman login elegan
            // di /login. Tamu yang membuka /admin akan diarahkan ke sana
            // oleh middleware Authenticate (lihat redirect ke route 'login').
            ->brandName('Kasentra')
            // Logo Kasentra di sidebar/topbar panel + favicon tab browser.
            // Filament menukar otomatis berdasarkan tema panel (kelas .fi-logo-light/dark).
            ->brandLogo(fn () => asset('images/logo.svg'))
            ->darkModeBrandLogo(fn () => asset('images/logo-dark.svg'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/logo-icon.png'))
            // Selaras palet Kasentra: primary = teal (brand), abu-abu = slate.
            ->colors([
                'primary' => Color::Teal,
                'gray' => Color::Slate,
            ])
            ->font('Instrument Sans')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                SalesStats::class,
                SalesChart::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
