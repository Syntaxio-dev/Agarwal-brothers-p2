<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Support\DashboardData;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** "Is business growing or slowing?": enquiries, messages and applications in the chosen range, each with a trend line and the change against the range before. */
class BusinessPulse extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user && ($user->canManage('enquiries') || $user->canManage('contact-messages') || $user->canManage('job-applications'));
    }

    protected function getHeading(): ?string
    {
        return 'Business pulse';
    }

    protected function getDescription(): ?string
    {
        $days = DashboardData::days($this->pageFilters);

        return "The last {$days} days, compared with the {$days} days before. Change the range at the top of the page.";
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $days = DashboardData::days($this->pageFilters);
        $stats = [];

        foreach ([
            ['enquiries', Enquiry::class, 'Enquiries'],
            ['contact-messages', ContactMessage::class, 'Contact messages'],
            ['job-applications', JobApplication::class, 'Job applications'],
        ] as [$area, $model, $label]) {
            if (! $user->canManage($area)) {
                continue;
            }

            $cmp = DashboardData::compare($model, $days);
            $up = $cmp['change'] !== null && $cmp['change'] > 0;
            $down = $cmp['change'] !== null && $cmp['change'] < 0;

            $stats[] = Stat::make($label, $cmp['now'])
                ->description(match (true) {
                    $cmp['change'] === null => $cmp['now'] ? 'Nothing to compare with yet' : 'None in this period',
                    $up => $cmp['change'] . '% more than before',
                    $down => abs($cmp['change']) . '% fewer than before',
                    default => 'Same as before',
                })
                ->descriptionIcon($up ? 'heroicon-m-arrow-trending-up' : ($down ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($up ? 'success' : ($down ? 'warning' : 'gray'))
                ->chart(DashboardData::daily($model, $days)['data']);
        }

        if ($user->canManage('enquiries')) {
            $avg = DashboardData::averageResponse($days);
            $target = (int) config('dashboard.response_target_hours');
            $good = $avg['hours'] !== null && $avg['hours'] <= $target;

            $stats[] = Stat::make('Average first response', DashboardData::humanHours($avg['hours']))
                ->description($avg['hours'] === null
                    ? 'No answered enquiries in this period yet'
                    : 'Target: within ' . DashboardData::humanHours((float) $target) . ' (' . $avg['count'] . ' answered)')
                ->descriptionIcon($avg['hours'] === null ? 'heroicon-m-clock' : ($good ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle'))
                ->color($avg['hours'] === null ? 'gray' : ($good ? 'success' : 'danger'));
        }

        return $stats;
    }
}
