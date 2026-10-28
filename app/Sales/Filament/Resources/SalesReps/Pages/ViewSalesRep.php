<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Models\SalesExpense;
use App\Sales\Support\ExpenseApproval;
use App\Sales\Support\Money;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\RepAccount;
use App\Sales\Support\SalesInsights;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;

/**
 * One rep's profile: Overview, Activity, Expenses, Clients and Account. Every figure comes from SalesInsights; a tab or a figure
 * that belongs to an area this manager may not open (expenses, meetings, calls, enquiries, clients, reports) is not built at all,
 * and every action checks the right again on the server.
 */
class ViewSalesRep extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SalesRepResource::class;

    protected string $view = 'filament.sales.reps.profile';

    /** tab key => [label, the area that unlocks it (null = always)] */
    public const TABS = [
        'overview' => ['Overview', null],
        'activity' => ['Activity', null],
        'expenses' => ['Expenses', 'sales-expenses'],
        'clients' => ['Clients', 'sales-clients'],
        'account' => ['Account', null],
    ];

    /** activity type => the area that unlocks it */
    public const ACTIVITY = ['meeting' => 'sales-meetings', 'call' => 'sales-calls', 'enquiry' => 'sales-enquiries', 'report' => 'sales-reports'];

    #[Url]
    public string $tab = 'overview';

    #[Url(as: 'type')]
    public string $activityType = 'all';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string
    {
        return $this->record->user->name;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    private function allows(string $area): bool
    {
        return (bool) auth()->user()?->canManage($area);
    }

    /** @return list<string> the tabs this manager may open, in order */
    private function visibleTabs(): array
    {
        $tabs = array_keys(array_filter(self::TABS, fn (array $t) => $t[1] === null || $this->allows($t[1])));

        // Activity is only useful when at least one kind of record may be read
        return array_values(array_filter($tabs, fn (string $k) => $k !== 'activity' || $this->activityTypes() !== []));
    }

    /** @return list<string> */
    private function activityTypes(): array
    {
        return array_keys(array_filter(self::ACTIVITY, fn (string $area) => $this->allows($area)));
    }

    public function setTab(string $tab): void
    {
        static::authorizeResourceAccess();

        $this->tab = in_array($tab, $this->visibleTabs(), true) ? $tab : 'overview';
    }

    public function setActivityType(string $type): void
    {
        static::authorizeResourceAccess();

        $this->activityType = in_array($type, $this->activityTypes(), true) ? $type : 'all';
    }

    /** Approve one waiting expense of THIS rep (the shared rules: right, still waiting, never one's own, audit trail). */
    public function approveExpense(int $id): void
    {
        static::authorizeResourceAccess();
        abort_unless($this->allows('sales-expenses'), 403);

        $belongs = SalesExpense::whereKey($id)->where('user_id', $this->record->user_id)->exists();
        abort_unless($belongs, 404);

        $result = ExpenseApproval::approve(auth()->user(), $id);

        Notification::make()->title($result === 'approved' ? 'Expense approved' : 'That expense was already decided')->{$result === 'approved' ? 'success' : 'warning'}()->send();
    }

    public function switchOn(): void
    {
        static::authorizeResourceAccess();

        RepAccount::setActive($this->record, true);
        $this->record->refresh();
        Notification::make()->title($this->record->user->name . ' is active again')->success()->send();
    }

    public function resetPasswordAction(): Action
    {
        return SalesRepResource::resetPasswordAction(fn () => $this->record);
    }

    public function deleteRepAction(): Action
    {
        return SalesRepResource::deleteAction(fn () => $this->record, fn () => $this->redirect(SalesRepResource::getUrl('index')));
    }

    public function switchOffAction(): Action
    {
        return SalesRepResource::switchOffAction(fn () => $this->record);
    }

    protected function getViewData(): array
    {
        $can = fn (string $area) => $this->allows($area);
        $tabs = $this->visibleTabs();
        $tab = in_array($this->tab, $tabs, true) ? $this->tab : 'overview';
        $uid = (int) $this->record->user_id;

        $p = SalesInsights::repProfile($uid, 30) ?? abort(404);

        // what this manager may not open is taken out of the data, not only hidden
        $reasonArea = ['pending' => 'sales-expenses', 'late' => 'sales-enquiries', 'report' => 'sales-reports'];
        $p['attention'] = array_filter($p['attention'], fn (string $key) => ! isset($reasonArea[$key]) || $can($reasonArea[$key]), ARRAY_FILTER_USE_KEY);
        if (! $can('sales-meetings') && $p['state'] === 'field') {
            // where a rep is today comes from their meetings: without that right it is just "Office"
            $p['state'] = 'office';
            $p['status_line'] = 'Office' . (str_contains($p['status_line'], ' · report not sent') ? ' · report not sent' : '');
        }
        if (! $can('sales-meetings')) {
            $p['route_title'] = null;
        }
        if (! $can('sales-reports')) {
            $p['status_line'] = str_replace(' · report not sent', '', $p['status_line']);
            $p['report'] = null;
        }
        if (! $can('sales-expenses')) {
            foreach (['month_spend_paise', 'pending', 'pending_count'] as $key) {
                $p[$key] = null;
            }
            unset($p['kpis']['spent']);
        }
        if (! $can('sales-meetings')) {
            unset($p['kpis']['meetings']);
            $p['visits'] = null;
            $p['chart']['meetings'] = null;
        }
        if (! $can('sales-calls')) {
            unset($p['kpis']['calls']);
            $p['chart']['calls'] = null;
        }
        if (! $can('sales-enquiries')) {
            unset($p['kpis']['won']);
            $p['enquiries'] = null;
        }
        if (! $can('sales-clients')) {
            $p['clients_count'] = null;
        }

        $types = $this->activityTypes();
        $shown = $this->activityType !== 'all' && in_array($this->activityType, $types, true) ? [$this->activityType] : $types;

        return [
            'p' => $p,
            'activeTab' => $tab,
            'tabs' => collect($tabs)->mapWithKeys(fn (string $k) => [$k => self::TABS[$k][0]])->all(),
            'badges' => ['expenses' => $can('sales-expenses') ? $p['pending_count'] : null, 'clients' => $can('sales-clients') ? $p['clients_count'] : null],
            'activityTypes' => $types,
            'activeType' => $this->activityType !== 'all' && in_array($this->activityType, $types, true) ? $this->activityType : 'all',
            'activity' => $tab === 'activity' && $types !== [] ? SalesInsights::repActivity($uid, 30, $shown) : [],
            'expenses' => $tab === 'expenses' && $can('sales-expenses') ? SalesInsights::repExpenses($uid) : collect(),
            'clients' => $tab === 'clients' && $can('sales-clients') ? SalesInsights::repClients($uid) : collect(),
            'can' => $can,
            'money' => fn (int $paise) => Money::short($paise),
            'rupees' => fn (int $paise) => Money::rupees($paise, false),
            'phoneText' => PhoneNumber::display($p['phone']),
            'listUrl' => SalesRepResource::getUrl('index'),
            'editUrl' => SalesRepResource::getUrl('edit', ['record' => $this->record]),
            'talk' => fn (int $seconds) => SalesInsights::duration($seconds),
            'userId' => $uid,
        ];
    }
}
