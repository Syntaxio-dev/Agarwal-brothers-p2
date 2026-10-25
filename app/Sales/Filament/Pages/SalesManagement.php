<?php

namespace App\Sales\Filament\Pages;

use App\Sales\Models\SalesExpense;
use App\Sales\Support\Money;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * "Sales management": the one page a manager opens to see how the field team is doing. Everything that comes from the
 * phones (expenses, meetings, clients, calls, enquiries, reports, sync) ends up as a figure here. A new app feature adds its insight to
 * App\Sales\Support\SalesInsights and one block to the view. The view only formats: every query lives in SalesInsights.
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

    public const TABS = ['approvals', 'enquiries', 'followups', 'reach'];

    /** Days shown: 7, 30 or 90. */
    public int $range = 30;

    /** The rep whose card is open on the right of the table (a SalesProfile id; null = the first row). */
    public ?int $selectedRep = null;

    /** The tab of the "Needs your action" card. */
    public string $tab = 'approvals';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->canManage('sales-dashboard');
    }

    public function setRange(int $days): void
    {
        $this->range = in_array($days, SalesInsights::RANGES, true) ? $days : 30;
    }

    public function selectRep(int $profileId): void
    {
        $this->selectedRep = $profileId;
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'approvals';
    }

    /** The expenses of the chosen range as a spreadsheet (only for a manager who may see expenses). */
    public function exportReport()
    {
        abort_unless(auth()->user()?->canManage('sales-expenses'), 403);

        $days = in_array($this->range, SalesInsights::RANGES, true) ? $this->range : 30;
        $query = SalesExpense::query()->whereBetween('expense_date', [SalesInsights::from($days)->toDateString(), SalesInsights::today()->toDateString()]);

        return SalesExporter::expenses($query, 'xlsx');
    }

    protected function getViewData(): array
    {
        $days = in_array($this->range, SalesInsights::RANGES, true) ? $this->range : 30;
        $daily = SalesInsights::daily($days);
        $categories = SalesInsights::byCategory($days);
        $reps = SalesInsights::reps($days);

        // the selected rep, or the first row when nobody was picked (or the picked rep is gone)
        $detail = SalesInsights::repDetail($this->selectedRep ?? $reps->first()['id'] ?? 0, $days)
            ?? ($reps->isNotEmpty() ? SalesInsights::repDetail($reps->first()['id'], $days) : null);
        $user = auth()->user();

        return [
            'days' => $days,
            'ranges' => SalesInsights::RANGES,
            'summary' => SalesInsights::summary($days),
            'daily' => $daily,
            'dailyMax' => max(1, ...array_column($daily, 'paise')),
            'categories' => $categories,
            'categoryTotal' => max(1, array_sum(array_column($categories, 'paise'))),
            'expenseSplit' => SalesInsights::expenseSplit($days),
            'reps' => $reps,
            'selectedId' => $detail['rep']['id'] ?? null,
            'detail' => $detail,
            'latestSync' => SalesInsights::latestSync(),
            'withoutReport' => SalesInsights::repsWithoutReport(),
            'enquiryStatuses' => SalesInsights::enquiriesByStatus(),
            'tabs' => [
                'approvals' => SalesInsights::pendingExpenses(5),
                'enquiries' => SalesInsights::overdueEnquiries(5),
                'followups' => SalesInsights::followups(5),
                'reach' => SalesInsights::neglectedClients(30, 5),
            ],
            'neglectedTotal' => SalesInsights::neglectedTotal(),
            'talk' => fn (int $seconds) => SalesInsights::duration($seconds),
            'money' => fn (int $paise, bool $withPaise = false) => Money::rupees($paise, $withPaise),
            // the page may only link to a screen the manager may open
            'can' => fn (string $area) => (bool) $user?->canManage($area),
        ];
    }
}
