<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
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
            ->brandName('Agarwal Brothers')
            ->colors([
                'primary' => Color::hex('#0077B6'),
                'info' => Color::hex('#00B4D8'),
                'success' => Color::hex('#2A9D8F'),
                'danger' => Color::hex('#E76F51'),
                'warning' => Color::hex('#E76F51'),
            ])
            ->font('IBM Plex Sans', provider: \Filament\FontProviders\LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandLogo(asset('sidebar-logo.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicon.png'))
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch(\App\Filament\Support\AdminSearchProvider::class)
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            ->renderHook(PanelsRenderHook::CONTENT_START, function () {
                $topic = \App\Support\HelpGuide::topicFor(request()->path());
                if (! $topic || request()->is('admin/help') || ! auth()->check()) {
                    return '';
                }

                return view('filament.partials.help-link', ['topic' => $topic]);
            })
            ->navigationGroups(['Homepage', 'Catalogue', 'Content', 'Leads', 'Careers', 'Settings', 'Help'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
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
