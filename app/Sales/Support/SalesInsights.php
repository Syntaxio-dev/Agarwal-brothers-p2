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
            'meetings' => (int) SalesMeeting::where('meeting_at', '>=', static::fromUtc($days))->count(),
            'meetings_today' => (int) SalesMeeting::where('meeting_at', '>=', static::today()->utc())->where('meeting_at', '<', static::today()->addDay()->utc())->count(),
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
            ->withMax('meetings', 'meeting_at')
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
            ->whereDoesntHave('meetings', fn ($q) => $q->where('meeting_at', '>=', $since))
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
        $meetings = SalesMeeting::selectRaw('user_id, COUNT(*) as n')->where('meeting_at', '>=', static::fromUtc($days))->groupBy('user_id')->pluck('n', 'user_id');
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

    private static function isStale(SalesProfile $p): bool
    {
        return ! $p->last_sync_at || $p->last_sync_at->lt(static::today()->utc());
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
}
