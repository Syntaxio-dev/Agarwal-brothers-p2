<?php

namespace App\Sales\Support;

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
        ];
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

        return SalesProfile::active()->with('user')->get()->map(fn (SalesProfile $p) => [
            'id' => $p->id,
            'name' => $p->user->name,
            'code' => $p->employee_code,
            'spend_paise' => (int) ($spend[$p->user_id]->total ?? 0),
            'expenses' => (int) ($spend[$p->user_id]->n ?? 0),
            'pending' => (int) ($pending[$p->user_id] ?? 0),
            'meetings' => (int) ($meetings[$p->user_id] ?? 0),
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
