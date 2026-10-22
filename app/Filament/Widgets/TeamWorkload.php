<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Support\DashboardData;
use Filament\Widgets\Widget;

/** Who has how many open enquiries (and how many have nobody yet). */
class TeamWorkload extends Widget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    protected string $view = 'filament.widgets.team-workload';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManage('enquiries');
    }

    public function getRows()
    {
        return DashboardData::workload();
    }

    public function getListUrl(): string
    {
        return EnquiryResource::getUrl('index', ['filters' => ['open' => ['isActive' => true]]]);
    }

    public function human(float $hours): string
    {
        return DashboardData::humanHours($hours);
    }
}
