<?php

namespace App\Sales\Support;

use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesProfile;
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
            'clients_without_people' => SalesClient::whereDoesntHave('contacts')->count(),
            'clients_unassigned' => SalesClient::whereNull('assigned_to')->count(),
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

        return SalesClient::query()
            ->with('assignee')
            ->whereDoesntHave('calls', fn ($q) => $q->where('started_at', '>=', $since))
            ->whereDoesntHave('meetings', fn ($q) => $q->where('meeting_at', '>=', $since))
            ->where('created_at', '<', $since)   // a client added this week is not "neglected" yet
            ->orderBy('name')
            ->limit($limit)
            ->get();
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

    /** @return Collection<int, array> every active rep with this window's figures, biggest spender first */
    public static function reps(int $days): Collection
    {
        $range = [static::from($days)->toDateString(), static::today()->toDateString()];
        $spend = SalesExpense::selectRaw('user_id, SUM(amount_paise) as total, COUNT(*) as n')->whereBetween('expense_date', $range)->groupBy('user_id')->get()->keyBy('user_id');
        $pending = SalesExpense::selectRaw('user_id, COUNT(*) as n')->where('status', SalesExpense::PENDING)->groupBy('user_id')->pluck('n', 'user_id');
        $meetings = SalesMeeting::selectRaw('user_id, COUNT(*) as n')->where('meeting_at', '>=', static::fromUtc($days))->groupBy('user_id')->pluck('n', 'user_id');
        $enquiries = SalesEnquiry::selectRaw('user_id, COUNT(*) as n, SUM(CASE WHEN next_followup_at < ? THEN 1 ELSE 0 END) as late', [now()])->whereIn('status', SalesEnquiry::OPEN)->groupBy('user_id')->get()->keyBy('user_id');
        $calls = SalesCall::selectRaw('user_id, COUNT(*) as n, SUM(duration_seconds) as secs')->where('started_at', '>=', static::fromUtc($days))->groupBy('user_id')->get()->keyBy('user_id');

        return SalesProfile::active()->with('user')->get()->map(fn (SalesProfile $p) => [
            'id' => $p->id,
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
