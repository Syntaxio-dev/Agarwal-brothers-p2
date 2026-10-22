<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

/**
 * Top of the dashboard: a greeting, how many things need attention, and the main "add" buttons.
 * Everything comes from QuickUpdates, so the permission rules stay in one place.
 */
class DashboardHeader extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.dashboard-header';

    public static function canView(): bool
    {
        return auth()->check();
    }

    public function getGreeting(): string
    {
        $hour = (int) now('Asia/Kolkata')->format('G');
        $part = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $first = trim(explode(' ', trim((string) auth()->user()?->name))[0] ?? '');

        return $part . ($first !== '' ? ', ' . $first : '');
    }

    public function getAttentionCount(): int
    {
        return collect(app(QuickUpdates::class)->getItems())->where('count', '>', 0)->count();
    }

    public function getDateText(): string
    {
        return now('Asia/Kolkata')->format('l, j F Y');
    }

    /** Webinars in the next 7 days, shown under the greeting (only for people who manage insights). */
    public function getWebinars()
    {
        return auth()->user()?->canManage('insights') ? \App\Support\DashboardData::upcomingWebinars() : collect();
    }

    /** The three main buttons (only those this role may use). @return array<int, array{label:string,url:string}> */
    public function getPrimaryLinks(): array
    {
        return QuickUpdates::primary(app(QuickUpdates::class)->getLinks());
    }
}
