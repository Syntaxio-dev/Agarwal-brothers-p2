<?php

namespace App\Filament\Widgets;

use App\Support\DashboardData;
use Filament\Widgets\Widget;

/** "Did anything slip?": the enquiry or message that has waited longest with nobody touching it. */
class ResponseWatch extends Widget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected string $view = 'filament.widgets.response-watch';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ($user->canManage('enquiries') || $user->canManage('contact-messages'));
    }

    public function getColumnSpan(): int|string|array
    {
        return TeamWorkload::canView() ? $this->columnSpan : 'full';
    }

    public function getOldest(): ?array
    {
        return DashboardData::oldestPending(auth()->user());
    }

    public function getTargetHours(): int
    {
        return (int) config('dashboard.response_target_hours');
    }

    public function human(float $hours): string
    {
        return DashboardData::humanHours($hours);
    }
}
