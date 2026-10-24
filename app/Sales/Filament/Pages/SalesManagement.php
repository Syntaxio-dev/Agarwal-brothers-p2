<?php

namespace App\Sales\Filament\Pages;

use App\Sales\Support\Money;
use App\Sales\Support\SalesInsights;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * "Sales management": the one page a manager opens to see how the field team is doing. Everything that comes from the
 * phones (expenses, meetings, clients, sync) ends up as a figure here. A new app feature adds its insight to
 * App\Sales\Support\SalesInsights and one block to the view.
 */
class SalesManagement extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Sales management';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Sales management';

    protected static ?string $slug = 'sales-management';

    protected string $view = 'filament.pages.sales-management';

    /** Days shown: 7, 30 or 90. */
    public int $range = 30;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->canManage('sales-dashboard');
    }

    public function setRange(int $days): void
    {
        $this->range = in_array($days, SalesInsights::RANGES, true) ? $days : 30;
    }

    protected function getViewData(): array
    {
        $days = in_array($this->range, SalesInsights::RANGES, true) ? $this->range : 30;
        $daily = SalesInsights::daily($days);
        $categories = SalesInsights::byCategory($days);

        return [
            'days' => $days,
            'ranges' => SalesInsights::RANGES,
            'summary' => SalesInsights::summary($days),
            'daily' => $daily,
            'dailyMax' => max(1, ...array_column($daily, 'paise')),
            'categories' => $categories,
            'categoryTotal' => max(1, array_sum(array_column($categories, 'paise'))),
            'reps' => SalesInsights::reps($days),
            'stale' => SalesInsights::staleReps(),
            'followups' => SalesInsights::followups(),
            'pending' => SalesInsights::pendingExpenses(),
            'money' => fn (int $paise, bool $withPaise = false) => Money::rupees($paise, $withPaise),
        ];
    }
}
