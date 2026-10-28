<?php

namespace App\Sales\Filament\Pages;

use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\ExpenseApproval;
use App\Sales\Support\Money;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * "Sales management": the one page a manager opens to see how the field team is doing. Everything that comes from the
 * phones (expenses, meetings, routes, clients, calls, enquiries, reports, sync) ends up as a figure here. A new app feature adds its
 * insight to App\Sales\Support\SalesInsights and one block to the view. The view only formats: every query lives in SalesInsights.
 *
 * The order is a manager's morning: where everybody is today, what needs a decision, then the figures with their change against the
 * period before. Every link and every action exists only when the manager may open that area, and the actions check it again.
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

    /** tab => the area a manager needs to see it (money is only ever shown to a manager who may see expenses). */
    public const TABS = [
        'approvals' => 'sales-expenses',
        'enquiries' => 'sales-enquiries',
        'notlogged' => 'sales-meetings',
        'followups' => 'sales-meetings',
        'reach' => 'sales-clients',
    ];

    /** Days shown: 7, 30 or 90. */
    public int $range = 30;

    /** The rep whose detail was opened last (a SalesProfile id). */
    public ?int $selectedRep = null;

    /** The tab of the "Needs your action" card. */
    public string $tab = 'approvals';

    /** The expenses "Approve all with a bill" asked about (fixed when its question opens). */
    public array $approveAllIds = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->canManage('sales-dashboard');
    }

    private function allows(string $area): bool
    {
        return (bool) auth()->user()?->canManage($area);
    }

    /** The tabs this manager may see, in order. */
    private function visibleTabs(): array
    {
        return array_keys(array_filter(self::TABS, fn (string $area) => $this->allows($area)));
    }

    public function setRange(int $days): void
    {
        abort_unless(static::canAccess(), 403);

        $this->range = in_array($days, SalesInsights::RANGES, true) ? $days : 30;
    }

    public function setTab(string $tab): void
    {
        abort_unless(static::canAccess(), 403);

        $this->tab = in_array($tab, $this->visibleTabs(), true) ? $tab : ($this->visibleTabs()[0] ?? 'approvals');
    }

    /** A row of the reps table was picked: remember it and open the rep's detail in a slide-over. */
    public function selectRep(int $profileId): void
    {
        abort_unless(static::canAccess(), 403);

        $this->selectedRep = $profileId;
        $this->mountAction('repDetail', ['id' => $profileId]);
    }

    /** The rep's day, open next to the table. Closed by Esc, by a click outside, or by its own Close button. */
    public function repDetailAction(): Action
    {
        return Action::make('repDetail')
            ->slideOver()
            ->modalWidth('md')
            ->modalHeading(fn (array $arguments) => SalesProfile::active()->with('user')->find((int) ($arguments['id'] ?? 0))?->user?->name ?? 'Rep')
            ->modalDescription('Their day and what waits for them')
            ->modalContent(function (array $arguments) {
                abort_unless(static::canAccess(), 403);
                $detail = SalesInsights::repDetail((int) ($arguments['id'] ?? 0), $this->days());

                if ($detail && ! $this->allows('sales-expenses')) {
                    // no money for a manager who may not see expenses, not even hidden in the markup
                    $detail['rep'] = array_merge($detail['rep'], ['spend_paise' => null, 'expenses' => null, 'pending' => null]);
                }

                return view('filament.pages.sales-management.rep-detail', ['detail' => $detail] + $this->helpers());
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->closeModalByClickingAway()
            ->closeModalByEscaping();
    }

    /** "Approve all N with a bill": asks first, then approves only what is still waiting and has a bill. */
    public function approveAllAction(): Action
    {
        return Action::make('approveAll')
            ->label('Approve all with a bill')
            ->color('success')
            ->visible(fn () => $this->allows('sales-expenses'))
            ->requiresConfirmation()
            // what the question shows is what is approved: the expenses are fixed when the question opens
            ->mountUsing(function () {
                $this->approveAllIds = $this->pendingWithBill()['ids'];
            })
            ->modalHeading(function () {
                $found = $this->approveAllIds ? $this->pendingWithBill($this->approveAllIds) : $this->pendingWithBill();

                return 'Approve ' . $found['count'] . ' ' . str('expense')->plural($found['count']) . ' worth ' . Money::rupees($found['paise'], false) . '?';
            })
            ->modalDescription('Only expenses that say they have a bill are approved. Expenses without a bill stay waiting for you.')
            ->modalSubmitActionLabel('Approve')
            ->action(function () {
                $ids = $this->approveAllIds;
                $this->approveAllIds = [];
                $this->approveAllWithBill($ids ?: null);
            });
    }

    /**
     * What "approve all with a bill" covers: still waiting, says it has a bill, and not the manager's own expense (the policy refuses those).
     *
     * @param  list<int>|null  $only  limit it to these expenses (the ones the question showed)
     * @return array{count: int, paise: int, ids: list<int>}
     */
    private function pendingWithBill(?array $only = null): array
    {
        $query = SalesExpense::where('status', SalesExpense::PENDING)->where('bill_type', 'with_bill')->whereNotNull('bill_path')->where('bill_path', '!=', '')->where('user_id', '!=', (int) auth()->id());

        if ($only !== null) {
            $query->whereIn('id', $only);
        }

        return ['count' => (int) (clone $query)->count(), 'paise' => (int) (clone $query)->sum('amount_paise'), 'ids' => (clone $query)->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all()];
    }

    /** One expense of the "Approvals" list. The same approve as the Expenses screen (SalesExpense::approve writes the audit trail). */
    public function approveExpense(int $id): void
    {
        $result = ExpenseApproval::approve(auth()->user(), $id);

        Notification::make()->title($result === 'approved' ? 'Expense approved' : 'That expense was already decided')->{$result === 'approved' ? 'success' : 'warning'}()->send();
    }

    /**
     * Every expense that is still waiting AND says it has a bill (or only the given ones). An expense without a bill is never approved here.
     *
     * @param  list<int>|null  $only
     */
    public function approveAllWithBill(?array $only = null): void
    {
        $user = auth()->user();
        abort_unless($user?->canManage('sales-expenses'), 403);

        // the same rule as the Expenses queue: waiting, says it has a bill AND the photo is here; each row locked, checked and audited
        $ids = $only !== null ? array_map('intval', $only) : $this->pendingWithBill()['ids'];
        $done = ExpenseApproval::approveWithBill($user, $ids)['approved'];

        Notification::make()->title($done . ' ' . str('expense')->plural($done) . ' approved')->success()->send();
    }

    /** The expenses of the chosen range as a spreadsheet (only for a manager who may see expenses). */
    public function exportReport()
    {
        abort_unless(auth()->user()?->canManage('sales-expenses'), 403);

        $days = $this->days();
        $query = SalesExpense::query()->whereBetween('expense_date', [SalesInsights::from($days)->toDateString(), SalesInsights::today()->toDateString()]);

        return SalesExporter::expenses($query, 'xlsx');
    }

    private function days(): int
    {
        return in_array($this->range, SalesInsights::RANGES, true) ? $this->range : 30;
    }

    /** The small formatting helpers every view of this page uses. */
    private function helpers(): array
    {
        $user = auth()->user();

        return [
            'talk' => fn (int $seconds) => SalesInsights::duration($seconds),
            'money' => fn (int $paise, bool $withPaise = false) => Money::rupees($paise, $withPaise),
            'short' => fn (int $paise) => Money::short($paise),
            // the page may only link to a screen the manager may open, and only a manager who may see expenses sees money
            'can' => fn (string $area) => (bool) $user?->canManage($area),
            'showMoney' => (bool) $user?->canManage('sales-expenses'),
        ];
    }

    protected function getViewData(): array
    {
        $days = $this->days();
        $showMoney = $this->allows('sales-expenses');
        $tabs = $this->visibleTabs();
        $tab = in_array($this->tab, $tabs, true) ? $this->tab : ($tabs[0] ?? 'approvals');

        $summary = SalesInsights::summary($days);
        $reps = SalesInsights::reps($days);
        $today = SalesInsights::todayField();

        if (! $showMoney) {
            // a manager who may not see expenses gets no money in the page at all, not even hidden in the markup
            foreach (['spend_paise', 'expense_count', 'approved_paise', 'pending_count', 'pending_paise'] as $key) {
                $summary[$key] = null;
            }
            $reps = $reps->map(fn (array $r) => array_merge($r, ['spend_paise' => null, 'expenses' => null, 'pending' => null]));
        }

        $categories = $showMoney ? SalesInsights::byCategory($days) : [];

        return [
            'days' => $days,
            'ranges' => SalesInsights::RANGES,
            'summary' => $summary,
            'compared' => SalesInsights::summaryCompared($days, $showMoney),
            'series' => SalesInsights::series(14),
            'today' => $today,
            'todayByUser' => collect($today['reps'])->keyBy('user_id'),
            'flags' => SalesInsights::flags(),
            'categories' => $categories,
            'categoryTotal' => max(1, array_sum(array_column($categories, 'paise'))),
            'expenseSplit' => $showMoney ? SalesInsights::expenseSplit($days) : null,
            'reps' => $reps,
            'selectedId' => $this->selectedRep,
            'latestSync' => SalesInsights::latestSync(),
            'enquiryStatuses' => SalesInsights::enquiriesByStatus(),
            'enquirySources' => SalesInsights::enquirySources($days),
            'meetingResults' => SalesInsights::meetingResults($days),
            'topProducts' => SalesInsights::topProducts($days, 5),
            'activeTab' => $tab,
            'visibleTabs' => $tabs,
            'tabRows' => [
                'approvals' => $showMoney ? SalesInsights::pendingExpenses(5) : collect(),
                'enquiries' => SalesInsights::overdueEnquiries(5),
                'notlogged' => SalesInsights::notLoggedMeetings(5),
                'followups' => SalesInsights::followups(5),
                'reach' => SalesInsights::neglectedClients(30, 5),
            ],
            'neglectedTotal' => SalesInsights::neglectedTotal(),
            'withBill' => $showMoney ? $this->pendingWithBill() : ['count' => 0, 'paise' => 0],
        ] + $this->helpers();
    }
}
