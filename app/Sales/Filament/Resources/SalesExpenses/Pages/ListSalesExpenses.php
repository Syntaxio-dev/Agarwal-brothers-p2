<?php

namespace App\Sales\Filament\Resources\SalesExpenses\Pages;

use App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource;
use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\ExpenseApproval;
use App\Sales\Support\Money;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;

/**
 * The review queue. What waits is sorted by what can be approved safely and what needs a look; the bill photo, the note and the rep's
 * context sit next to Approve / Reject. Every decision goes through ExpenseApproval (SalesExpense::approve() / reject(): audit trail),
 * every action re-checks the right and that the expense is still waiting, and the bill photo is only ever shown through the authorised
 * route `sales.bill`. All figures come from SalesInsights.
 */
class ListSalesExpenses extends Page
{
    protected static string $resource = SalesExpenseResource::class;

    protected string $view = 'filament.sales.expenses.queue';

    public const TABS = ['waiting', 'approved', 'rejected', 'all'];

    /** The quick reasons of the reject window (the texts are in lang/en/sales_expenses.php). */
    public const REASONS = ['missing', 'unreadable', 'too_high', 'category', 'date'];

    #[Url]
    public string $tab = '';

    #[Url]
    public ?string $period = null;

    #[Url]
    public ?string $from = null;

    #[Url]
    public ?string $until = null;

    #[Url]
    public ?string $rep = null;

    #[Url]
    public ?string $category = null;

    #[Url]
    public ?string $area = null;

    #[Url]
    public ?string $bill = null;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'expense')]
    public ?string $expense = null;

    /** The uuids ticked in the list. */
    public array $selected = [];

    /** The waiting expenses "Approve all" asked about (fixed when its question opens). */
    public array $approveAllIds = [];

    public function getTitle(): string
    {
        return 'Expenses';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    // ---- state -----------------------------------------------------------------------------------------------------

    /** The filters, cleaned: whatever the address or the browser sends, only known values get through. */
    private function filters(): array
    {
        $in = fn (?string $v, array $allowed) => in_array($v, $allowed, true) ? $v : null;

        return [
            'rep' => ctype_digit((string) $this->rep) && (int) $this->rep > 0 ? (int) $this->rep : null,
            'category' => $in($this->category, config('sales.expense_categories')),
            'area' => $in($this->area, ['local', 'outstation', 'none']),
            'bill' => $in($this->bill, ['with_bill', 'without_bill', 'none']),
            'q' => mb_substr(trim($this->search), 0, 100),
        ];
    }

    private function period(): array
    {
        return SalesInsights::expensePeriod($this->period, $this->from, $this->until);
    }

    /** The tab shown: the one asked for, else Waiting when something waits, else All. */
    private function currentTab(array $counts): string
    {
        return in_array($this->tab, self::TABS, true) ? $this->tab : ($counts['waiting'] > 0 ? 'waiting' : 'all');
    }

    /** @return Collection<int, SalesExpense> the rows in the order the list shows them */
    private function visibleRows(string $tab, array $groups, ?Collection $flat): Collection
    {
        return $tab === 'waiting' ? collect($groups)->flatMap(fn (array $g) => $g['rows'])->values() : ($flat ?? collect());
    }

    private function load(): array
    {
        $win = $this->period();
        $filters = $this->filters();
        $counts = SalesInsights::expenseCounts($win['from'], $win['to']);
        $tab = $this->currentTab($counts);
        $groups = $tab === 'waiting' ? SalesInsights::waitingGroups($filters) : [];
        $flat = $tab === 'waiting' ? null : SalesInsights::expenseList($tab === 'all' ? null : $tab, $win['from'], $win['to'], $filters);
        $rows = $this->visibleRows($tab, $groups, $flat);

        return ['win' => $win, 'filters' => $filters, 'counts' => $counts, 'activeTab' => $tab, 'groups' => $groups, 'flat' => $flat, 'rows' => $rows];
    }

    /** The expense open in the detail pane: the one asked for (when it is in the list), else the first. */
    private function currentOf(Collection $rows): ?SalesExpense
    {
        return $rows->firstWhere('uuid', $this->expense) ?? $rows->first();
    }

    // ---- small actions -------------------------------------------------------------------------------------------

    public function setTab(string $tab): void
    {
        static::authorizeResourceAccess();

        $this->tab = in_array($tab, self::TABS, true) ? $tab : 'waiting';
        $this->selected = [];
        $this->expense = null;
    }

    public function selectExpense(string $uuid): void
    {
        static::authorizeResourceAccess();

        $this->expense = $this->load()['rows']->contains('uuid', $uuid) ? $uuid : $this->expense;
    }

    public function moveSelection(int $step): void
    {
        static::authorizeResourceAccess();

        $rows = $this->load()['rows'];

        if ($rows->isEmpty()) {
            return;
        }

        $at = (int) $rows->search(fn (SalesExpense $e) => $e->uuid === $this->expense);
        $this->expense = $rows->get(max(0, min($rows->count() - 1, $at + ($step >= 0 ? 1 : -1))))?->uuid;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    // a filter, the search or the period changed: what was ticked or open may be out of sight now, so it is let go
    public function updatedRep(): void { $this->forgetPicks(); }

    public function updatedCategory(): void { $this->forgetPicks(); }

    public function updatedArea(): void { $this->forgetPicks(); }

    public function updatedBill(): void { $this->forgetPicks(); }

    public function updatedSearch(): void { $this->forgetPicks(); }

    public function updatedFrom(): void { $this->forgetPicks(); }

    public function updatedUntil(): void { $this->forgetPicks(); }

    /** Picking a month leaves the custom range, which would otherwise win over it. */
    public function updatedPeriod(): void
    {
        $this->from = null;
        $this->until = null;
        $this->forgetPicks();
    }

    private function forgetPicks(): void
    {
        $this->selected = [];
        $this->expense = null;
    }

    // ---- decisions ---------------------------------------------------------------------------------------------

    /** Approve the expense open in the pane (key A): the server checks again that it waits and that this manager may review it. */
    public function approveCurrent(?string $shown = null): void
    {
        static::authorizeResourceAccess();

        $data = $this->load();
        // the row the manager SAW when pressing the key (the page may have moved on since): it must be in the list
        $current = $shown ? $data['rows']->firstWhere('uuid', $shown) : $this->currentOf($data['rows']);

        if ($current) {
            $this->approveOne($current->uuid);
        }
    }

    public function approveOne(string $uuid): void
    {
        static::authorizeResourceAccess();

        $expense = SalesExpense::where('uuid', $uuid)->first();
        abort_unless($expense, 404);

        $next = $this->nextWaiting($uuid);
        $result = ExpenseApproval::approve(auth()->user(), $expense->id);

        Notification::make()->title($result === 'approved' ? 'Expense approved' : 'That expense was already decided')->{$result === 'approved' ? 'success' : 'warning'}()->send();
        $this->expense = $next;
        $this->selected = array_values(array_diff($this->selected, [$uuid]));
    }

    /** The key R / the Reject button: opens the window; the reason is asked there. */
    public function rejectCurrent(?string $shown = null): void
    {
        static::authorizeResourceAccess();

        $rows = $this->load()['rows'];
        $current = $shown ? $rows->firstWhere('uuid', $shown) : $this->currentOf($rows);

        if ($current?->isPending() && auth()->user()?->can('review', $current)) {
            $this->mountAction('reject', ['uuid' => $current->uuid]);
        }
    }

    /** After a decision the pane moves to the next waiting row of the list (or the one before it when it was the last). */
    private function nextWaiting(string $uuid): ?string
    {
        $rows = $this->load()['rows']->filter(fn (SalesExpense $e) => $e->isPending())->values();
        $at = $rows->search(fn (SalesExpense $e) => $e->uuid === $uuid);

        if ($at === false) {
            return $this->expense;
        }

        return ($rows->get($at + 1) ?? $rows->get($at - 1))?->uuid;
    }

    public function rejectAction(): Action
    {
        $find = fn (array $arguments) => SalesExpense::with('user')->where('uuid', (string) ($arguments['uuid'] ?? ''))->first();

        return Action::make('reject')
            ->modalHeading('Reject this expense')
            ->modalDescription(function (array $arguments) use ($find) {
                $e = $find($arguments);

                return $e ? $e->user->name . ' · ' . SalesExpense::categoryLabel($e->category) . ' · ' . $e->expense_date->format('j M') . ' · ' . Money::rupees($e->amount_paise, false) . ' · ' . $this->billText($e) : '';
            })
            ->modalSubmitActionLabel('Reject expense')
            ->modalCancelActionLabel('Cancel')
            ->color('danger')
            ->schema(function (array $arguments) use ($find) {
                $name = $find($arguments)?->user?->name ?? 'The rep';

                return [
                    ToggleButtons::make('quick')->label('Quick reasons')->inline()->live()
                        ->options(collect(self::REASONS)->mapWithKeys(fn (string $r) => [$r => __('sales_expenses.reasons.' . $r)])->all())
                        ->afterStateUpdated(fn ($state, $set) => $state ? $set('reason', __('sales_expenses.reasons.' . $state)) : null)
                        ->dehydrated(false),
                    Textarea::make('reason')->label('Reason')->required()->minLength(3)->maxLength(500)->rows(3)->live(debounce: 300)
                        ->hint(fn (?string $state) => mb_strlen((string) $state) . ' / 500')
                        ->helperText($name . ' sees this reason on the phone. ' . __('sales_expenses.locked_note', ['name' => $name])),
                ];
            })
            ->action(function (array $data, array $arguments) {
                static::authorizeResourceAccess();

                $expense = SalesExpense::where('uuid', (string) ($arguments['uuid'] ?? ''))->first();
                abort_unless($expense, 404);

                if (mb_strlen(trim((string) $data['reason'])) < 3) {
                    Notification::make()->title('Write a reason of at least 3 characters')->warning()->send();

                    return;
                }

                $next = $this->nextWaiting($expense->uuid);
                $result = ExpenseApproval::reject(auth()->user(), $expense->id, (string) $data['reason']);

                Notification::make()->title($result === 'rejected' ? 'Expense rejected' : 'That expense was already decided')->{$result === 'rejected' ? 'success' : 'warning'}()->send();
                $this->expense = $next;
                $this->selected = array_values(array_diff($this->selected, [$expense->uuid]));
            });
    }

    /** "Approve all N": only what is waiting, says it has a bill and has its photo; the question shows what will be approved. */
    public function approveAllAction(): Action
    {
        return Action::make('approveAll')
            ->label('Approve all')
            ->requiresConfirmation()
            ->mountUsing(function () {
                $ready = collect(SalesInsights::waitingGroups($this->filters()))->firstWhere('key', 'ready');
                $this->approveAllIds = $ready ? $ready['rows']->filter(fn (SalesExpense $e) => auth()->user()?->can('review', $e))->pluck('id')->all() : [];
            })
            ->modalHeading(function () {
                $rows = SalesExpense::whereIn('id', $this->approveAllIds)->get();

                return 'Approve ' . $rows->count() . ' ' . str('expense')->plural($rows->count()) . ' worth ' . Money::rupees((int) $rows->sum('amount_paise'), false) . '?';
            })
            ->modalDescription('Only expenses that say they have a bill, with the photo here, are approved. Nothing else is touched.')
            ->modalSubmitActionLabel('Approve')
            ->action(function () {
                static::authorizeResourceAccess();

                $ids = $this->approveAllIds;
                $this->approveAllIds = [];
                $this->reportApproved(ExpenseApproval::approveWithBill(auth()->user(), $ids));
            });
    }

    /** The bar of ticked rows: approves the ones that can be approved safely and says how many were skipped. */
    public function approveSelectedAction(): Action
    {
        return Action::make('approveSelected')
            ->label('Approve')
            ->requiresConfirmation()
            ->modalHeading(fn () => 'Approve the selected expenses?')
            ->modalDescription('Only expenses that are still waiting, say they have a bill and have the photo here are approved. The others are skipped.')
            ->modalSubmitActionLabel('Approve')
            ->action(function () {
                static::authorizeResourceAccess();

                // only what is ticked AND still in the list the manager sees
                $visible = $this->load()['rows']->pluck('uuid')->all();
                $ids = SalesExpense::whereIn('uuid', array_intersect(array_slice(array_map('strval', $this->selected), 0, 500), $visible))->pluck('id')->all();
                $this->selected = [];
                $this->reportApproved(ExpenseApproval::approveWithBill(auth()->user(), $ids));
            });
    }

    private function reportApproved(array $result): void
    {
        $n = $result['approved'];
        $text = $n . ' ' . str('expense')->plural($n) . ' approved' . ($result['skipped'] > 0 ? ', ' . $result['skipped'] . ' skipped' : '');
        Notification::make()->title($text)->{$n > 0 ? 'success' : 'warning'}()->send();
        $this->expense = null;
    }

    public function exportAction(): Action
    {
        return Action::make('export')
            ->label('Export')
            ->color('gray')
            ->modalHeading('Export expenses')
            ->modalDescription('Downloads every expense that matches the tab, period and filters you have set now.')
            ->modalSubmitActionLabel('Download')
            ->fillForm(['format' => 'xlsx'])
            ->schema([$this->formatSelect()])
            ->action(function (array $data) {
                static::authorizeResourceAccess();

                $period = $this->period();
                $tab = $this->currentTab(SalesInsights::expenseCounts($period['from'], $period['to']));
                $query = $tab === 'waiting' ? SalesInsights::expenseQuery($this->filters(), SalesExpense::PENDING)
                    : SalesInsights::expenseQuery($this->filters(), $tab === 'all' ? null : $tab, $period['from'], $period['to']);

                return SalesExporter::expenses($query, $data['format']);
            });
    }

    public function exportSelectedAction(): Action
    {
        return Action::make('exportSelected')
            ->label('Export selected')
            ->color('gray')
            ->modalHeading('Export the selected expenses')
            ->modalSubmitActionLabel('Download')
            ->fillForm(['format' => 'xlsx'])
            ->schema([$this->formatSelect()])
            ->action(function (array $data) {
                static::authorizeResourceAccess();

                $visible = $this->load()['rows']->pluck('uuid')->all();

                return SalesExporter::expenses(SalesExpense::whereIn('uuid', array_intersect(array_slice(array_map('strval', $this->selected), 0, 500), $visible)), $data['format']);
            });
    }

    private function formatSelect(): Select
    {
        return Select::make('format')->label('File type')->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])->required()->native(false);
    }

    // ---- view ------------------------------------------------------------------------------------------------------

    /** "With bill" / "No bill" / "Photo not here yet" / "Not told": what the rep said about the bill. */
    private function billText(SalesExpense $e): string
    {
        return match (true) {
            $e->bill_type === null => 'Not told',
            $e->bill_type === 'without_bill' => 'No bill',
            $e->hasBill() => 'Bill photo',
            default => 'Photo not here yet',
        };
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $data = $this->load();
        $rows = $data['rows'];
        $current = $this->currentOf($rows);
        $win = $data['win'];

        $detail = null;
        if ($current) {
            $profile = $current->user?->salesProfile;
            $detail = [
                'expense' => $current,
                'photo' => $current->hasBill() && Storage::disk('local')->exists($current->bill_path),
                'flags' => $current->isPending() ? SalesInsights::expenseFlags($current) : [],
                'context' => SalesInsights::repExpenseContext((int) $current->user_id, $win['from'], $win['to']),
                'profile' => $profile,
                'profile_url' => $profile && $user?->canManage('sales-reps') ? SalesRepResource::getUrl('view', ['record' => $profile->id]) : null,
                'can_review' => $current->isPending() && $user?->can('review', $current),
                'position' => $current->isPending() ? ($rows->filter(fn (SalesExpense $e) => $e->isPending())->values()->search(fn (SalesExpense $e) => $e->uuid === $current->uuid) + 1) : null,
                'waiting_total' => $rows->filter(fn (SalesExpense $e) => $e->isPending())->count(),
            ];
        }

        return $data + [
            'summary' => SalesInsights::expensePeriodSummary($win['from'], $win['to']),
            'months' => SalesInsights::expenseMonths(),
            'monthKey' => $this->period && isset(SalesInsights::expenseMonths()[$this->period]) ? $this->period : now(config('sales.timezone'))->format('Y-m'),
            'reps' => SalesProfile::with('user')->get()->mapWithKeys(fn (SalesProfile $p) => [$p->user_id => $p->user->name])->sort()->all(),
            'categories' => collect(config('sales.expense_categories'))->mapWithKeys(fn (string $c) => [$c => SalesExpense::categoryLabel($c)])->all(),
            'current' => $current,
            'detail' => $detail,
            'selectedRows' => $rows->filter(fn (SalesExpense $e) => in_array($e->uuid, $this->selected, true)),
            'billText' => fn (SalesExpense $e) => $this->billText($e),
            'short' => fn (int $paise) => Money::short($paise),
            'rupees' => fn (int $paise) => Money::rupees($paise, false),
        ];
    }
}
