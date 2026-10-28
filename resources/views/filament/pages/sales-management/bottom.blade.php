@use('App\Sales\Filament\Resources\SalesClients\SalesClientResource')
@use('App\Sales\Filament\Resources\SalesReps\SalesRepResource')
{{-- 6. The last row: what clients ask for, how visits ended, spend by category, trip and bills (bars are a share of the biggest value of the card) --}}
@php
    $askMax = max(1, ...array_column($topProducts, 'count') ?: [1]);
    $splits = $showMoney ? [
        'Local or outstation' => [$expenseSplit['work_area'], ['local' => 'Local (in Jaipur)', 'outstation' => 'Outstation', 'unknown' => 'Not told (older app)']],
        'With or without bill' => [$expenseSplit['bill'], ['with_bill' => 'With bill', 'without_bill' => 'Without bill', 'unknown' => 'Not told (older app)']],
    ] : [];
@endphp

<section class="sx-last4">
    <div class="sx-card">
        <p class="sx-eyebrow" style="margin-bottom:.25rem">What clients ask for</p>
        @forelse ($topProducts as $p)
            <div style="margin-top:.75rem">
                <div class="sx-line" style="margin-top:0"><span style="font-weight:500" class="sx-trunc">{{ $p['product'] }}</span><span class="sx-mute">{{ $p['count'] }}</span></div>
                <div class="sx-track"><div class="sx-fill" style="width: {{ round(100 * $p['count'] / $askMax) }}%"></div></div>
            </div>
        @empty
            <p class="sx-empty">No enquiries in the last {{ $days }} days.</p>
        @endforelse
    </div>

    <div class="sx-card">
        <p class="sx-eyebrow" style="margin-bottom:.25rem">How visits ended</p>
        @if ($meetingResults['total'] === 0)
            <p class="sx-empty">No visits done in the last {{ $days }} days.</p>
        @else
            @foreach ($meetingResults['items'] as $item)
                <div style="margin-top:.75rem">
                    <div class="sx-line" style="margin-top:0"><span style="font-weight:500">{{ $item['label'] }}</span><span class="sx-mute">{{ $item['count'] }}</span></div>
                    <div class="sx-track"><div class="sx-fill" style="width: {{ round(100 * $item['count'] / $meetingResults['max']) }}%"></div></div>
                </div>
            @endforeach
        @endif
    </div>

    @if ($showMoney)
        <div class="sx-card">
            <p class="sx-eyebrow" style="margin-bottom:.25rem">Spend by category</p>
            @forelse (array_slice($categories, 0, 5) as $c)
                <div style="margin-top:.75rem">
                    <div class="sx-line" style="margin-top:0"><span style="font-weight:500">{{ $categoryNames[$c['category']] ?? ucfirst($c['category']) }}</span><span class="sx-mute">{{ $money($c['paise']) }} &middot; {{ round(100 * $c['paise'] / $categoryTotal) }}%</span></div>
                    <div class="sx-track"><div class="sx-fill" style="width: {{ round(100 * $c['paise'] / $categoryTotal) }}%"></div></div>
                </div>
            @empty
                <p class="sx-empty">Nothing to show yet.</p>
            @endforelse
        </div>

        <div class="sx-card">
            @foreach ($splits as $title => [$figures, $names])
                @php $splitTotal = max(1, array_sum($figures)); @endphp
                <p class="sx-eyebrow" style="margin-bottom:.25rem;{{ $loop->first ? '' : 'margin-top:1.25rem' }}">{{ $title }}</p>
                @foreach ($figures as $key => $paise)
                    @continue($key === 'unknown' && $paise === 0)
                    <div style="margin-top:.75rem">
                        <div class="sx-line" style="margin-top:0"><span style="font-weight:500">{{ $names[$key] }}</span><span class="sx-mute">{{ $money($paise) }} &middot; {{ round(100 * $paise / $splitTotal) }}%</span></div>
                        <div class="sx-track"><div class="sx-fill" style="width: {{ round(100 * $paise / $splitTotal) }}%"></div></div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @endif
</section>

{{-- the client pool and the team, in two slim cards (they were two of the eight cards of the old page) --}}
<section class="sx-pool" aria-label="The client pool and the team">
    @if ($to('sales-clients', SalesClientResource::class))<a href="{{ $to('sales-clients', SalesClientResource::class) }}" class="sx-card">@else<div class="sx-card">@endif
        <p class="sx-eyebrow" style="width:100%">Client pool</p>
        <b>{{ $s['clients'] }}</b><span class="sx-small">{{ $plural($s['clients'], 'client') }}</span>
        <span class="sx-small">{{ $people($s['people']) }} &middot; {{ $s['people_alone'] }} without a company &middot; {{ $s['clients_called'] }} called</span>
    @if ($to('sales-clients', SalesClientResource::class))</a>@else</div>@endif

    @if ($to('sales-reps', SalesRepResource::class))<a href="{{ $to('sales-reps', SalesRepResource::class) }}" class="sx-card">@else<div class="sx-card">@endif
        <p class="sx-eyebrow" style="width:100%">Field reps</p>
        <b>{{ $s['reps_active'] }}</b><span class="sx-small">active</span>
        <span class="sx-small"><span class="{{ $s['reps_not_synced_today'] ? 'sx-late' : '' }}">{{ $s['reps_not_synced_today'] }} not synced today</span> &middot; {{ $plural($s['scripts'], 'own script') }} from {{ $plural($s['scripts_reps'], 'rep') }}</span>
    @if ($to('sales-reps', SalesRepResource::class))</a>@else</div>@endif
</section>
