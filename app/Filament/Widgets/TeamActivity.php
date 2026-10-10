<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\ActivityLog;
use Filament\Widgets\Widget;

/** Dashboard: the latest changes made by the team (administrators only). */
class TeamActivity extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.team-activity';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManage('activity-log');
    }

    /** @return \Illuminate\Support\Collection<int, ActivityLog> */
    public function getRows()
    {
        return ActivityLog::query()->where('action', '!=', 'login')->latest('id')->limit(8)->get();
    }

    public function getAllUrl(): string
    {
        return ActivityLogResource::getUrl('index');
    }
}
