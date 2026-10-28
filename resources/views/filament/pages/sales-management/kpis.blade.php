@use('App\Sales\Filament\Resources\SalesCalls\SalesCallResource')
@use('App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource')
@use('App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource')
@use('App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource')
{{-- 4. The six figures: value, change against the same number of days before, and the last 14 days as a sparkline --}}
@php
    $c = $compared;
    $dateFilter = ['date' => ['from' => \App\Sales\Support\SalesInsights::from($days)->toDateString(), 'until' => \App\Sales\Support\SalesInsights::today()->toDateString()]];
    $winRate = $c['win_rate'];

    // label, value, sub-line parts [text, late?], the comparison, the sparkline, the link
    $kpis = [];

    if ($showMoney) {
        $kpis[] = ['Spent', $short((int) $s['spend_paise']), [[$plural((int) $s['expense_count'], 'expense'), false], [$short((int) $s['approved_paise']) . ' approved', false], [$short((int) $s['pending_paise']) . ' waiting', false]],
            $c['spent'], $series['spent'], $to('sales-expenses', SalesExpenseResource::class, ['tab' => 'all', 'from' => $dateFilter['date']['from'], 'until' => $dateFilter['date']['until']])];
    }

    $kpis[] = ['Meetings done', (string) $c['meetings']['value'], [[$s['meetings_planned'] . ' planned ahead', false], $s['meetings_missed'] ? [$s['meetings_missed'] . ' missed', true] : null],
        $c['meetings'], $series['meetings'], $to('sales-meetings', SalesMeetingResource::class, ['tableFilters' => ['status' => ['values' => ['done']]]])];
    $kpis[] = ['New enquiries', (string) $c['enquiries']['value'], [[$s['enquiries_open'] . ' open', false], [$s['enquiries_overdue'] . ' overdue', $s['enquiries_overdue'] > 0]],
        $c['enquiries'], $series['enquiries'], $to('sales-enquiries', SalesEnquiryResource::class)];
    $kpis[] = ['Orders won', (string) $c['won']['value'], [[$s['enquiries_lost'] . ' lost', false], ['last ' . $days . ' days', false]],
        $c['won'], $series['won'], $to('sales-enquiries', SalesEnquiryResource::class, ['tableFilters' => ['status' => ['values' => ['won']]]])];
    $kpis[] = ['Win rate', $winRate['value'] === null ? '—' : $winRate['value'] . '%', [[$winRate['decided'] ? $winRate['won'] . ' of ' . $winRate['decided'] . ' decided' : 'Nothing decided yet', false]],
        $winRate, $series['win_rate'], $to('sales-enquiries', SalesEnquiryResource::class)];
    $kpis[] = ['Client calls', (string) $c['calls']['value'], [[$s['calls_out'] . ' out', false], [$s['calls_in'] . ' in', false], [$s['calls_missed'] . ' missed', $s['calls_missed'] > 0], [$talk($s['talk_seconds']) . ' talk', false]],
        $c['calls'], $series['calls'], $to('sales-calls', SalesCallResource::class)];
@endphp

<section class="sx-kpi6" aria-label="Key figures, compared with the {{ $days }} days before">
    @foreach ($kpis as [$label, $value, $parts, $cmp, $points, $url])
        @if ($url)
            <a href="{{ $url }}" class="sx-card sx-kpi">
        @else
            <div class="sx-card sx-kpi">
        @endif
            <p class="sx-eyebrow">{{ $label }}</p>
            <p class="sx-kpi-value">{{ $value }}</p>

            @if ($cmp['delta'] === 'new')
                <span class="sx-delta">new</span>
            @elseif ($cmp['delta'] !== null)
                <span class="sx-delta {{ $cmp['tone'] === 'none' ? '' : $cmp['tone'] }}" title="Compared with the {{ $days }} days before">
                    @if ($cmp['dir'] === 'up')<x-filament::icon icon="heroicon-m-arrow-up" />@elseif ($cmp['dir'] === 'down')<x-filament::icon icon="heroicon-m-arrow-down" />@endif
                    {{ $cmp['dir'] === 'up' ? '+' : '' }}{{ $cmp['delta'] }}%
                    <span class="sx-sr">{{ $cmp['dir'] === 'up' ? 'more' : ($cmp['dir'] === 'down' ? 'less' : 'the same') }} than the {{ $days }} days before</span>
                </span>
            @endif

            <p class="sx-stat-sub">
                @foreach (array_values(array_filter($parts)) as [$text, $late])
                    @if (! $loop->first) &middot; @endif<span class="{{ $late ? 'sx-late' : '' }}">{{ $text }}</span>
                @endforeach
            </p>

            <svg class="sx-spark" viewBox="0 0 100 28" preserveAspectRatio="none" role="img" aria-label="{{ $label }}, the last {{ count($points) }} days">
                <polyline points="{{ $spark($points) }}" />
            </svg>
        @if ($url)
            </a>
        @else
            </div>
        @endif
    @endforeach
</section>
