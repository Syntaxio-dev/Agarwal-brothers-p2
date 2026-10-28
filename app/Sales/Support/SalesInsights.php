<?php

namespace App\Sales\Support;

use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesProduct;
use App\Sales\Models\SalesProfile;
use App\Sales\Models\SalesRoute;
use App\Sales\Models\SalesScript;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * The numbers behind the "Sales management" page. One place, so the page and the tests read the same figures, and so
 * every new feature of the app can add its own insight here. Days are business days (Asia/Kolkata): an expense dated
 * "today" is the rep's today, and "not synced today" means no sync since midnight in India.
 */
class SalesInsights
{
    public const RANGES = [7, 30, 90];

    public static function tz(): string
    {
        return (string) config('sales.timezone');
    }

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now(static::tz())->startOfDay();
    }

    /** First day of an N-day window that ends today. */
    public static function from(int $days): CarbonImmutable
    {
        return static::today()->subDays($days - 1);
    }

    /** Start of the window as a UTC moment (meetings and syncs are stored with a time). */
    private static function fromUtc(int $days): CarbonImmutable
    {
        return static::from($days)->utc();
    }

    /** @return array<string, mixed> the headline figures */
    public static function summary(int $days): array
    {
        $expenses = SalesExpense::whereBetween('expense_date', [static::from($days)->toDateString(), static::today()->toDateString()]);
        $pending = SalesExpense::where('status', SalesExpense::PENDING);

        return [
            'spend_paise' => (int) (clone $expenses)->sum('amount_paise'),
            'expense_count' => (int) (clone $expenses)->count(),
            'approved_paise' => (int) (clone $expenses)->where('status', SalesExpense::APPROVED)->sum('amount_paise'),
            'pending_count' => (int) (clone $pending)->count(),
            'pending_paise' => (int) (clone $pending)->sum('amount_paise'),
            // a meeting that happened (an old meeting is "done"); a planned one is counted apart below
            'meetings' => (int) SalesMeeting::where('status', 'done')->where('meeting_at', '>=', static::fromUtc($days))->count(),
            'meetings_today' => (int) SalesMeeting::where('status', 'done')->where('meeting_at', '>=', static::today()->utc())->where('meeting_at', '<', static::today()->addDay()->utc())->count(),
            'meetings_planned' => (int) SalesMeeting::whereIn('status', SalesMeeting::OPEN)->where('meeting_at', '>=', now())->count(),
            'meetings_not_logged' => (int) SalesMeeting::whereIn('status', SalesMeeting::OPEN)->where('meeting_at', '<', now()->subHour())->count(),
            'meetings_missed' => (int) SalesMeeting::where('status', 'missed')->where('meeting_at', '>=', static::fromUtc($days))->count(),
            'reps_active' => (int) SalesProfile::active()->count(),
            'reps_not_synced_today' => static::staleReps()->count(),
            'followups_due' => (int) static::followupQuery()->count(),
        ] + static::callFigures($days) + static::poolFigures() + static::enquiryFigures($days) + static::reportFigures();
    }

    /** Enquiries: what is open, what is late, what came in and what was decided in the window. */
    private static function enquiryFigures(int $days): array
    {
        $open = SalesEnquiry::whereIn('status', SalesEnquiry::OPEN);
        $from = static::fromUtc($days);

        return [
            'enquiries_open' => (int) (clone $open)->count(),
            'enquiries_overdue' => (int) (clone $open)->where('next_followup_at', '<', now())->count(),
            'enquiries_due_today' => (int) (clone $open)->whereBetween('next_followup_at', [now(), static::today()->addDay()->utc()])->count(),
            // the same rule as the app: open, no follow-up date, and nobody has touched it for 3 days
            'enquiries_idle' => (int) (clone $open)->whereNull('next_followup_at')->where('client_updated_at', '<', now()->subDays(3))->count(),
            'enquiries_new' => (int) SalesEnquiry::where('opened_at', '>=', $from)->count(),
            'enquiries_won' => (int) SalesEnquiry::where('status', 'won')->where('closed_at', '>=', $from)->count(),
            'enquiries_lost' => (int) SalesEnquiry::where('status', 'lost')->where('closed_at', '>=', $from)->count(),
        ];
    }

    /** @return array<string, int> open enquiries per status, in the order of the pipeline */
    public static function enquiriesByStatus(): array
    {
        $counts = SalesEnquiry::whereIn('status', SalesEnquiry::OPEN)->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return collect(SalesEnquiry::OPEN)->mapWithKeys(fn ($s) => [$s => (int) ($counts[$s] ?? 0)])->all();
    }

    /** @return list<array{product: string, count: int}> what the clients ask for most in the window */
    public static function topProducts(int $days, int $limit = 8): array
    {
        return SalesEnquiry::where('opened_at', '>=', static::fromUtc($days))->orderByDesc('opened_at')->limit(500)->get()
            ->flatMap(fn (SalesEnquiry $e) => collect($e->items ?? [])->pluck('product'))
            ->map(fn ($p) => trim((string) $p))->filter()
            ->groupBy(fn ($p) => mb_strtolower($p))
            ->map(fn ($group) => ['product' => $group->first(), 'count' => $group->count()])
            ->sortByDesc('count')->take($limit)->values()->all();
    }

    /** The enquiries that need action first: overdue follow-ups, oldest first. */
    public static function overdueEnquiries(int $limit = 8): Collection
    {
        return SalesEnquiry::with('user')->whereIn('status', SalesEnquiry::OPEN)->where('next_followup_at', '<', now())->orderBy('next_followup_at')->limit($limit)->get();
    }

    /** Daily reports: who has sent today's, who has not. */
    private static function reportFigures(): array
    {
        $today = static::today()->toDateString();

        return [
            'reports_today' => (int) SalesDailyReport::whereDate('report_date', $today)->whereNotNull('submitted_at')->count(),
        ];
    }

    /** Active reps who have not sent a report for today yet. */
    public static function repsWithoutReport(): Collection
    {
        $sent = SalesDailyReport::whereDate('report_date', static::today()->toDateString())->whereNotNull('submitted_at')->pluck('user_id');

        return SalesProfile::active()->with('user')->whereNotIn('user_id', $sent)->get();
    }

    /** The enquiries a rep worked on during one business day: opened that day, or with a note or a status change that day. */
    public static function enquiriesWorked(int $userId, string $date): Collection
    {
        $tz = static::tz();
        $start = CarbonImmutable::parse($date, $tz)->startOfDay();
        $end = $start->endOfDay();

        return SalesEnquiry::where('user_id', $userId)->where('client_updated_at', '>=', $start->utc())->orderBy('opened_at')->limit(300)->get()
            ->filter(function (SalesEnquiry $e) use ($start, $end, $tz) {
                if ($e->opened_at && $e->opened_at->between($start, $end)) {
                    return true;
                }

                return collect($e->timeline ?? [])->contains(fn ($t) => CarbonImmutable::parse($t['at'])->timezone($tz)->between($start, $end));
            })->values();
    }

    /** Calls in the window (only calls with saved client numbers ever reach the server). */
    private static function callFigures(int $days): array
    {
        $calls = SalesCall::where('started_at', '>=', static::fromUtc($days));

        return [
            'calls' => (int) (clone $calls)->count(),
            'calls_out' => (int) (clone $calls)->where('direction', 'outgoing')->count(),
            'calls_in' => (int) (clone $calls)->where('direction', 'incoming')->count(),
            'calls_missed' => (int) (clone $calls)->where('direction', 'missed')->count(),
            'calls_today' => (int) SalesCall::where('started_at', '>=', static::today()->utc())->count(),
            'talk_seconds' => (int) (clone $calls)->sum('duration_seconds'),
            'clients_called' => (int) (clone $calls)->whereNotNull('client_uuid')->distinct()->count('client_uuid'),
        ];
    }

    /** The shared client pool as a whole. */
    private static function poolFigures(): array
    {
        return [
            'clients' => SalesClient::count(),
            'people' => SalesContact::count(),
            'people_alone' => SalesContact::whereNull('client_uuid')->count(), // customers who buy on their own, with no company
            'clients_without_people' => SalesClient::whereDoesntHave('contacts')->count(),
            'clients_unassigned' => SalesClient::whereNull('assigned_to')->count(),
            'scripts' => SalesScript::count(), // the reps' own pitches for a product
            'scripts_reps' => (int) SalesScript::query()->distinct()->count('user_id'),
            'products_briefed' => SalesProduct::query()->where('is_active', true)->whereNotNull('pitch')->where('pitch', '!=', '')->count(),
        ];
    }

    /**
     * Clients nobody called or met in the last `$days` days (the ones that are slipping away), oldest contact first.
     *
     * @return Collection<int, SalesClient>
     */
    public static function neglectedClients(int $days = 30, int $limit = 8): Collection
    {
        $since = static::today()->subDays($days)->utc();

        return static::neglectedQuery($since)
            ->with('assignee')
            ->withMax('calls', 'started_at')
            ->withMax(['meetings' => fn ($q) => $q->where('status', 'done')], 'meeting_at')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->each(function (SalesClient $c) {
                // days since the last call or meeting (null = never reached at all)
                $last = collect([$c->calls_max_started_at, $c->meetings_max_meeting_at])->filter()->map(fn ($t) => CarbonImmutable::parse($t, 'UTC'))->max();
                $c->setAttribute('days_since_contact', $last ? (int) $last->diffInDays(now(), true) : null);
            });
    }

    /** How many clients nobody called or met in the last `$days` days (the whole number, not just the page shown). */
    public static function neglectedTotal(int $days = 30): int
    {
        return (int) static::neglectedQuery(static::today()->subDays($days)->utc())->count();
    }

    private static function neglectedQuery(CarbonImmutable $since)
    {
        return SalesClient::query()
            ->whereDoesntHave('calls', fn ($q) => $q->where('started_at', '>=', $since))
            ->whereDoesntHave('meetings', fn ($q) => $q->where('status', 'done')->where('meeting_at', '>=', $since))
            ->where('created_at', '<', $since);   // a client added this week is not "neglected" yet
    }

    /** Talk time as "1h 05m" / "7m". */
    public static function duration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);

        return $minutes >= 60 ? intdiv($minutes, 60) . 'h ' . str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT) . 'm' : $minutes . 'm';
    }

    /** @return list<array{date: string, label: string, paise: int}> one entry per day, zero when nothing was spent */
    public static function daily(int $days): array
    {
        $totals = SalesExpense::selectRaw('expense_date as d, SUM(amount_paise) as total')
            ->whereBetween('expense_date', [static::from($days)->toDateString(), static::today()->toDateString()])
            ->groupBy('expense_date')->pluck('total', 'd')
            ->mapWithKeys(fn ($total, $d) => [CarbonImmutable::parse($d)->toDateString() => (int) $total]);

        return collect(range(0, $days - 1))->map(function (int $i) use ($days, $totals) {
            $day = static::from($days)->addDays($i);

            return ['date' => $day->toDateString(), 'label' => $day->format('j M'), 'paise' => (int) ($totals[$day->toDateString()] ?? 0)];
        })->all();
    }

    /** @return list<array{category: string, paise: int, count: int}> biggest first */
    public static function byCategory(int $days): array
    {
        return SalesExpense::selectRaw('category, SUM(amount_paise) as total, COUNT(*) as n')
            ->whereBetween('expense_date', [static::from($days)->toDateString(), static::today()->toDateString()])
            ->groupBy('category')->orderByDesc('total')->get()
            ->map(fn ($row) => ['category' => $row->category, 'paise' => (int) $row->total, 'count' => (int) $row->n])->all();
    }

    /**
     * Where the money went (local work or outstation) and whether there was a bill: whole paise for each answer. An expense from an older
     * phone, which did not ask, is counted under "unknown".
     *
     * @return array{work_area: array<string, int>, bill: array<string, int>}
     */
    public static function expenseSplit(int $days): array
    {
        $out = ['work_area' => ['local' => 0, 'outstation' => 0, 'unknown' => 0], 'bill' => ['with_bill' => 0, 'without_bill' => 0, 'unknown' => 0]];

        $rows = SalesExpense::selectRaw('work_area, bill_type, SUM(amount_paise) as total')
            ->whereBetween('expense_date', [static::from($days)->toDateString(), static::today()->toDateString()])
            ->groupBy('work_area', 'bill_type')->get();

        foreach ($rows as $row) {
            $out['work_area'][$row->work_area ?? 'unknown'] += (int) $row->total;
            $out['bill'][$row->bill_type ?? 'unknown'] += (int) $row->total;
        }

        return $out;
    }

    /** @return Collection<int, array> every active rep with this window's figures, biggest spender first */
    public static function reps(int $days): Collection
    {
        $range = [static::from($days)->toDateString(), static::today()->toDateString()];
        $spend = SalesExpense::selectRaw('user_id, SUM(amount_paise) as total, COUNT(*) as n')->whereBetween('expense_date', $range)->groupBy('user_id')->get()->keyBy('user_id');
        $pending = SalesExpense::selectRaw('user_id, COUNT(*) as n')->where('status', SalesExpense::PENDING)->groupBy('user_id')->pluck('n', 'user_id');
        $meetings = SalesMeeting::selectRaw('user_id, COUNT(*) as n')->where('status', 'done')->where('meeting_at', '>=', static::fromUtc($days))->groupBy('user_id')->pluck('n', 'user_id');
        $enquiries = SalesEnquiry::selectRaw('user_id, COUNT(*) as n, SUM(CASE WHEN next_followup_at < ? THEN 1 ELSE 0 END) as late', [now()])->whereIn('status', SalesEnquiry::OPEN)->groupBy('user_id')->get()->keyBy('user_id');
        $calls = SalesCall::selectRaw('user_id, COUNT(*) as n, SUM(duration_seconds) as secs')->where('started_at', '>=', static::fromUtc($days))->groupBy('user_id')->get()->keyBy('user_id');
        $won = SalesEnquiry::selectRaw('user_id, COUNT(*) as n')->where('status', 'won')->where('closed_at', '>=', static::fromUtc($days))->groupBy('user_id')->pluck('n', 'user_id');
        $reported = SalesDailyReport::whereDate('report_date', static::today()->toDateString())->whereNotNull('submitted_at')->pluck('user_id')->flip();

        return SalesProfile::active()->with('user')->get()->map(fn (SalesProfile $p) => [
            'id' => $p->id,
            'user_id' => $p->user_id,
            'won' => (int) ($won[$p->user_id] ?? 0),
            'report_today' => $reported->has($p->user_id),
            'name' => $p->user->name,
            'code' => $p->employee_code,
            'spend_paise' => (int) ($spend[$p->user_id]->total ?? 0),
            'expenses' => (int) ($spend[$p->user_id]->n ?? 0),
            'pending' => (int) ($pending[$p->user_id] ?? 0),
            'meetings' => (int) ($meetings[$p->user_id] ?? 0),
            'enquiries_open' => (int) ($enquiries[$p->user_id]->n ?? 0),
            'enquiries_late' => (int) ($enquiries[$p->user_id]->late ?? 0),
            'calls' => (int) ($calls[$p->user_id]->n ?? 0),
            'talk_seconds' => (int) ($calls[$p->user_id]->secs ?? 0),
            'last_sync_at' => $p->last_sync_at,
            'stale' => static::isStale($p),
        ])->sortByDesc('spend_paise')->values();
    }

    /**
     * Everything the "selected rep" card shows: the rep's own row of figures, the phone, today's report and the three open enquiries
     * that need the most attention (overdue first). Null when the profile is not an active rep.
     *
     * @return ?array{rep: array, phone: ?string, app_version: ?string, device_model: ?string, last_sync_at: mixed, stale: bool, report: array{submitted: bool, notes: ?string}, enquiries: Collection<int, SalesEnquiry>}
     */
    public static function repDetail(int $profileId, int $days): ?array
    {
        $row = static::reps($days)->firstWhere('id', $profileId);
        $profile = SalesProfile::active()->find($profileId);

        if (! $row || ! $profile) {
            return null;
        }

        $report = SalesDailyReport::where('user_id', $profile->user_id)->whereDate('report_date', static::today()->toDateString())->first();

        return [
            'rep' => $row,
            'phone' => $profile->phone,
            'app_version' => $profile->app_version,
            'device_model' => $profile->device_model,
            'last_sync_at' => $profile->last_sync_at,
            'stale' => static::isStale($profile),
            'report' => ['submitted' => (bool) $report?->submitted_at, 'notes' => $report?->submitted_at ? $report->notes : null],
            // overdue first (the oldest follow-up), then the ones with a date, then those without
            'enquiries' => SalesEnquiry::where('user_id', $profile->user_id)->whereIn('status', SalesEnquiry::OPEN)
                ->orderByRaw('next_followup_at IS NULL')->orderBy('next_followup_at')->orderByDesc('opened_at')->limit(3)->get(),
        ];
    }

    /** The most recent time any active rep's phone synced (null when none ever did). */
    public static function latestSync(): ?CarbonImmutable
    {
        $latest = SalesProfile::active()->max('last_sync_at');

        return $latest ? CarbonImmutable::parse($latest, 'UTC') : null;
    }

    /** Active reps whose phone has not synced since midnight today (India time). */
    public static function staleReps(): Collection
    {
        return SalesProfile::active()->with('user')->get()->filter(fn (SalesProfile $p) => static::isStale($p))->values();
    }

    private static function isStale(SalesProfile $p, ?CarbonImmutable $now = null): bool
    {
        $midnight = $now ? $now->setTimezone(static::tz())->startOfDay() : static::today();

        return ! $p->last_sync_at || $p->last_sync_at->lt($midnight->utc());
    }

    /** Meetings with a follow-up that is overdue or due in the next 7 days. */
    private static function followupQuery()
    {
        return SalesMeeting::whereNotNull('next_followup_at')->where('next_followup_at', '<=', now()->addDays(7));
    }

    public static function followups(int $limit = 8): Collection
    {
        return static::followupQuery()->with('user')->orderBy('next_followup_at')->limit($limit)->get();
    }

    /** The oldest expenses still waiting for a decision. */
    public static function pendingExpenses(int $limit = 8): Collection
    {
        return SalesExpense::where('status', SalesExpense::PENDING)->with('user')->orderBy('expense_date')->orderBy('id')->limit($limit)->get();
    }

    // =====================================================================================================================
    // The "Today first" page: where everybody is today, what needs a word, and every figure against the period before it.
    // =====================================================================================================================

    /** A meeting is drawn this long when the phone does not know when it ended. */
    public const MEETING_MINUTES = 60;

    /** The hours the team timeline shows; it widens (up to AXIS_LIMIT) when a meeting of the day falls outside. */
    public const AXIS_DEFAULT = [8, 19];

    public const AXIS_LIMIT = [6, 22];

    /** A time stored in UTC (a string or a date object) as the business timezone's moment. */
    private static function local(mixed $time): CarbonImmutable
    {
        $moment = $time instanceof \DateTimeInterface ? CarbonImmutable::instance($time) : CarbonImmutable::parse((string) $time, 'UTC');

        return $moment->setTimezone(static::tz());
    }

    private static function now(?CarbonImmutable $now = null): CarbonImmutable
    {
        return ($now ?? CarbonImmutable::now(static::tz()))->setTimezone(static::tz());
    }

    /** "yesterday 6:12 pm", "today 9:05 am", "Mon 26 Oct, 6:12 pm" */
    public static function whenText(?CarbonImmutable $at, ?CarbonImmutable $now = null): string
    {
        if (! $at) {
            return 'never';
        }

        $now = static::now($now);
        $at = $at->setTimezone(static::tz());
        $day = $at->isSameDay($now) ? 'today' : ($at->isSameDay($now->subDay()) ? 'yesterday' : $at->format('D j M,'));

        return $day . ' ' . $at->format('g:i a');
    }

    /**
     * The team's day: one entry per active rep with the meetings of the business day drawn as blocks on an hour axis, the routes that
     * join them, and a status line; plus the five tiles above the timeline. Positions are percentages of the axis, so the page needs
     * no script to lay them out. A route only ever lists its own rep's meetings.
     *
     * @return array{day: string, axis: array, tiles: array, reps: list<array>}
     */
    public static function todayField(?CarbonImmutable $now = null): array
    {
        $now = static::now($now);
        $start = $now->startOfDay();
        $end = $start->addDay();
        $nowMinute = $now->hour * 60 + $now->minute;

        $profiles = SalesProfile::active()->with('user')->get();
        $meetings = $profiles->isEmpty() ? collect() : SalesMeeting::whereIn('user_id', $profiles->pluck('user_id'))
            ->where('meeting_at', '>=', $start->utc())->where('meeting_at', '<', $end->utc())
            ->orderBy('meeting_at')->orderBy('id')->get();
        $routeIds = $meetings->pluck('route_uuid')->filter()->unique()->values();
        $routes = $routeIds->isEmpty() ? collect() : SalesRoute::whereIn('uuid', $routeIds)->get()->keyBy('uuid');
        $reported = SalesDailyReport::whereDate('report_date', $start->toDateString())->whereNotNull('submitted_at')->pluck('user_id')->flip();
        $callRows = SalesCall::where('started_at', '>=', $start->utc())->where('started_at', '<', $end->utc())
            ->selectRaw('direction, COUNT(*) as n')->groupBy('direction')->pluck('n', 'direction');

        // every meeting with its place on the day (minutes since midnight, business time)
        $items = $meetings->map(function (SalesMeeting $m) {
            $at = $m->plannedOrActualAt();
            $local = static::local($at);
            $seconds = $m->ended_at ? $m->ended_at->getTimestamp() - $at->getTimestamp() : 0;
            $minutes = $seconds > 0 ? max(15, min(480, intdiv($seconds, 60))) : self::MEETING_MINUTES;
            $startMinute = $local->hour * 60 + $local->minute;

            return ['m' => $m, 'at' => $local, 'start' => $startMinute, 'end' => $startMinute + $minutes];
        });

        // the axis: 8 AM to 7 PM, wider when a meeting falls outside, never beyond 6 AM .. 10 PM
        $low = self::AXIS_DEFAULT[0] * 60;
        $high = self::AXIS_DEFAULT[1] * 60;
        if ($items->isNotEmpty()) {
            $low = min($low, intdiv($items->min('start'), 60) * 60);
            $high = max($high, (int) (ceil($items->max('end') / 60) * 60));
        }
        $low = max(self::AXIS_LIMIT[0] * 60, $low);
        $high = min(self::AXIS_LIMIT[1] * 60, $high);
        $span = max(60, $high - $low);
        $pct = fn (int $minute) => round(100 * (max($low, min($high, $minute)) - $low) / $span, 2);

        $step = $span > 12 * 60 ? 2 : 1;
        $hours = [];
        for ($h = intdiv($low, 60); $h <= intdiv($high, 60); $h += $step) {
            $hours[] = ['label' => $start->setTime($h, 0)->format('g A'), 'pct' => $pct($h * 60)];
        }

        $byUser = $items->groupBy(fn (array $i) => $i['m']->user_id);
        $reps = [];
        $routeStats = [];   // route uuid => [finished stops, open stops]

        foreach ($profiles as $profile) {
            $mine = $byUser->get($profile->user_id, collect())->sortBy('start')->values();
            $nextTaken = false;
            $blocks = [];

            foreach ($mine as $i) {
                /** @var SalesMeeting $m */
                $m = $i['m'];
                $open = $m->isOpen();
                $late = $open && $i['at']->lt($now->subHour());
                $state = $m->status === 'done' ? 'done' : ($m->status === 'missed' ? 'missed' : 'planned');

                if ($open && ! $nextTaken && $i['end'] > $nowMinute) {
                    $state = 'next';
                    $nextTaken = true;
                }

                $width = max(1.8, round(100 * (max($low, min($high, $i['end'])) - max($low, min($high, $i['start']))) / $span, 2));
                $left = min($pct($i['start']), round(100 - $width, 2));   // a meeting past the end of the axis is drawn at its edge
                $routeOk = $m->route_uuid && $routes->has($m->route_uuid) && (int) $routes->get($m->route_uuid)->user_id === (int) $profile->user_id;
                $blocks[] = [
                    'uuid' => $m->uuid,
                    'client' => $m->client_name,
                    'time' => $i['at']->format('g:i a'),
                    'left' => $left,
                    'width' => $width,
                    'state' => $state,
                    'late' => $late,
                    'open' => $open,
                    'route_uuid' => $routeOk ? $m->route_uuid : null,
                    'word' => match (true) {
                        $state === 'done' => 'done',
                        $state === 'missed' => 'missed',
                        $late => 'not logged',
                        $state === 'next' => 'next',
                        default => 'planned',
                    },
                ];
            }

            // the routes of the day: a dashed line through the stops, the title above the first one
            $lines = [];
            foreach (collect($blocks)->whereNotNull('route_uuid')->groupBy('route_uuid') as $uuid => $stops) {
                $route = $routes->get($uuid);
                $first = $stops->sortBy('left')->first();
                $last = $stops->sortBy('left')->last();
                $lines[] = [
                    'uuid' => $uuid,
                    'title' => $route->title,
                    'label_left' => $first['left'],
                    'from' => round($first['left'] + $first['width'] / 2, 2),
                    'to' => round($last['left'] + $last['width'] / 2, 2),
                    'stops' => $stops->count(),
                ];
                $routeStats[$uuid] = [
                    'finished' => $stops->where('state', 'done')->count(),
                    'open' => $stops->where('open', true)->count(),
                ];
            }

            $synced = ! static::isStale($profile, $now);
            $hasOpen = collect($blocks)->contains('open', true);
            $nextBlock = collect($blocks)->firstWhere('state', 'next') ?? collect($blocks)->firstWhere('open', true);
            $routeTitle = $nextBlock && $nextBlock['route_uuid'] ? ($routes->get($nextBlock['route_uuid'])?->title) : ($lines[0]['title'] ?? null);

            if (! $synced) {
                $state = 'stale';
                $line = $profile->last_sync_at ? 'Not synced since ' . static::whenText(static::local($profile->last_sync_at), $now) : 'Never synced';
            } elseif ($hasOpen) {
                $state = 'field';
                $line = 'In field' . ($routeTitle ? ' · ' . $routeTitle : '');
            } else {
                $state = 'office';
                $line = 'Office';
            }

            $reps[] = [
                'profile_id' => $profile->id,
                'user_id' => $profile->user_id,
                'name' => $profile->user->name,
                'code' => $profile->employee_code,
                'state' => $state,
                'status_line' => $line,
                'report_in' => $reported->has($profile->user_id),
                'no_plan' => $mine->isEmpty(),
                'blocks' => $blocks,
                'lines' => $lines,
            ];
        }

        $count = fn (string $status) => $meetings->where('status', $status)->count();
        $open = $meetings->filter(fn (SalesMeeting $m) => $m->isOpen())->count();
        $stale = collect($reps)->where('state', 'stale');
        $missingReport = collect($reps)->where('report_in', false);

        return [
            'day' => $start->toDateString(),
            'axis' => ['low' => $low, 'high' => $high, 'hours' => $hours, 'now_pct' => $nowMinute >= $low && $nowMinute <= $high ? $pct($nowMinute) : null],
            'tiles' => [
                'meetings' => ['total' => $meetings->count(), 'done' => $count('done'), 'to_go' => $open, 'missed' => $count('missed')],
                'routes' => [
                    'total' => count($routeStats),
                    'on_the_way' => collect($routeStats)->filter(fn ($r) => $r['finished'] > 0 && $r['open'] > 0)->count(),
                ],
                'calls' => ['total' => (int) $callRows->sum(), 'out' => (int) ($callRows['outgoing'] ?? 0), 'in' => (int) ($callRows['incoming'] ?? 0), 'missed' => (int) ($callRows['missed'] ?? 0)],
                'reports' => ['in' => count($reps) - $missingReport->count(), 'total' => count($reps), 'missing' => $missingReport->pluck('name')->values()->all()],
                'synced' => ['in' => count($reps) - $stale->count(), 'total' => count($reps), 'missing' => $stale->pluck('name')->values()->all()],
            ],
            'reps' => $reps,
        ];
    }

    /**
     * "Needs a word": things a manager should talk to somebody about, in this order, at most `$limit`:
     * a client booked by two reps on the same day, a visit that was missed and not moved, a rep with no plan, a phone that has not synced,
     * and (after 6 pm) the reports still to come.
     *
     * @return list<array{key: string, icon: string, tone: string, title: string, text: string}>
     */
    public static function flags(?CarbonImmutable $now = null, int $limit = 6): array
    {
        $now = static::now($now);
        $start = $now->startOfDay();
        $end = $start->addDay();
        $yesterday = $start->subDay();

        $profiles = SalesProfile::active()->with('user')->get();
        $names = $profiles->mapWithKeys(fn (SalesProfile $p) => [$p->user_id => $p->user->name]);
        $userIds = $profiles->pluck('user_id');

        $recent = $profiles->isEmpty() ? collect() : SalesMeeting::whereIn('user_id', $userIds)
            ->where('meeting_at', '>=', $yesterday->utc())->where('meeting_at', '<', $end->utc())
            ->orderBy('meeting_at')->orderBy('id')->get();
        $dayOf = fn (SalesMeeting $m) => static::local($m->plannedOrActualAt());
        $today = $recent->filter(fn (SalesMeeting $m) => $dayOf($m)->gte($start));

        $out = ['double' => [], 'missed' => [], 'noplan' => [], 'stale' => [], 'reports' => []];

        // 1. the same client booked by two different reps today (open or done; the same rep twice is not a clash)
        $booked = $today->filter(fn (SalesMeeting $m) => $m->status !== 'missed' && $m->client_uuid)->groupBy('client_uuid');
        foreach ($booked as $meetings) {
            if ($meetings->pluck('user_id')->unique()->count() < 2) {
                continue;
            }

            $parts = $meetings->sortBy(fn (SalesMeeting $m) => $m->plannedOrActualAt()->getTimestamp())
                ->map(fn (SalesMeeting $m) => ($names[$m->user_id] ?? 'A rep') . ' at ' . $dayOf($m)->format('g:i a'))->values();

            $out['double'][] = [
                'key' => 'double_booked', 'icon' => 'heroicon-o-user-group', 'tone' => 'alert',
                'title' => $meetings->first()->client_name . ' is booked twice today',
                'text' => $parts->slice(0, -1)->implode(', ') . ' and ' . $parts->last() . '.',
            ];
        }

        // 3. missed today or yesterday and nothing planned again for that client by that rep
        $missed = $recent->filter(fn (SalesMeeting $m) => $m->status === 'missed' && $dayOf($m)->gte($yesterday));
        if ($missed->isNotEmpty()) {
            $later = SalesMeeting::whereIn('status', SalesMeeting::OPEN)->whereIn('user_id', $missed->pluck('user_id')->unique())
                ->where('meeting_at', '>', $missed->min('meeting_at'))->get();

            foreach ($missed as $m) {
                $again = $later->contains(fn (SalesMeeting $o) => $o->user_id === $m->user_id
                    && $o->meeting_at->gt($m->meeting_at)
                    && ($m->client_uuid ? $o->client_uuid === $m->client_uuid : mb_strtolower($o->client_name) === mb_strtolower($m->client_name)));

                if (! $again) {
                    $out['missed'][] = [
                        'key' => 'missed', 'icon' => 'heroicon-o-x-circle', 'tone' => 'alert',
                        'title' => ($names[$m->user_id] ?? 'A rep') . ' missed ' . $m->client_name,
                        'text' => 'Missed ' . ($dayOf($m)->isSameDay($now) ? 'today' : 'yesterday') . ' at ' . $dayOf($m)->format('g:i a') . '; nothing is planned with them again.',
                    ];
                }
            }
        }

        // 2. no meeting today, after 10:00 on a Monday to Saturday
        if ($now->dayOfWeekIso <= 6 && $now->hour >= 10) {
            $planned = $today->pluck('user_id')->unique();
            $idle = $profiles->reject(fn (SalesProfile $p) => $planned->contains($p->user_id));
            $last = $idle->isEmpty() ? collect() : SalesMeeting::where('status', 'done')->whereIn('user_id', $idle->pluck('user_id'))
                ->groupBy('user_id')->selectRaw('user_id, MAX(meeting_at) as last_at')->pluck('last_at', 'user_id');

            foreach ($idle as $p) {
                $out['noplan'][] = [
                    'key' => 'no_plan', 'icon' => 'heroicon-o-calendar', 'tone' => 'warn',
                    'title' => $p->user->name . ' has no plan today',
                    'text' => 'No meetings planned. ' . (isset($last[$p->user_id]) ? 'Last logged visit ' . static::local($last[$p->user_id])->format('j M') . '.' : 'No visit logged yet.'),
                ];
            }
        }

        // 4. phones that have not synced today
        foreach ($profiles as $p) {
            if (static::isStale($p, $now)) {
                $out['stale'][] = [
                    'key' => 'not_synced', 'icon' => 'heroicon-o-signal-slash', 'tone' => 'warn',
                    'title' => $p->user->name . "'s phone has not synced",
                    'text' => $p->last_sync_at ? 'Last sync ' . static::whenText(static::local($p->last_sync_at), $now) . '.' : 'It has never synced.',
                ];
            }
        }

        // 5. after 6 pm: the reports still to come
        if ($now->hour >= 18) {
            $sent = SalesDailyReport::whereDate('report_date', $start->toDateString())->whereNotNull('submitted_at')->pluck('user_id');
            $waiting = $profiles->reject(fn (SalesProfile $p) => $sent->contains($p->user_id))->map(fn (SalesProfile $p) => $p->user->name)->values();

            if ($waiting->isNotEmpty()) {
                $out['reports'][] = [
                    'key' => 'reports', 'icon' => 'heroicon-o-document-text', 'tone' => 'warn',
                    'title' => 'Reports still to come',
                    'text' => $waiting->take(4)->implode(', ') . ($waiting->count() > 4 ? ' +' . ($waiting->count() - 4) : '') . ' ' . ($waiting->count() === 1 ? 'has' : 'have') . ' not sent today\'s report.',
                ];
            }
        }

        return array_slice(array_merge($out['double'], $out['missed'], $out['noplan'], $out['stale'], $out['reports']), 0, $limit);
    }

    /**
     * One figure against the same number of days just before the window. `$delta` is a whole percent, "new" when there was nothing before
     * (no arrow) and null when there is nothing to compare (both zero, or no value). `$tone`: whether the change is good news.
     *
     * @return array{value: ?int, prev: ?int, delta: int|string|null, dir: string, tone: string}
     */
    public static function compare(?int $now, ?int $prev, bool $upIsGood = true): array
    {
        $out = ['value' => $now, 'prev' => $prev, 'delta' => null, 'dir' => 'flat', 'tone' => 'none'];

        if ($now === null) {
            return $out;
        }
        if (! $prev) {
            return $now > 0 ? ['delta' => 'new'] + $out : $out;
        }

        $delta = (int) round(100 * ($now - $prev) / $prev);
        $dir = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'flat');

        return [
            'delta' => $delta,
            'dir' => $dir,
            'tone' => $dir === 'flat' ? 'none' : (($dir === 'up') === $upIsGood ? 'ok' : 'alert'),
        ] + $out;
    }

    /**
     * The six headline figures with the change against the previous period (the same number of days right before the window).
     * Spent is left out when `$withMoney` is false (a manager who may not see expenses).
     *
     * @return array<string, array>
     */
    public static function summaryCompared(int $days, bool $withMoney = true): array
    {
        $from = static::from($days);
        $until = static::today()->addDay();
        $prevFrom = $from->subDays($days);

        $between = fn ($query, string $column, CarbonImmutable $a, CarbonImmutable $b) => $query->where($column, '>=', $a->utc())->where($column, '<', $b->utc());
        $pair = fn (callable $count) => [$count($from, $until), $count($prevFrom, $from)];

        $out = [];

        if ($withMoney) {
            [$now, $prev] = $pair(fn ($a, $b) => (int) SalesExpense::whereBetween('expense_date', [$a->toDateString(), $b->subDay()->toDateString()])->sum('amount_paise'));
            $out['spent'] = static::compare($now, $prev, upIsGood: false);
        }

        [$now, $prev] = $pair(fn ($a, $b) => (int) $between(SalesMeeting::where('status', 'done'), 'meeting_at', $a, $b)->count());
        $out['meetings'] = static::compare($now, $prev);

        [$now, $prev] = $pair(fn ($a, $b) => (int) $between(SalesEnquiry::query(), 'opened_at', $a, $b)->count());
        $out['enquiries'] = static::compare($now, $prev);

        $closed = fn ($a, $b) => $between(SalesEnquiry::whereIn('status', ['won', 'lost']), 'closed_at', $a, $b)->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
        $now = $closed($from, $until);
        $prev = $closed($prevFrom, $from);
        $rate = fn ($c) => ($c['won'] ?? 0) + ($c['lost'] ?? 0) > 0 ? (int) round(100 * ($c['won'] ?? 0) / (($c['won'] ?? 0) + ($c['lost'] ?? 0))) : null;

        $out['won'] = static::compare((int) ($now['won'] ?? 0), (int) ($prev['won'] ?? 0));
        $out['win_rate'] = static::compare($rate($now), $rate($prev)) + ['won' => (int) ($now['won'] ?? 0), 'decided' => (int) (($now['won'] ?? 0) + ($now['lost'] ?? 0))];

        [$now, $prev] = $pair(fn ($a, $b) => (int) $between(SalesCall::query(), 'started_at', $a, $b)->count());
        $out['calls'] = static::compare($now, $prev);

        return $out;
    }

    /** Won / (won + lost) among the enquiries decided in the window; the rate is null when nothing was decided. */
    public static function winRate(int $days): array
    {
        $counts = SalesEnquiry::whereIn('status', ['won', 'lost'])->where('closed_at', '>=', static::fromUtc($days))
            ->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');
        $won = (int) ($counts['won'] ?? 0);
        $lost = (int) ($counts['lost'] ?? 0);

        return ['won' => $won, 'lost' => $lost, 'decided' => $won + $lost, 'rate' => $won + $lost > 0 ? (int) round(100 * $won / ($won + $lost)) : null];
    }

    /**
     * The last `$days` days (business days, today last) of each figure the sparklines draw. One query per figure; the days are counted
     * in PHP in business time, so the day edges are right whatever the database does with dates.
     *
     * @return array{dates: list<string>, spent: list<int>, meetings: list<int>, enquiries: list<int>, won: list<int>, win_rate: list<int>, calls: list<int>}
     */
    public static function series(int $days = 14): array
    {
        $to = static::today();
        $from = $to->subDays($days - 1);
        $keys = collect(range(0, $days - 1))->map(fn (int $i) => $from->addDays($i)->toDateString());
        $blank = $keys->mapWithKeys(fn ($k) => [$k => 0])->all();

        $perDay = function ($query, string $column) use ($from, $to, $blank) {
            $counts = $blank;
            foreach ($query->where($column, '>=', $from->utc())->where($column, '<', $to->addDay()->utc())->pluck($column) as $time) {
                $day = static::local($time)->toDateString();
                if (isset($counts[$day])) {
                    $counts[$day]++;
                }
            }

            return array_values($counts);
        };

        $spend = SalesExpense::selectRaw('expense_date as d, SUM(amount_paise) as total')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])->groupBy('expense_date')->pluck('total', 'd')
            ->mapWithKeys(fn ($total, $d) => [CarbonImmutable::parse($d)->toDateString() => (int) $total]);

        // win rate of the 7 days ending on each day: it needs the six days before the first one as well
        $wonByDay = $lostByDay = array_fill_keys(collect(range(-6, $days - 1))->map(fn (int $i) => $from->addDays($i)->toDateString())->all(), 0);
        $closed = SalesEnquiry::whereIn('status', ['won', 'lost'])->where('closed_at', '>=', $from->subDays(6)->utc())->where('closed_at', '<', $to->addDay()->utc())->get(['status', 'closed_at']);
        foreach ($closed as $e) {
            $day = static::local($e->closed_at)->toDateString();
            if ($e->status === 'won' && isset($wonByDay[$day])) {
                $wonByDay[$day]++;
            } elseif ($e->status === 'lost' && isset($lostByDay[$day])) {
                $lostByDay[$day]++;
            }
        }
        $rolling = $keys->map(function (string $day) use ($wonByDay, $lostByDay) {
            $won = $lost = 0;
            for ($i = 0; $i < 7; $i++) {
                $d = CarbonImmutable::parse($day)->subDays($i)->toDateString();
                $won += $wonByDay[$d] ?? 0;
                $lost += $lostByDay[$d] ?? 0;
            }

            return $won + $lost > 0 ? (int) round(100 * $won / ($won + $lost)) : null;
        })->all();

        // a day with nothing decided in its 7 days has no rate: the line carries the last known value (and starts at the first one) instead of dipping to 0%
        $known = array_values(array_filter($rolling, fn ($r) => $r !== null));
        $last = $known[0] ?? 0;
        $rolling = array_map(function ($r) use (&$last) {
            $last = $r ?? $last;

            return $last;
        }, $rolling);

        return [
            'dates' => $keys->all(),
            'spent' => $keys->map(fn ($k) => (int) ($spend[$k] ?? 0))->all(),
            'meetings' => $perDay(SalesMeeting::where('status', 'done'), 'meeting_at'),
            'enquiries' => $perDay(SalesEnquiry::query(), 'opened_at'),
            'won' => array_values(array_map(fn ($k) => $wonByDay[$k] ?? 0, $keys->all())),
            'win_rate' => array_values($rolling),
            'calls' => $perDay(SalesCall::query(), 'started_at'),
        ];
    }

    /**
     * Where the enquiries opened in the window came from ("came through"). A source nobody told is "Not told", shown only when there
     * is one.
     *
     * @return array{total: int, items: list<array{key: string, label: string, count: int, percent: int}>}
     */
    public static function enquirySources(int $days): array
    {
        $rows = SalesEnquiry::where('opened_at', '>=', static::fromUtc($days))->selectRaw('source, COUNT(*) as n')->groupBy('source')->pluck('n', 'source');
        $items = [];

        foreach (SalesEnquiry::SOURCES as $key => $label) {
            $items[] = ['key' => $key, 'label' => $label, 'count' => (int) ($rows[$key] ?? 0)];
        }
        $unknown = (int) ($rows[''] ?? 0);
        if ($unknown > 0) {
            $items[] = ['key' => 'unknown', 'label' => 'Not told', 'count' => $unknown];
        }

        $total = array_sum(array_column($items, 'count'));

        return ['total' => $total, 'items' => array_map(fn ($i) => $i + ['percent' => $total ? (int) round(100 * $i['count'] / $total) : 0], $items)];
    }

    /**
     * How the visits done in the window ended (the rep's answer after the visit). "No result written" is shown only when there is one.
     *
     * @return array{total: int, max: int, items: list<array{key: string, label: string, count: int}>}
     */
    public static function meetingResults(int $days): array
    {
        $rows = SalesMeeting::where('status', 'done')->where('meeting_at', '>=', static::fromUtc($days))
            ->selectRaw('result, COUNT(*) as n')->groupBy('result')->pluck('n', 'result');
        $items = [];

        foreach (SalesMeeting::RESULTS as $key => $label) {
            $items[] = ['key' => $key, 'label' => $label, 'count' => (int) ($rows[$key] ?? 0)];
        }
        $none = (int) ($rows[''] ?? 0);
        if ($none > 0) {
            $items[] = ['key' => 'none', 'label' => 'No result written', 'count' => $none];
        }

        return ['total' => array_sum(array_column($items, 'count')), 'max' => max(1, ...array_column($items, 'count')), 'items' => $items];
    }

    /** Planned meetings whose time passed more than an hour ago and nobody wrote down what happened, the oldest first. */
    public static function notLoggedMeetings(int $limit = 8): Collection
    {
        return SalesMeeting::whereIn('status', SalesMeeting::OPEN)->where('meeting_at', '<', now()->subHour())
            ->with('user')->orderBy('meeting_at')->orderBy('id')->limit($limit)->get();
    }

    // =====================================================================================================================
    // The Reps screens: the roster (where is everybody, who needs a word) and one rep's profile.
    // =====================================================================================================================

    /** Monday to Saturday are working days unless config('sales.working_days') says otherwise (ISO weekday numbers, 1 = Monday). */
    public static function isWorkingDay(CarbonImmutable $day): bool
    {
        return in_array($day->dayOfWeekIso, (array) config('sales.working_days', [1, 2, 3, 4, 5, 6]), true);
    }

    /** True when the app version a phone reports is older than the version the team should run (an unknown version is not flagged). */
    public static function isOutdatedApp(?string $version): bool
    {
        $min = (string) config('sales.min_app_version', '');

        return $min !== '' && filled($version) && version_compare((string) $version, $min, '<');
    }

    /**
     * The code a new rep gets by default: the highest number among the codes that look like AB-0412 plus one, with the same width.
     * Codes of another shape (REP001 ...) are ignored; with none that match it starts at AB-0001. The manager may overwrite it.
     */
    public static function nextEmployeeCode(): string
    {
        $highest = 0;
        $width = 4;

        foreach (SalesProfile::pluck('employee_code') as $code) {
            if (preg_match('/^AB-(\d+)$/i', (string) $code, $m)) {
                $highest = max($highest, (int) $m[1]);
                $width = max($width, strlen($m[1]));
            }
        }

        return 'AB-' . str_pad((string) ($highest + 1), $width, '0', STR_PAD_LEFT);
    }

    /**
     * One row per rep (active or switched off), everything the roster shows, in a fixed number of queries.
     *
     * State: `off` (switched off), `stale` (no sync since midnight India time), `field` (a planned visit is still open today),
     * otherwise `office`. `attention` lists why a manager should look (active reps only): phone not synced today, the daily report
     * missing after the report hour on a working day, more than one expense waiting, an enquiry whose follow-up is late.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function repRoster(?CarbonImmutable $now = null): Collection
    {
        $now = static::now($now);
        $start = $now->startOfDay();
        $monthStart = $now->startOfMonth();
        $profiles = SalesProfile::with('user')->get();

        if ($profiles->isEmpty()) {
            return collect();
        }

        $monthSpend = SalesExpense::selectRaw('user_id, SUM(amount_paise) as total')->whereBetween('expense_date', [$monthStart->toDateString(), $start->toDateString()])->groupBy('user_id')->pluck('total', 'user_id');
        $pending = SalesExpense::selectRaw('user_id, COUNT(*) as n')->where('status', SalesExpense::PENDING)->groupBy('user_id')->pluck('n', 'user_id');
        $doneInMonth = SalesMeeting::selectRaw('user_id, COUNT(*) as n')->where('status', 'done')->where('meeting_at', '>=', $monthStart->utc())->where('meeting_at', '<=', $now->utc())->groupBy('user_id')->pluck('n', 'user_id');
        $enquiries = SalesEnquiry::selectRaw('user_id, COUNT(*) as n, SUM(CASE WHEN next_followup_at < ? THEN 1 ELSE 0 END) as late', [$now->utc()])->whereIn('status', SalesEnquiry::OPEN)->groupBy('user_id')->get()->keyBy('user_id');
        $clients = SalesClient::selectRaw('assigned_to, COUNT(*) as n')->whereNotNull('assigned_to')->groupBy('assigned_to')->pluck('n', 'assigned_to');
        $reported = SalesDailyReport::whereDate('report_date', $start->toDateString())->whereNotNull('submitted_at')->pluck('user_id')->flip();
        $today = SalesMeeting::where('meeting_at', '>=', $start->utc())->where('meeting_at', '<', $start->addDay()->utc())->orderBy('meeting_at')->orderBy('id')->get(['user_id', 'status', 'route_uuid'])->groupBy('user_id');
        $routeIds = $today->flatten(1)->filter(fn (SalesMeeting $m) => $m->isOpen())->pluck('route_uuid')->filter()->unique()->values();
        $routes = $routeIds->isEmpty() ? collect() : SalesRoute::whereIn('uuid', $routeIds)->pluck('title', 'uuid');

        $reportDue = $now->hour >= (int) config('sales.report_hour', 18) && static::isWorkingDay($now);

        return $profiles->map(function (SalesProfile $p) use ($now, $monthSpend, $pending, $doneInMonth, $enquiries, $clients, $reported, $today, $routes, $reportDue) {
            $uid = $p->user_id;
            $stale = static::isStale($p, $now);
            $open = ($today->get($uid) ?? collect())->first(fn (SalesMeeting $m) => $m->isOpen());
            $late = (int) ($enquiries[$uid]->late ?? 0);
            $waiting = (int) ($pending[$uid] ?? 0);
            $reportMissing = $reportDue && ! $reported->has($uid);

            $reasons = [];
            if ($p->is_active) {
                $stale && $reasons['sync'] = 'Not synced today';
                $reportMissing && $reasons['report'] = 'Report not sent';
                $waiting > 1 && $reasons['pending'] = $waiting . ' pending';
                $late > 0 && $reasons['late'] = $late . ' late';
            }

            if (! $p->is_active) {
                $state = 'off';
                $line = 'Switched off ' . static::local($p->updated_at)->format('j M');
            } elseif ($stale) {
                $state = 'stale';
                $line = 'Not synced today';
            } elseif ($open) {
                $state = 'field';
                $title = $open->route_uuid ? ($routes[$open->route_uuid] ?? null) : null;
                $line = 'In field' . ($title ? ' · ' . $title : '');
            } else {
                $state = 'office';
                $line = 'Office' . ($reportMissing ? ' · report not sent' : '');
            }

            return [
                'id' => $p->id,
                'user_id' => $uid,
                'name' => $p->user->name,
                'code' => $p->employee_code,
                'phone' => $p->phone,
                'sort_order' => $p->sort_order,
                'email' => RepAccount::realEmail($p->user->email),
                'active' => (bool) $p->is_active,
                'state' => $state,
                'status_line' => $line,
                'last_sync_at' => $p->last_sync_at,
                'device_model' => $p->device_model,
                'app_version' => $p->app_version,
                'outdated' => static::isOutdatedApp($p->app_version),
                'meetings_month' => (int) ($doneInMonth[$uid] ?? 0),
                'enquiries_open' => (int) ($enquiries[$uid]->n ?? 0),
                'enquiries_late' => $late,
                'clients' => (int) ($clients[$uid] ?? 0),
                'month_spend_paise' => (int) ($monthSpend[$uid] ?? 0),
                'pending' => $waiting,
                'attention' => $reasons,
            ];
        // the order a manager set by dragging; reps not placed yet come after, by employee code
        })->sortBy(fn (array $r) => [$r['sort_order'] ?? PHP_INT_MAX, mb_strtolower($r['code'])])->values();
    }

    /** @return array<int, array<string, string>> user id => the reasons (keys sync, report, pending, late), for active reps that need a word (see repRoster) */
    public static function repsNeedingAttention(?CarbonImmutable $now = null): array
    {
        return static::repRoster($now)->filter(fn (array $r) => $r['attention'] !== [])->mapWithKeys(fn (array $r) => [$r['user_id'] => $r['attention']])->all();
    }

    /**
     * One rep's profile: the four figures of the last `$days` days against the days right before, today's visits, the 14-day chart,
     * the daily-report streak, the three most late enquiries, the expenses waiting and the phone. Null when there is no such rep.
     *
     * @return ?array<string, mixed>
     */
    public static function repProfile(int $userId, int $days = 30, ?CarbonImmutable $now = null): ?array
    {
        $profile = SalesProfile::with('user')->where('user_id', $userId)->first();

        if (! $profile) {
            return null;
        }

        $now = static::now($now);
        $start = $now->startOfDay();
        $from = $start->subDays($days - 1);
        $prevFrom = $from->subDays($days);
        $until = $start->addDay();
        $row = static::repRoster($now)->firstWhere('user_id', $userId);

        $spend = fn ($a, $b) => (int) SalesExpense::where('user_id', $userId)->whereBetween('expense_date', [$a->toDateString(), $b->toDateString()])->sum('amount_paise');
        $count = fn (string $model, string $column, $a, $b, array $where = []) => (int) $model::where('user_id', $userId)->where($where)->where($column, '>=', $a->utc())->where($column, '<', $b->utc())->count();

        $spentNow = $spend($from, $start);
        $spentPrev = $spend($prevFrom, $from->subDay());
        $meetingsNow = $count(SalesMeeting::class, 'meeting_at', $from, $until, ['status' => 'done']);
        $meetingsPrev = $count(SalesMeeting::class, 'meeting_at', $prevFrom, $from, ['status' => 'done']);
        $callsNow = $count(SalesCall::class, 'started_at', $from, $until);
        $callsPrev = $count(SalesCall::class, 'started_at', $prevFrom, $from);
        $wonNow = $count(SalesEnquiry::class, 'closed_at', $from, $until, ['status' => 'won']);
        $wonPrev = $count(SalesEnquiry::class, 'closed_at', $prevFrom, $from, ['status' => 'won']);
        $talk = (int) SalesCall::where('user_id', $userId)->where('started_at', '>=', $from->utc())->where('started_at', '<', $until->utc())->sum('duration_seconds');

        // "best in the team": the most visits done in the window, and at least one
        $bestCount = (int) SalesMeeting::where('status', 'done')->where('meeting_at', '>=', $from->utc())->where('meeting_at', '<', $until->utc())
            ->selectRaw('user_id, COUNT(*) as n')->groupBy('user_id')->orderByDesc('n')->value('n');

        // today's visits in time order, and the title of their route
        $meetings = SalesMeeting::where('user_id', $userId)->where('meeting_at', '>=', $start->utc())->where('meeting_at', '<', $until->utc())->orderBy('meeting_at')->orderBy('id')->get();
        $routes = SalesRoute::where('user_id', $userId)->whereIn('uuid', $meetings->pluck('route_uuid')->filter()->unique())->get();
        $nextTaken = false;
        $visits = $meetings->map(function (SalesMeeting $m) use ($now, &$nextTaken) {
            $at = static::local($m->plannedOrActualAt());
            $state = $m->status === 'done' ? 'done' : ($m->status === 'missed' ? 'missed' : 'planned');

            if ($m->isOpen() && ! $nextTaken && $at->gte($now->subHour())) {
                $state = 'next';
                $nextTaken = true;
            }

            return [
                'id' => $m->id,
                'time' => $at->format('g:i a'),
                'client' => $m->client_name,
                'purpose' => $m->purpose,
                'state' => $state,
                'late' => $m->isOpen() && $at->lt($now->subHour()),
                'in' => $state === 'next' ? static::inText($at, $now) : null,
            ];
        })->values()->all();

        // 14 business days, oldest first: visits done and calls per day (counted in PHP, in business time)
        $chartFrom = $start->subDays(13);
        $days14 = collect(range(0, 13))->map(fn (int $i) => $chartFrom->addDays($i));
        $perDay = function (string $model, string $column, array $where = []) use ($userId, $chartFrom, $until, $days14) {
            $by = [];
            foreach ($model::where('user_id', $userId)->where($where)->where($column, '>=', $chartFrom->utc())->where($column, '<', $until->utc())->pluck($column) as $time) {
                $key = static::local($time)->toDateString();
                $by[$key] = ($by[$key] ?? 0) + 1;
            }

            return $days14->map(fn (CarbonImmutable $d) => $by[$d->toDateString()] ?? 0)->all();
        };
        $chart = [
            'dates' => $days14->map(fn (CarbonImmutable $d) => $d->toDateString())->all(),
            'labels' => $days14->map(fn (CarbonImmutable $d) => $d->format('j M'))->all(),
            'meetings' => $perDay(SalesMeeting::class, 'meeting_at', ['status' => 'done']),
            'calls' => $perDay(SalesCall::class, 'started_at'),
        ];

        // the daily-report streak: sent / missed / not a working day / today not yet
        $sent = SalesDailyReport::where('user_id', $userId)->whereNotNull('submitted_at')->whereBetween('report_date', [$chartFrom->toDateString(), $start->toDateString()])
            ->pluck('report_date')->map(fn ($d) => CarbonImmutable::parse($d)->toDateString())->flip();
        $streak = $days14->map(function (CarbonImmutable $d) use ($sent, $start) {
            $working = static::isWorkingDay($d);
            $state = $sent->has($d->toDateString()) ? 'sent' : (! $working ? 'off' : ($d->equalTo($start) ? 'today' : 'missed'));

            return ['date' => $d->format('D j M'), 'state' => $state];
        })->all();
        $workedDays = collect($streak)->filter(fn (array $s) => in_array($s['state'], ['sent', 'missed'], true))->count();
        $sentDays = collect($streak)->where('state', 'sent')->count();

        $open = SalesEnquiry::where('user_id', $userId)->whereIn('status', SalesEnquiry::OPEN)->get();
        $top = $open->sortBy(fn (SalesEnquiry $e) => $e->next_followup_at ? $e->next_followup_at->getTimestamp() : PHP_INT_MAX)->take(3)->map(fn (SalesEnquiry $e) => [
            'id' => $e->id,
            'client' => $e->client_name,
            'summary' => $e->itemsSummary(2),
            'status' => $e->statusLabel(),
            'late' => (bool) ($e->next_followup_at && $e->next_followup_at->lt($now->utc())),
            // whole business days since the follow-up day; 0 = it fell due earlier today
            'late_days' => $e->next_followup_at && $e->next_followup_at->lt($now->utc()) ? max(0, (int) static::local($e->next_followup_at)->startOfDay()->diffInDays($start)) : 0,
        ])->values()->all();

        $pending = SalesExpense::where('user_id', $userId)->where('status', SalesExpense::PENDING)->orderBy('expense_date')->orderBy('id');

        return [
            'profile_id' => $profile->id,
            'user_id' => $userId,
            'name' => $profile->user->name,
            'code' => $profile->employee_code,
            'phone' => $profile->phone,
            'email' => $row['email'] ?? null,
            'active' => (bool) $profile->is_active,
            'state' => $row['state'],
            'status_line' => $row['status_line'],
            'attention' => $row['attention'],
            'last_sync_at' => $profile->last_sync_at,
            'device_model' => $profile->device_model,
            'app_version' => $profile->app_version,
            'outdated' => static::isOutdatedApp($profile->app_version),
            'tokens' => (int) $profile->user->tokens()->count(),
            'days' => $days,
            'month_label' => $now->format('F'),
            'month_spend_paise' => $row['month_spend_paise'],
            'kpis' => [
                'spent' => static::compare($spentNow, $spentPrev, false) + ['paise' => $spentNow],
                'meetings' => static::compare($meetingsNow, $meetingsPrev) + ['best' => $meetingsNow > 0 && $meetingsNow >= $bestCount],
                'calls' => static::compare($callsNow, $callsPrev) + ['talk_seconds' => $talk],
                'won' => static::compare($wonNow, $wonPrev) + ['open' => $row['enquiries_open'], 'late' => $row['enquiries_late']],
            ],
            'route_title' => $routes->first()?->title,
            'visits' => $visits,
            'chart' => $chart,
            'report' => ['streak' => $streak, 'sent' => $sentDays, 'working' => $workedDays, 'today_sent' => ($streak[13]['state'] ?? '') === 'sent', 'today_due' => ($streak[13]['state'] ?? '') === 'today'],
            'enquiries' => ['top' => $top, 'total' => $open->count()],
            'pending_count' => (int) (clone $pending)->count(),
            'pending' => (clone $pending)->limit(3)->get(),
            'clients_count' => $row['clients'],
        ];
    }

    /** "in 20 min", "in 2 h" for a visit that is still to come; "now" when it is within a minute (or has just begun). */
    private static function inText(CarbonImmutable $at, CarbonImmutable $now): string
    {
        $minutes = (int) $now->diffInMinutes($at, false);

        return $minutes <= 0 ? 'now' : ($minutes < 60 ? 'in ' . $minutes . ' min' : 'in ' . round($minutes / 60, 1) . ' h');
    }

    /**
     * What one rep did in the last `$days` days, newest first: visits, calls, enquiry updates and daily reports. `$types` limits the kinds
     * (`meeting`, `call`, `enquiry`, `report`; empty = all). Each row says what it is and the id to link to.
     *
     * @param  list<string>  $types
     * @return list<array{type: string, id: int, at: CarbonImmutable, title: string, text: string}>
     */
    public static function repActivity(int $userId, int $days = 30, array $types = [], int $limit = 120): array
    {
        $from = static::from($days)->utc();
        $want = fn (string $type) => $types === [] || in_array($type, $types, true);
        $rows = collect();

        if ($want('meeting')) {
            foreach (SalesMeeting::where('user_id', $userId)->where('meeting_at', '>=', $from)->orderByDesc('meeting_at')->limit($limit)->get() as $m) {
                $rows->push(['type' => 'meeting', 'id' => $m->id, 'at' => static::local($m->plannedOrActualAt()), 'title' => $m->client_name, 'text' => (SalesMeeting::STATUSES[$m->status] ?? ucfirst((string) $m->status)) . ($m->result ? ' · ' . (SalesMeeting::RESULTS[$m->result] ?? $m->result) : '')]);
            }
        }
        if ($want('call')) {
            foreach (SalesCall::where('user_id', $userId)->where('started_at', '>=', $from)->orderByDesc('started_at')->limit($limit)->get() as $c) {
                $rows->push(['type' => 'call', 'id' => $c->id, 'at' => static::local($c->started_at), 'title' => $c->client_name ?: $c->number, 'text' => ucfirst((string) $c->direction) . ($c->duration_seconds ? ' · ' . static::duration((int) $c->duration_seconds) : '')]);
            }
        }
        if ($want('enquiry')) {
            foreach (SalesEnquiry::where('user_id', $userId)->where('updated_at', '>=', $from)->orderByDesc('updated_at')->limit($limit)->get() as $e) {
                $rows->push(['type' => 'enquiry', 'id' => $e->id, 'at' => static::local($e->updated_at), 'title' => $e->client_name, 'text' => $e->statusLabel() . ' · ' . $e->itemsSummary(2)]);
            }
        }
        if ($want('report')) {
            foreach (SalesDailyReport::where('user_id', $userId)->whereNotNull('submitted_at')->where('report_date', '>=', static::from($days)->toDateString())->orderByDesc('report_date')->limit($limit)->get() as $r) {
                $rows->push(['type' => 'report', 'id' => $r->id, 'at' => static::local($r->submitted_at), 'title' => 'Daily report', 'text' => CarbonImmutable::parse($r->report_date)->format('D j M')]);
            }
        }

        return $rows->sortByDesc(fn (array $r) => $r['at']->getTimestamp())->take($limit)->values()->all();
    }

    /** The rep's clients (assigned to them) with the date of the last visit done and the enquiries still open. */
    public static function repClients(int $userId, int $limit = 100): Collection
    {
        $clients = SalesClient::where('assigned_to', $userId)->orderBy('name')->limit($limit)->get();
        $uuids = $clients->pluck('uuid');
        $visits = $uuids->isEmpty() ? collect() : SalesMeeting::whereIn('client_uuid', $uuids)->where('status', 'done')->selectRaw('client_uuid, MAX(meeting_at) as last')->groupBy('client_uuid')->pluck('last', 'client_uuid');
        $names = $clients->pluck('name')->all();
        $open = $names === [] ? collect() : SalesEnquiry::where('user_id', $userId)->whereIn('status', SalesEnquiry::OPEN)->whereIn('client_name', $names)->selectRaw('client_name, COUNT(*) as n')->groupBy('client_name')->pluck('n', 'client_name');

        return $clients->map(fn (SalesClient $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'city' => $c->city,
            'last_visit' => isset($visits[$c->uuid]) ? static::local($visits[$c->uuid]) : null,
            'open_enquiries' => (int) ($open[$c->name] ?? 0),
        ]);
    }

    /** The rep's expenses, waiting ones first, then the newest. */
    public static function repExpenses(int $userId, int $limit = 50): Collection
    {
        return SalesExpense::where('user_id', $userId)->orderByRaw("status = 'pending' DESC")->orderByDesc('expense_date')->orderByDesc('id')->limit($limit)->get();
    }

    // =====================================================================================================================
    // The Expenses review queue: what waits (in four groups), counts for the period, flags and the rep's context.
    // =====================================================================================================================

    public const EXPENSE_FILTERS = ['rep', 'category', 'area', 'bill', 'q'];

    /**
     * The period the Expenses screen shows, in business dates: a custom range (from / until) wins over a month ("2026-10"), and a month
     * that is not valid falls back to the current one.
     *
     * @return array{from: CarbonImmutable, to: CarbonImmutable, label: string, custom: bool}
     */
    public static function expensePeriod(?string $month = null, ?string $from = null, ?string $until = null): array
    {
        $valid = fn (?string $d) => $d && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && checkdate((int) substr($d, 5, 2), (int) substr($d, 8, 2), (int) substr($d, 0, 4));

        if ($valid($from) || $valid($until)) {
            $a = $valid($from) ? CarbonImmutable::parse($from, static::tz())->startOfDay() : CarbonImmutable::parse('2020-01-01', static::tz());
            $b = $valid($until) ? CarbonImmutable::parse($until, static::tz())->startOfDay() : static::today();

            if ($b->lt($a)) {
                [$a, $b] = [$b, $a];
            }

            return ['from' => $a, 'to' => $b, 'label' => $a->format('j M Y') . ' to ' . $b->format('j M Y'), 'custom' => true];
        }

        $start = $month && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? CarbonImmutable::parse($month . '-01', static::tz())->startOfMonth() : static::today()->startOfMonth();

        return ['from' => $start, 'to' => $start->endOfMonth()->startOfDay(), 'label' => $start->format('F Y'), 'custom' => false];
    }

    /** The twelve months a manager can pick, newest first: ['2026-10' => 'October 2026', ...]. */
    public static function expenseMonths(int $count = 12): array
    {
        $out = [];

        for ($i = 0; $i < $count; $i++) {
            $m = static::today()->startOfMonth()->subMonthsNoOverflow($i);
            $out[$m->format('Y-m')] = $m->format('F Y');
        }

        return $out;
    }

    /**
     * The expenses behind a list: the filters (rep, category, area, bill, search by rep name or note), a status and a window of expense dates.
     * One builder for the queue, the counts and the export.
     */
    public static function expenseQuery(array $filters = [], ?string $status = null, ?CarbonImmutable $from = null, ?CarbonImmutable $to = null): \Illuminate\Database\Eloquent\Builder
    {
        $q = SalesExpense::query();

        $status && $q->where('status', $status);
        $from && $q->where('expense_date', '>=', $from->toDateString());
        $to && $q->where('expense_date', '<=', $to->toDateString());

        if (filled($filters['rep'] ?? null)) {
            $q->where('user_id', (int) $filters['rep']);
        }
        if (filled($filters['category'] ?? null)) {
            $q->where('category', (string) $filters['category']);
        }
        if (filled($filters['area'] ?? null)) {
            $filters['area'] === 'none' ? $q->whereNull('work_area') : $q->where('work_area', (string) $filters['area']);
        }
        if (filled($filters['bill'] ?? null)) {
            $filters['bill'] === 'none' ? $q->whereNull('bill_type') : $q->where('bill_type', (string) $filters['bill']);
        }
        if (filled($needle = trim((string) ($filters['q'] ?? '')))) {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle) . '%';
            $q->where(fn ($w) => $w->where('note', 'like', $like)->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)));
        }

        return $q;
    }

    /**
     * The tab counts. Waiting is every expense still waiting, whatever its date; the other tabs count the expense dates of the period.
     *
     * @return array{waiting: int, approved: int, rejected: int, all: int}
     */
    public static function expenseCounts(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $rows = SalesExpense::where('expense_date', '>=', $from->toDateString())->where('expense_date', '<=', $to->toDateString())
            ->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status');

        return [
            'waiting' => (int) SalesExpense::where('status', SalesExpense::PENDING)->count(),
            'approved' => (int) ($rows[SalesExpense::APPROVED] ?? 0),
            'rejected' => (int) ($rows[SalesExpense::REJECTED] ?? 0),
            'all' => (int) $rows->sum(),
        ];
    }

    /** The header line: how many wait (and their total), and how many were reviewed in the period. */
    public static function expensePeriodSummary(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $waiting = SalesExpense::where('status', SalesExpense::PENDING)->selectRaw('COUNT(*) as n, COALESCE(SUM(amount_paise), 0) as total')->first();
        $reviewed = SalesExpense::whereIn('status', [SalesExpense::APPROVED, SalesExpense::REJECTED])
            ->where('reviewed_at', '>=', $from->startOfDay()->utc())->where('reviewed_at', '<', $to->addDay()->startOfDay()->utc())->count();

        return ['waiting' => (int) $waiting->n, 'waiting_paise' => (int) $waiting->total, 'reviewed' => (int) $reviewed];
    }

    /**
     * What waits, in the four groups a manager decides differently (oldest first inside a group):
     * `ready` (says it has a bill and the photo is here: safe to approve together), `nobill` (the rep said there is no bill),
     * `photo` (says it has a bill but the photo is still on the phone) and `older` (an older app did not say). Only `ready` has a group action.
     *
     * @return list<array{key: string, title: string, text: string, rows: Collection, count: int, paise: int, can_approve_all: bool}>
     */
    public static function waitingGroups(array $filters = []): array
    {
        $rows = static::expenseQuery($filters, SalesExpense::PENDING)->with(['user.salesProfile'])->orderBy('expense_date')->orderBy('id')->get();

        $kind = fn (SalesExpense $e) => match (true) {
            $e->bill_type === null => 'older',
            $e->bill_type === 'without_bill' => 'nobill',
            $e->hasBill() => 'ready',
            default => 'photo',
        };
        $by = $rows->groupBy($kind);
        $versions = ($by->get('older') ?? collect())->map(fn (SalesExpense $e) => $e->user?->salesProfile?->app_version)->filter()->unique()->sort()->values();

        $defs = [
            'ready' => ['Ready to approve', 'Says it has a bill and the photo is here. Safe to approve together.'],
            'nobill' => ['No bill', 'Rep said there is no bill. You decide: approve, or reject with a reason.'],
            'photo' => ['Photo still on the phone', 'It reaches the office after the next sync. Wait, or approve now.'],
            'older' => ['Older app', $versions->isEmpty() ? 'These phones did not say if there is a bill. You decide.' : 'App ' . $versions->implode(', ') . ' did not say if there is a bill. You decide.'],
        ];

        $groups = [];
        foreach ($defs as $key => [$title, $text]) {
            $list = $by->get($key, collect())->values();
            $groups[] = ['key' => $key, 'title' => $title, 'text' => $text, 'rows' => $list, 'count' => $list->count(), 'paise' => (int) $list->sum('amount_paise'), 'can_approve_all' => $key === 'ready'];
        }

        return $groups;
    }

    /** The flat list of the Approved / Rejected / All tabs: newest first, at most `$limit` rows. */
    public static function expenseList(?string $status, CarbonImmutable $from, CarbonImmutable $to, array $filters = [], int $limit = 200): Collection
    {
        return static::expenseQuery($filters, $status, $from, $to)->with(['user.salesProfile', 'reviewer'])->orderByDesc('expense_date')->orderByDesc('id')->limit($limit)->get();
    }

    /**
     * Up to two things worth a look before deciding on ONE waiting expense:
     * "Usually higher" (at least 3 times the rep's median for the same category over the 90 days before it, needing 5 earlier ones)
     * and "same trip" (other waiting expenses of the same rep, outstation, on the same or a neighbouring day).
     *
     * @return list<array{key: string, title: string, text: string}>
     */
    public static function expenseFlags(SalesExpense $e): array
    {
        $flags = [];
        $date = CarbonImmutable::parse($e->expense_date->toDateString(), static::tz());
        $name = $e->user?->name ?? 'The rep';

        $earlier = SalesExpense::where('user_id', $e->user_id)->where('category', $e->category)->whereKeyNot($e->getKey())
            ->where('expense_date', '<=', $date->toDateString())->where('expense_date', '>=', $date->subDays(90)->toDateString())->orderBy('amount_paise')->pluck('amount_paise')->all();

        if (count($earlier) >= 5) {
            $mid = intdiv(count($earlier), 2);
            $median = count($earlier) % 2 ? $earlier[$mid] : (int) round(($earlier[$mid - 1] + $earlier[$mid]) / 2);

            if ($median > 0 && $e->amount_paise >= 3 * $median) {
                $flags[] = ['key' => 'higher', 'title' => 'Usually higher', 'text' => Money::rupees($e->amount_paise, false) . ' is ' . round($e->amount_paise / $median, 1) . ' times ' . $name . '\'s usual ' . SalesExpense::categoryLabel($e->category) . ' expense (' . Money::rupees($median, false) . ', the middle of the last ' . count($earlier) . ' in 90 days).'];
            }
        }

        if ($e->work_area === 'outstation') {
            $same = SalesExpense::where('user_id', $e->user_id)->where('status', SalesExpense::PENDING)->where('work_area', 'outstation')->whereKeyNot($e->getKey())
                ->whereBetween('expense_date', [$date->subDay()->toDateString(), $date->addDay()->toDateString()])->count();

            if ($same > 0) {
                $flags[] = ['key' => 'trip', 'title' => 'Same trip', 'text' => ($same === 1 ? 'One more expense' : $same . ' more expenses') . ' from the same trip ' . ($same === 1 ? 'is' : 'are') . ' waiting.'];
            }
        }

        return array_slice($flags, 0, 2);
    }

    /**
     * One rep's expenses at a glance for the detail pane: spend in the period, approved, waiting (and its total) and rejected.
     *
     * @return array{spend_paise: int, approved: int, waiting: int, waiting_paise: int, rejected: int}
     */
    public static function repExpenseContext(int $userId, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $period = SalesExpense::where('user_id', $userId)->where('expense_date', '>=', $from->toDateString())->where('expense_date', '<=', $to->toDateString())
            ->selectRaw('status, COUNT(*) as n, COALESCE(SUM(amount_paise), 0) as total')->groupBy('status')->get()->keyBy('status');
        $waiting = SalesExpense::where('user_id', $userId)->where('status', SalesExpense::PENDING)->selectRaw('COUNT(*) as n, COALESCE(SUM(amount_paise), 0) as total')->first();

        return [
            'spend_paise' => (int) $period->sum('total'),
            'approved' => (int) ($period[SalesExpense::APPROVED]->n ?? 0),
            'waiting' => (int) $waiting->n,
            'waiting_paise' => (int) $waiting->total,
            'rejected' => (int) ($period[SalesExpense::REJECTED]->n ?? 0),
        ];
    }

    // =====================================================================================================================
    // The Enquiries pipeline board: urgency (the phone's rules), the six open stages, the closed strip, one enquiry's timeline.
    // =====================================================================================================================

    /** An open enquiry nobody has touched for this many days is "idle" (the phone's IDLE_DAYS). */
    public const ENQUIRY_IDLE_DAYS = 3;

    public const URGENCY_RANK = ['overdue' => 0, 'today' => 1, 'idle' => 2, 'planned' => 3, 'none' => 4];

    /**
     * How urgent is this enquiry right now? The same four answers as `urgency()` on the phone (src/core/enquiry.js), in business time:
     *  overdue: open and the follow-up moment has passed; today: open and the follow-up is later today; idle: open, no follow-up, untouched
     *  for MORE than 3 days (`client_updated_at`, else `opened_at`); planned: a follow-up on a later day; none: closed, or nothing due.
     *
     * @return array{key: string, label: string, days: int}
     */
    public static function enquiryUrgency(SalesEnquiry $e, ?CarbonImmutable $now = null): array
    {
        $now = static::now($now);

        if (! $e->isOpen()) {
            return ['key' => 'none', 'label' => '', 'days' => 0];
        }

        if ($e->next_followup_at) {
            $at = static::local($e->next_followup_at);

            if ($at->lte($now)) {
                $days = abs((int) round($at->startOfDay()->diffInDays($now->startOfDay())));

                return ['key' => 'overdue', 'label' => $days > 0 ? $days . ($days === 1 ? ' day late' : ' days late') : 'Late today', 'days' => $days];
            }

            if ($at->lt($now->startOfDay()->addDay())) {
                return ['key' => 'today', 'label' => 'Today ' . $at->format('g:i a'), 'days' => 0];
            }

            $days = abs((int) round($now->startOfDay()->diffInDays($at->startOfDay())));

            return ['key' => 'planned', 'label' => 'In ' . $days . ($days === 1 ? ' day' : ' days'), 'days' => $days];
        }

        $touched = $e->client_updated_at ? static::local($e->client_updated_at) : static::local($e->opened_at);
        $seconds = $now->getTimestamp() - $touched->getTimestamp();

        if ($seconds > self::ENQUIRY_IDLE_DAYS * 86400) {
            $days = intdiv($seconds, 86400);

            return ['key' => 'idle', 'label' => 'Idle ' . $days . ($days === 1 ? ' day' : ' days'), 'days' => $days];
        }

        return ['key' => 'none', 'label' => '', 'days' => 0];
    }

    /** The enquiries behind the board and the List view's export: rep, came through and a search (client, person, rep, the products asked for). */
    public static function enquiryQuery(array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $q = SalesEnquiry::query();

        if (filled($filters['rep'] ?? null)) {
            $q->where('user_id', (int) $filters['rep']);
        }
        if (filled($filters['source'] ?? null) && array_key_exists($filters['source'], SalesEnquiry::SOURCES)) {
            $q->where('source', $filters['source']);
        }
        if (filled($needle = trim((string) ($filters['q'] ?? '')))) {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $needle) . '%';
            $q->where(fn ($w) => $w->where('client_name', 'like', $like)->orWhere('contact_name', 'like', $like)->orWhereRaw('LOWER(CAST(items AS CHAR)) LIKE ?', [mb_strtolower($like)])
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)));
        }

        return $q;
    }

    /**
     * The board: the six open stages with their count, the rupees in play and the cards (most urgent first, at most `$perColumn`), plus the
     * numbers of the header and the chips. Cards that do not match the chosen chip are `dim` (the columns stay). One query for the open
     * enquiries (and one for their reps), whatever their number; sums and counts are made from that one list.
     *
     * @return array{columns: array<string, array>, counts: array<string, int>, summary: array<string, int>}
     */
    public static function pipelineBoard(array $filters = [], string $chip = 'all', int $perColumn = 8, ?CarbonImmutable $now = null): array
    {
        $now = static::now($now);
        $open = static::enquiryQuery($filters)->whereIn('status', SalesEnquiry::OPEN)->with('user')->orderBy('id')->limit(5000)->get()
            ->map(fn (SalesEnquiry $e) => ['e' => $e, 'u' => static::enquiryUrgency($e, $now)]);

        $count = fn (string $key) => $open->where('u.key', $key)->count();
        $counts = ['open' => $open->count(), 'overdue' => $count('overdue'), 'today' => $count('today'), 'idle' => $count('idle')];

        $columns = [];
        foreach (SalesEnquiry::OPEN as $stage) {
            $rows = $open->filter(fn (array $r) => $r['e']->status === $stage)
                ->sortBy(fn (array $r) => [self::URGENCY_RANK[$r['u']['key']], $r['e']->next_followup_at?->getTimestamp() ?? PHP_INT_MAX, -$r['e']->opened_at->getTimestamp()])->values();

            $columns[$stage] = [
                'key' => $stage,
                'label' => SalesEnquiry::STATUSES[$stage],
                'count' => $rows->count(),
                'paise' => (int) $rows->sum(fn (array $r) => $r['e']->budgetPaise()),
                'hidden' => max(0, $rows->count() - $perColumn),
                'cards' => $rows->take($perColumn)->map(fn (array $r) => [
                    'uuid' => $r['e']->uuid,
                    'client' => $r['e']->client_name,
                    'summary' => $r['e']->itemsSummary(2),
                    'paise' => $r['e']->budgetPaise(),
                    'rep' => $r['e']->user?->name ?? 'Rep',
                    'source' => $r['e']->sourceLabel(),
                    'urgency' => $r['u'],
                    'version' => $r['e']->client_updated_at?->format('Y-m-d H:i:s.v'),
                    'dim' => $chip !== 'all' && $r['u']['key'] !== $chip,
                ])->all(),
            ];
        }

        return [
            'columns' => $columns,
            'counts' => $counts,
            'summary' => [
                'open' => $counts['open'],
                'paise' => (int) $open->sum(fn (array $r) => $r['e']->budgetPaise()),
                'need' => $counts['overdue'] + $counts['today'] + $counts['idle'],
            ],
        ];
    }

    /**
     * The closed strip: what was won and lost in the month of `$month` (by `closed_at`, business time) and the win rate = won / (won + lost)
     * as a whole percent, null when nothing was decided.
     *
     * @return array{won: array{count: int, paise: int}, lost: array{count: int, paise: int}, rate: ?int, label: string}
     */
    public static function closedSummary(array $filters = [], ?CarbonImmutable $month = null): array
    {
        $start = ($month ?? static::today())->setTimezone(static::tz())->startOfMonth();
        $rows = static::enquiryQuery($filters)->whereIn('status', SalesEnquiry::CLOSED)
            ->where('closed_at', '>=', $start->utc())->where('closed_at', '<', $start->addMonth()->utc())->get(['status', 'items']);

        $part = fn (string $status) => ['count' => $rows->where('status', $status)->count(), 'paise' => (int) $rows->where('status', $status)->sum(fn (SalesEnquiry $e) => $e->budgetPaise())];
        $won = $part('won');
        $lost = $part('lost');
        $decided = $won['count'] + $lost['count'];

        return ['won' => $won, 'lost' => $lost, 'rate' => $decided > 0 ? (int) round(100 * $won['count'] / $decided) : null, 'label' => $start->format('F')];
    }

    /** The notes and status changes of one enquiry, newest first, in business time. @return list<array{at: CarbonImmutable, status: ?string, text: string, office: bool}> */
    public static function enquiryTimeline(SalesEnquiry $e): array
    {
        return collect($e->timeline ?? [])->map(fn (array $t) => [
            'at' => static::local($t['at']),
            'status' => ! empty($t['status']) ? (SalesEnquiry::STATUSES[$t['status']] ?? $t['status']) : null,
            'text' => (string) ($t['text'] ?? ''),
            'office' => str_contains((string) ($t['text'] ?? ''), '(office)'),
        ])->reverse()->values()->all();
    }
}
