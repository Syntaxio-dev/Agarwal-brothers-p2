<?php

namespace App\Sales\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The Sales team's own admin panel. With SALES_DOMAIN set (sales.agarwalbrothers.com) it lives at the root of that
 * sub-domain; without it (local development, tests) it lives at /sales on the same host. The website's /admin does
 * not show any Sales screen. Same users, same database, but a separate login session (host-only cookie).
 */
class SalesPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        if (app()->environment('local')) {
            @unlink(base_path('bootstrap/cache/filament/panels/sales.php'));   // same reason as the admin panel
        }

        $domain = config('sales.domain');

        $panel = $panel
            ->id('sales')
            ->login(\App\Sales\Auth\SalesLogin::class)
            ->renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, fn () => view('filament.sales.login-hero'))
            ->brandName('AB Sales')
            ->colors([
                'primary' => Color::hex('#0077B6'),
                'info' => Color::hex('#00B4D8'),
                'success' => Color::hex('#2A9D8F'),
                'danger' => Color::hex('#E76F51'),
                'warning' => Color::hex('#E76F51'),
            ])
            ->font('IBM Plex Sans', provider: \Filament\FontProviders\LocalFontProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')   // the same look as the website's admin
            ->brandLogo(asset('sidebar-logo.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicon.png'))
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15rem')
            ->unsavedChangesAlerts()
            ->navigationGroups(['Sales team'])
            ->discoverResources(in: app_path('Sales/Filament/Resources'), for: 'App\Sales\Filament\Resources')
            ->discoverPages(in: app_path('Sales/Filament/Pages'), for: 'App\Sales\Filament\Pages')
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

        return $domain
            ? $panel->domain($domain)->path('')
            : $panel->path('sales');
    }
}
