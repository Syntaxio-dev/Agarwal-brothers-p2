<?php

namespace App\Filament\Widgets;

use App\Models\Enquiry;
use App\Support\DashboardData;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/** Small bar chart: how many enquiries arrived on each day of the chosen range (for people who work the enquiries). */
class EnquiryTrend extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 7;

    protected ?string $maxHeight = '220px';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManage('enquiries');
    }

    public function getHeading(): ?string
    {
        return 'Enquiries, last ' . DashboardData::days($this->pageFilters) . ' days';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $daily = DashboardData::daily(Enquiry::class, DashboardData::days($this->pageFilters));

        return [
            'datasets' => [[
                'label' => 'Enquiries',
                'data' => $daily['data'],
                'backgroundColor' => '#00B4D8',
                'borderRadius' => 4,
            ]],
            'labels' => $daily['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
