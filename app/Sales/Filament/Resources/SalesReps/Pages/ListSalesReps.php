<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\Money;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\RepAccount;
use App\Sales\Support\SalesInsights;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;

/**
 * The roster: where each rep is today, when their phone last synced and who needs a word. Every figure comes from
 * SalesInsights::repRoster (a fixed number of queries); what a manager may not see (money, expenses, enquiries, reports)
 * is removed from the data here, not only hidden in the markup.
 */
class ListSalesReps extends Page
{
    protected static string $resource = SalesRepResource::class;

    protected string $view = 'filament.sales.reps.roster';

    public const FILTERS = ['all', 'active', 'off', 'attention'];

    public const SORTS = ['order', 'code', 'name', 'sync', 'spend', 'meetings'];

    #[Url(as: 'show')]
    public string $filter = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $sort = 'order';

    #[Url]
    public string $dir = 'asc';

    public bool $showEmail = false;

    public function getTitle(): string
    {
        return 'Reps';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function setFilter(string $filter): void
    {
        static::authorizeResourceAccess();

        $this->filter = in_array($filter, self::FILTERS, true) ? $filter : 'all';
    }

    public function sortBy(string $key): void
    {
        static::authorizeResourceAccess();

        if (! in_array($key, self::SORTS, true)) {
            return;
        }

        $this->dir = $key !== 'order' && $this->sort === $key && $this->dir === 'asc' ? 'desc' : 'asc';
        $this->sort = $key;
    }

    /**
     * A row was dragged: move rep `$id` to `$position` (0 = first) in the whole list and remember the order. Only on the whole, unfiltered list in the
     * custom order; the server checks that, whatever the browser sends.
     */
    public function moveRep(int $id, int $position): void
    {
        static::authorizeResourceAccess();

        if ($this->filter !== 'all' || trim($this->search) !== '' || $this->sort !== 'order' || $this->dir === 'desc') {
            return;
        }

        $ids = SalesInsights::repRoster()->pluck('id')->all();
        $from = array_search($id, $ids, true);

        if ($from === false) {
            return;
        }

        array_splice($ids, $from, 1);
        array_splice($ids, max(0, min(count($ids), $position)), 0, [$id]);

        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $profileId) {
                // the query builder, not Eloquent: no timestamp is touched ("Switched off <date>" reads updated_at)
                SalesProfile::whereKey($profileId)->toBase()->update(['sort_order' => $i + 1]);
            }
        });
    }

    /** Delete a rep and everything they own (only a rep who is switched off; the window asks for the employee code). */
    public function deleteRepAction(): Action
    {
        return SalesRepResource::deleteAction(fn (array $arguments) => SalesProfile::with('user')->findOrFail((int) ($arguments['id'] ?? 0)));
    }

    /** Off needs a question first (the phone is signed out); on needs none. */
    public function toggleActive(int $profileId): void
    {
        static::authorizeResourceAccess();

        $profile = SalesProfile::with('user')->findOrFail($profileId);

        if ($profile->is_active) {
            $this->mountAction('switchOff', ['id' => $profileId]);

            return;
        }

        RepAccount::setActive($profile, true);
        Notification::make()->title($profile->user->name . ' is active again')->success()->send();
    }

    public function switchOffAction(): Action
    {
        return Action::make('switchOff')
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => 'Switch off ' . (SalesProfile::with('user')->find((int) ($arguments['id'] ?? 0))?->user?->name ?? 'this rep') . '?')
            ->modalDescription('Their phone is signed out on its next sync. Their records stay.')
            ->modalSubmitActionLabel('Switch off')
            ->color('danger')
            ->action(function (array $arguments) {
                static::authorizeResourceAccess();

                $profile = SalesProfile::with('user')->findOrFail((int) ($arguments['id'] ?? 0));
                RepAccount::setActive($profile, false);
                Notification::make()->title($profile->user->name . ' is switched off')->success()->send();
            });
    }

    /** What this manager may see of each rep: an area they may not open is removed from the rows and from the "needs attention" reasons. */
    private function rows(): Collection
    {
        $user = auth()->user();
        $can = fn (string $area) => (bool) $user?->canManage($area);
        $reasonArea = ['pending' => 'sales-expenses', 'late' => 'sales-enquiries', 'report' => 'sales-reports'];

        return SalesInsights::repRoster()->map(function (array $r) use ($can, $reasonArea) {
            if (! $can('sales-expenses')) {
                $r['month_spend_paise'] = null;
                $r['pending'] = null;
            }
            if (! $can('sales-meetings')) {
                $r['meetings_month'] = null;
            }
            if (! $can('sales-enquiries')) {
                $r['enquiries_open'] = null;
                $r['enquiries_late'] = null;
            }
            if (! $can('sales-clients')) {
                $r['clients'] = null;
            }
            if (! $can('sales-meetings') && $r['state'] === 'field') {
                // where a rep is today comes from their meetings: without that right it is just "Office"
                $r['state'] = 'office';
                $r['status_line'] = 'Office' . (str_contains($r['status_line'], ' · report not sent') ? ' · report not sent' : '');
            }
            if (! $can('sales-reports')) {
                $r['status_line'] = str_replace(' · report not sent', '', $r['status_line']);
            }

            $r['attention'] = array_filter($r['attention'], fn (string $key) => ! isset($reasonArea[$key]) || $can($reasonArea[$key]), ARRAY_FILTER_USE_KEY);

            return $r;
        });
    }

    protected function getViewData(): array
    {
        $user = auth()->user();
        $can = fn (string $area) => (bool) $user?->canManage($area);
        $all = $this->rows();

        $counts = [
            'all' => $all->count(),
            'active' => $all->where('active', true)->count(),
            'off' => $all->where('active', false)->count(),
            'attention' => $all->filter(fn (array $r) => $r['attention'] !== [])->count(),
        ];

        $filter = in_array($this->filter, self::FILTERS, true) ? $this->filter : 'all';
        $rows = match ($filter) {
            'active' => $all->where('active', true),
            'off' => $all->where('active', false),
            'attention' => $all->filter(fn (array $r) => $r['attention'] !== []),
            default => $all,
        };

        $needle = mb_strtolower(trim($this->search));
        if ($needle !== '') {
            // digits are matched against phone numbers only when the search has no letters ("AB-0412" is a code, not a number)
            $digits = preg_match('/[a-z]/i', $needle) ? '' : preg_replace('/\D+/', '', $needle);
            $rows = $rows->filter(fn (array $r) => str_contains(mb_strtolower($r['name']), $needle)
                || str_contains(mb_strtolower($r['code']), $needle)
                || str_contains(mb_strtolower((string) $r['email']), $needle)
                || ($digits !== '' && str_contains(preg_replace('/\D+/', '', (string) $r['phone']), $digits)));
        }

        $sort = in_array($this->sort, self::SORTS, true) ? $this->sort : 'order';
        $key = ['order' => 'sort_order', 'code' => 'code', 'name' => 'name', 'sync' => 'last_sync_at', 'spend' => 'month_spend_paise', 'meetings' => 'meetings_month'][$sort];
        $text = in_array($sort, ['code', 'name'], true);
        // "order" is the order a manager set by dragging: the roster already comes in it
        $rows = $sort === 'order' ? ($this->dir === 'desc' ? $rows->reverse()->values() : $rows->values()) : $rows->sortBy(function (array $r) use ($key, $text) {
            $value = $r[$key] ?? null;

            return $value instanceof \DateTimeInterface ? $value->getTimestamp() : ($value ?? ($text ? '' : -1));
        }, $text ? SORT_NATURAL | SORT_FLAG_CASE : SORT_REGULAR, $this->dir === 'desc')->values();

        return [
            'rows' => $rows,
            // dragging only makes sense on the whole list in the custom order (not filtered, not searched)
            'canReorder' => $sort === 'order' && $this->dir !== 'desc' && $filter === 'all' && $needle === '',
            'counts' => $counts,
            'activeFilter' => $filter,
            'activeSort' => $sort,
            'activeDir' => $this->dir === 'desc' ? 'desc' : 'asc',
            'subline' => $counts['all'] . ' ' . ($counts['all'] === 1 ? 'rep' : 'reps') . ' · ' . $counts['active'] . ' active · ' . $counts['off'] . ' switched off',
            'showMoney' => $can('sales-expenses'),
            'showMeetings' => $can('sales-meetings'),
            'showEnquiries' => $can('sales-enquiries'),
            'showClients' => $can('sales-clients'),
            'monthName' => now(config('sales.timezone'))->format('F'),
            'createUrl' => SalesRepResource::getUrl('create'),
            'profileUrl' => fn (int $id) => SalesRepResource::getUrl('view', ['record' => $id]),
            'money' => fn (int $paise) => Money::short($paise),
            'phoneText' => fn (?string $phone) => PhoneNumber::display($phone),
        ];
    }
}
