@php
    use App\Sales\Filament\Resources\SalesCalls\SalesCallResource;
    use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
    use App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource;
    use App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource;
    use App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource;
    use App\Sales\Filament\Resources\SalesReports\SalesReportResource;
    use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
    use App\Sales\Models\SalesEnquiry;
    use App\Sales\Models\SalesExpense;
    use Illuminate\Support\Str;

    $tz = config('sales.timezone');
    $statusNames = SalesEnquiry::STATUSES;
    $categoryNames = SalesExpense::CATEGORY_LABELS;

    // The view only formats: every figure comes from SalesInsights. A link exists only when the manager may open that screen.
    $to = fn (string $area, string $resource, array $params = [], string $page = 'index') => $can($area) ? $resource::getUrl($page, $params) : null;
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $dayShort = fn ($time) => $time ? \Carbon\CarbonImmutable::instance($time)->timezone($tz)->format('j M') : '';
    $people = fn (int $n) => $n . ' ' . ($n === 1 ? 'person' : 'people');
    $plural = fn (int $n, string $word) => $n . ' ' . $word . ($n === 1 ? '' : 's');

    // a 14-point sparkline as SVG points (x 0..100, y 2..26, the biggest value on top)
    $spark = function (array $values): string {
        $n = count($values);
        $max = max(1, ...$values);

        return collect($values)->map(fn ($v, $i) => round($n > 1 ? 100 * $i / ($n - 1) : 0, 2) . ',' . round(26 - 24 * $v / $max, 2))->implode(' ');
    };

    $s = $summary;
    $lastSync = $latestSync?->timezone($tz);
@endphp

<x-filament-panels::page>
    <div class="sx-page">

        {{-- 1. header --}}
        <header class="sx-head">
            <div>
                <p class="sx-eyebrow">Sales team</p>
                <h1 class="sx-h1">Sales management</h1>
                <p class="sx-sub">
                    {{ now($tz)->format('l, j F Y') }} &middot; {{ $s['reps_active'] }} active {{ $s['reps_active'] === 1 ? 'rep' : 'reps' }}
                    &middot; {{ $lastSync ? 'last sync ' . $lastSync->diffForHumans() : 'nobody has synced yet' }}
                </p>
            </div>
            <div class="sx-actions">
                @foreach ($ranges as $r)
                    <button type="button" class="sx-btn" aria-pressed="{{ $r === $days ? 'true' : 'false' }}" wire:click="setRange({{ $r }})">{{ $r }} days</button>
                @endforeach
                @if ($showMoney)
                    <button type="button" class="sx-btn" wire:click="exportReport" wire:loading.attr="disabled" wire:target="exportReport">
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                        Export report
                    </button>
                @endif
            </div>
        </header>

        {{-- 2. today in the field --}}
        @include('filament.pages.sales-management.today')

        {{-- 3. what needs a decision, and what needs a word --}}
        @include('filament.pages.sales-management.needs')

        {{-- 4. the six figures against the period before --}}
        @include('filament.pages.sales-management.kpis')

        {{-- 5. reps, stages, sources --}}
        @include('filament.pages.sales-management.reps')

        {{-- 6. the last rows --}}
        @include('filament.pages.sales-management.bottom')
    </div>
</x-filament-panels::page>
