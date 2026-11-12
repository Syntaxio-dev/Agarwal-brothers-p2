<?php

namespace App\Sales\Rep;

use App\Sales\Rep\Pages\RepLogin;
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
 * The reps' own desk on the desktop: a field rep signs in with the same employee code (or phone) and password as in the phone app and
 * sees and manages only what is his own (his enquiries, follow-ups ...). It lives on the Sales sub-domain under /rep. Same database as the
 * phone app: what he does here reaches his phone on its next sync, and what he did on the phone is here after the phone's sync.
 * Nothing of the office screens is reachable from here (see User::canAccessPanel).
 */
class RepPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        if (app()->environment('local')) {
            @unlink(base_path('bootstrap/cache/filament/panels/rep.php'));
        }

        $domain = config('sales.domain');

        $panel = $panel
            ->id('rep')
            ->login(RepLogin::class)
            ->renderHook(\Filament\View\PanelsRenderHook::SIMPLE_LAYOUT_START, fn () => view('filament.sales.login-hero'))
            ->brandName('AB Sales · My desk')
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
            ->sidebarWidth('15rem')
            ->unsavedChangesAlerts()
            ->discoverResources(in: app_path('Sales/Rep/Resources'), for: 'App\Sales\Rep\Resources')
            ->discoverPages(in: app_path('Sales/Rep/Pages'), for: 'App\Sales\Rep\Pages')
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

        return $domain ? $panel->domain($domain)->path('rep') : $panel->path('rep');
    }
}
