<?php

namespace App\Filament\Widgets;

use App\Support\DashboardData;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/** Which vertical the enquiries of the range are about (where the interest is). */
class EnquirySources extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 8;

    protected ?string $maxHeight = '220px';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = ['default' => 'full', 'lg' => 1];

    /** Site palette only. */
    private const COLOURS = ['#0B2545', '#0077B6', '#00B4D8', '#2A9D8F', '#E76F51', '#6C757D'];

    public static function canView(): bool
    {
        return (bool) auth()->user()?->canManage('enquiries');
    }

    public function getHeading(): ?string
    {
        return 'Enquiries by vertical, last ' . DashboardData::days($this->pageFilters) . ' days';
    }

    public function getDescription(): ?string
    {
        return 'Which product area customers ask about. An enquiry about products in two verticals counts in both.';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = DashboardData::bySource(DashboardData::days($this->pageFilters));

        if ($counts->isEmpty()) {
            return ['datasets' => [['data' => [1], 'backgroundColor' => ['#E5E7EB']]], 'labels' => ['No enquiries in this period']];
        }

        return [
            'datasets' => [[
                'label' => 'Enquiries',
                'data' => $counts->values()->all(),
                'backgroundColor' => $counts->keys()->map(fn ($k, $i) => self::COLOURS[$i % count(self::COLOURS)])->all(),
            ]],
            'labels' => $counts->keys()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => true, 'position' => 'right']],
            'scales' => ['x' => ['display' => false], 'y' => ['display' => false]],
        ];
    }
}
