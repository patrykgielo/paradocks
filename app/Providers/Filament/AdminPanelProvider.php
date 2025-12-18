<?php

namespace App\Providers\Filament;

use App\Filament\Pages\MaintenanceSettings;
use App\Filament\Pages\SystemSettings;
use App\Filament\Widgets\CacheClearWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->login()

            // 🎨 COLOR SYSTEM - Medical Precision Turquoise
            ->colors([
                'primary' => [
                    50 => '#E6F4F6',
                    100 => '#CCE9ED',
                    200 => '#99D3DB',
                    300 => '#66BDC9',
                    400 => '#4AA5B0',
                    500 => '#3D8A94',  // Main brand color
                    600 => '#2F6A72',
                    700 => '#224A50',
                    800 => '#162A2E',
                    900 => '#0A0F10',
                ],
                'success' => Color::hex('#34C759'),
                'warning' => Color::hex('#FF9500'),
                'danger' => Color::hex('#FF3B30'),
                'info' => Color::hex('#4AA5B0'),
            ])

            // 🏷️ BRANDING
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->darkModeBrandLogo(fn () => view('filament.brand-logo-dark'))
            ->brandLogoHeight('2.5rem')
            ->brandName('Paradocks Admin')

            // 📐 LAYOUT
            ->sidebarCollapsibleOnDesktop(true)
            ->sidebarWidth('16rem')
            ->collapsedSidebarWidth('4rem')
            ->maxContentWidth('7xl')
            ->darkMode(true)

            // 🔤 TYPOGRAPHY
            ->font('system-ui')

            // 🧭 NAVIGATION - Logical Business Flow
            ->navigationGroups([
                __('navigation.groups.appointments'),    // Wizyty
                __('navigation.groups.content'),         // Treść
                __('navigation.groups.vehicles'),        // Pojazdy
                __('navigation.groups.staff'),           // Personel
                __('navigation.groups.users'),           // Użytkownicy
                __('navigation.groups.communication'),   // Komunikacja
                __('navigation.groups.settings'),        // Ustawienia
                __('navigation.groups.system'),          // System
            ])

            // 📄 PAGES & RESOURCES
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                SystemSettings::class,
                MaintenanceSettings::class,
            ])

            // 📊 WIDGETS
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                CacheClearWidget::class,
            ])

            // 🔒 MIDDLEWARE
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
