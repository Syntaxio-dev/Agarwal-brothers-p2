<?php

namespace App\Support;

use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\Insight;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The numbers behind the dashboard's business-pulse widgets. Days are Indian days (IST), the database stores UTC.
 * Everything here is read-only.
 */
class DashboardData
{
    public const TZ = 'Asia/Kolkata';

    /** The chosen date range in days (7 / 30 / 90); anything else falls back to the default. */
    public static function days(?array $filters): int
    {
        $days = (int) ($filters['range'] ?? 0);

        return in_array($days, config('dashboard.ranges'), true) ? $days : (int) config('dashboard.default_range');
    }

    /** First moment (in the app's database timezone) of the range, today included. */
    public static function start(int $days): Carbon
    {
        return now(self::TZ)->subDays($days - 1)->startOfDay()->timezone(config('app.timezone'));
    }

    public static function previousStart(int $days): Carbon
    {
        return now(self::TZ)->subDays(2 * $days - 1)->startOfDay()->timezone(config('app.timezone'));
    }

    /** Rows created in the range and in the range before it, with the change in percent (null when there was nothing before). */
    public static function compare(string $model, int $days): array
    {
        $now = $model::where('created_at', '>=', self::start($days))->count();
        $before = $model::where('created_at', '>=', self::previousStart($days))->where('created_at', '<', self::start($days))->count();

        return ['now' => $now, 'before' => $before, 'change' => $before > 0 ? (int) round(($now - $before) / $before * 100) : null];
    }

    /** How many rows were created on each day of the range, oldest first. @return array{labels: list<string>, data: list<int>} */
    public static function daily(string $model, int $days): array
    {
        $dates = collect(range($days - 1, 0))->map(fn ($i) => now(self::TZ)->subDays($i)->startOfDay());

        $perDay = $model::where('created_at', '>=', self::start($days))
            ->pluck('created_at')
            ->countBy(fn ($at) => $at->copy()->timezone(self::TZ)->format('Y-m-d'));

        return [
            'labels' => $dates->map(fn ($d) => $d->format('j M'))->all(),
            'data' => $dates->map(fn ($d) => (int) ($perDay[$d->format('Y-m-d')] ?? 0))->all(),
        ];
    }

    /** Average hours from "received" to the first response, over enquiries of the range that have one. @return array{hours: ?float, count: int} */
    public static function averageResponse(int $days): array
    {
        $rows = Enquiry::where('created_at', '>=', self::start($days))->whereNotNull('first_responded_at')->get(['created_at', 'first_responded_at']);

        if ($rows->isEmpty()) {
            return ['hours' => null, 'count' => 0];
        }

        $minutes = $rows->avg(fn ($e) => max(0, $e->created_at->diffInMinutes($e->first_responded_at, false)));

        return ['hours' => round($minutes / 60, 1), 'count' => $rows->count()];
    }

    /** The enquiry or contact message that has waited longest without anybody touching it. @return ?array{type: string, name: string, since: Carbon, hours: float, url: string} */
    public static function oldestPending(User $user): ?array
    {
        $candidates = collect();

        if ($user->canManage('enquiries') && ($e = Enquiry::where('status', 'new')->oldest()->first())) {
            $candidates->push(['type' => 'Enquiry', 'name' => $e->name, 'since' => $e->created_at, 'url' => route('filament.admin.resources.enquiries.edit', $e)]);
        }

        if ($user->canManage('contact-messages') && ($m = ContactMessage::where('status', 'new')->oldest()->first())) {
            $candidates->push(['type' => 'Message', 'name' => $m->name, 'since' => $m->created_at, 'url' => route('filament.admin.resources.contact-messages.edit', $m)]);
        }

        $oldest = $candidates->sortBy('since')->first();

        return $oldest ? $oldest + ['hours' => round($oldest['since']->diffInMinutes(now()) / 60, 1)] : null;
    }

    /** Products people asked about most in the range (single and group enquiries). @return Collection<int, array{id: ?int, name: string, brand: ?string, count: int}> */
    public static function topProducts(int $days, int $limit = 5): Collection
    {
        $since = self::start($days);
        $counts = [];

        Enquiry::where('created_at', '>=', $since)->whereNotNull('product_id')
            ->selectRaw('product_id, count(*) as c')->groupBy('product_id')->get()
            ->each(function ($row) use (&$counts) {
                $counts['p' . $row->product_id] = ['id' => (int) $row->product_id, 'name' => null, 'count' => (int) $row->c];
            });

        EnquiryItem::whereHas('enquiry', fn ($q) => $q->where('created_at', '>=', $since))
            ->selectRaw('product_id, product_name, count(distinct enquiry_id) as c')->groupBy('product_id', 'product_name')->get()
            ->each(function ($row) use (&$counts) {
                $key = $row->product_id ? 'p' . $row->product_id : 'n' . $row->product_name;
                $counts[$key] = [
                    'id' => $row->product_id ? (int) $row->product_id : null,
                    'name' => $row->product_name,
                    'count' => ($counts[$key]['count'] ?? 0) + (int) $row->c,
                ];
            });

        $products = Product::with('category.brand')->whereIn('id', collect($counts)->pluck('id')->filter())->get()->keyBy('id');

        return collect($counts)
            ->map(fn ($c) => [
                'id' => isset($products[$c['id']]) ? $c['id'] : null,
                'name' => $products[$c['id']]->name ?? $c['name'] ?? 'Removed product',
                'brand' => $products[$c['id']]->category?->brand?->name ?? null,
                'count' => $c['count'],
            ])
            ->sortByDesc('count')->take($limit)->values();
    }

    /** Enquiries of the range per vertical of the product asked about. @return Collection<string, int> */
    public static function bySource(int $days): Collection
    {
        $enquiries = Enquiry::with('items')->where('created_at', '>=', self::start($days))->get(['id', 'product_id']);

        $ids = $enquiries->flatMap(fn ($e) => $e->items->pluck('product_id')->push($e->product_id))->filter()->unique();
        $products = Product::with('category.verticals')->whereIn('id', $ids)->get()->keyBy('id');

        $counts = collect();

        foreach ($enquiries as $e) {
            $names = $e->items->pluck('product_id')->push($e->product_id)->filter()->unique()
                ->flatMap(fn ($id) => isset($products[$id]) ? $products[$id]->category?->verticals->pluck('name') ?? [] : [])
                ->unique();

            foreach ($names->isEmpty() ? ['Other / general'] : $names as $name) {
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }

        return $counts->sortDesc();
    }

    /** Open enquiries per team member (and unassigned). @return Collection<int, array{name: string, open: int, oldest: Carbon, unassigned: bool}> */
    public static function workload(): Collection
    {
        $rows = Enquiry::open()->selectRaw('assigned_to, count(*) as c, min(created_at) as oldest')->groupBy('assigned_to')->get();
        $users = User::whereIn('id', $rows->pluck('assigned_to')->filter())->pluck('name', 'id');

        return $rows->map(fn ($r) => [
            'name' => $r->assigned_to ? ($users[$r->assigned_to] ?? 'Removed user') : 'Nobody yet',
            'open' => (int) $r->c,
            'oldest' => Carbon::parse($r->oldest),
            'unassigned' => $r->assigned_to === null,
        ])->sortBy([['open', 'desc'], ['name', 'asc']])->values();
    }

    /** Live products with a photo, specifications and an overview, out of all live products. @return array{total: int, complete: int, percent: int} */
    public static function catalogueHealth(): array
    {
        $total = Product::where('is_active', true)->count();
        $complete = Product::where('is_active', true)
            ->whereNotNull('image')->where('image', '!=', '')
            ->whereRaw('JSON_LENGTH(specs) > 0')
            ->whereNotNull('overview')->where('overview', '!=', '')
            ->count();

        return ['total' => $total, 'complete' => $complete, 'percent' => $total ? (int) floor($complete / $total * 100) : 100];
    }

    /** Live products nobody has edited for a long time. @return array{count: int, rows: Collection<int, Product>} */
    public static function stale(int $limit = 5): array
    {
        $query = Product::where('is_active', true)->where('updated_at', '<', now()->subMonths((int) config('dashboard.stale_months')));

        return ['count' => (clone $query)->count(), 'rows' => $query->orderBy('updated_at')->limit($limit)->get()];
    }

    /** Webinars starting within the next 7 days, soonest first. */
    public static function upcomingWebinars(): Collection
    {
        $limit = now(Insight::TZ)->addDays(7)->endOfDay();

        return Insight::where('type', 'webinar')->where('is_active', true)->get()
            ->filter(fn (Insight $w) => $w->isUpcoming() && $w->start_ist->lte($limit))
            ->sortBy(fn (Insight $w) => $w->start_ist)
            ->values();
    }

    /** 90 minutes, 5 h, 2.5 days: how long, in words a person says. */
    public static function humanHours(?float $hours): string
    {
        if ($hours === null) {
            return '-';
        }

        if ($hours < 1) {
            return max(1, (int) round($hours * 60)) . ' min';
        }

        if ($hours < 48) {
            return rtrim(rtrim(number_format($hours, 1), '0'), '.') . ' h';
        }

        return rtrim(rtrim(number_format($hours / 24, 1), '0'), '.') . ' days';
    }
}
