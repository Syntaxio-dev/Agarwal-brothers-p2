{{-- The rep's detail, shown in a slide-over when a row of the reps table is picked --}}
@php
    use App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource;
    use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
    use Illuminate\Support\Str;

    $tz = config('sales.timezone');
    $to = fn (string $area, string $resource, array $params = [], string $page = 'index') => $can($area) ? $resource::getUrl($page, $params) : null;
    $dayShort = fn ($time) => $time ? \Carbon\CarbonImmutable::instance($time)->timezone($tz)->format('j M') : '';
@endphp

<div class="sx-page" style="gap:0">
    @if ($detail)
        @php $r = $detail['rep']; @endphp
        <div class="sx-rep">
            <div class="sx-rep-text" style="flex:1">
                <p class="sx-small sx-trunc">{{ $r['code'] }}@if ($detail['phone']) &middot; {{ $detail['phone'] }}@endif</p>
                <p class="sx-small" style="margin-top:.25rem">
                    {{ $detail['device_model'] ?: 'Phone not known' }}@if ($detail['app_version']) &middot; app {{ $detail['app_version'] }}@endif
                    @if ($detail['last_sync_at']) &middot; {{ $detail['last_sync_at']->diffForHumans() }}@endif
                </p>
            </div>
            <span class="sx-chip {{ $detail['stale'] ? 'late' : 'ok' }}">{{ $detail['last_sync_at'] ? ($detail['stale'] ? 'Not synced' : 'Synced') : 'Never synced' }}</span>
        </div>

        <div class="sx-figures">
            @if ($showMoney)<div class="sx-figure"><span class="sx-eyebrow">Spent</span><b>{{ $money($r['spend_paise']) }}</b></div>@endif
            <div class="sx-figure"><span class="sx-eyebrow">Meetings</span><b>{{ $r['meetings'] }}</b></div>
            <div class="sx-figure"><span class="sx-eyebrow">Calls</span><b>{{ $r['calls'] }}</b></div>
        </div>

        <div class="sx-block" style="margin-top:0">
            <div class="sx-card-head" style="margin-bottom:.5rem">
                <p class="sx-eyebrow">Today's report</p>
                <span class="sx-chip {{ $detail['report']['submitted'] ? 'ok' : '' }}">{{ $detail['report']['submitted'] ? 'Submitted' : 'Not sent' }}</span>
            </div>
            <p class="sx-small" style="font-size:.8125rem;color:var(--sx-ink)">{{ $detail['report']['notes'] ? Str::limit($detail['report']['notes'], 150) : ($detail['report']['submitted'] ? 'No text written.' : 'Nothing written yet today.') }}</p>
        </div>

        <div class="sx-block">
            <p class="sx-eyebrow" style="margin-bottom:.25rem">Open enquiries</p>
            @forelse ($detail['enquiries'] as $e)
                <div class="sx-item {{ $loop->first ? 'first' : '' }}" style="padding-left:0;padding-right:0">
                    <div class="sx-item-text">
                        <p class="sx-trunc" style="font-weight:600;font-size:.875rem">{{ $e->client_name }}</p>
                        <p class="sx-small sx-trunc">{{ $e->itemsSummary(2) }}</p>
                    </div>
                    <span class="sx-chip {{ $e->isOverdue() ? 'late' : '' }}">{{ $e->isOverdue() ? 'Late ' . $dayShort($e->next_followup_at) : $e->statusLabel() }}</span>
                </div>
            @empty
                <p class="sx-empty" style="padding-left:0">No open enquiries.</p>
            @endforelse
        </div>

        <div class="sx-buttons">
            @if ($to('sales-reps', SalesRepResource::class, ['record' => $r['id']], 'edit'))
                <a class="sx-btn sx-btn-primary" href="{{ $to('sales-reps', SalesRepResource::class, ['record' => $r['id']], 'edit') }}">Open full rep page</a>
            @endif
            @if ($to('sales-expenses', SalesExpenseResource::class))
                <a class="sx-btn" href="{{ $to('sales-expenses', SalesExpenseResource::class, ['tab' => 'all', 'rep' => $r['user_id']]) }}">Expenses</a>
            @endif
        </div>
    @else
        <p class="sx-empty">This rep is not active any more.</p>
    @endif
</div>
