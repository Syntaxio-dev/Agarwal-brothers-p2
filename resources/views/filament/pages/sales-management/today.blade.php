@use('App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource')
{{-- 2. Today in the field: five tiles and the team timeline (this card refreshes itself every minute so "now" and "next" move) --}}
@php
    $t = $today['tiles'];
    $axis = $today['axis'];
    $segments = max(1, intdiv($axis['high'] - $axis['low'], 60));
    $names = fn (array $list) => collect($list)->take(2)->implode(', ') . (count($list) > 2 ? ' +' . (count($list) - 2) : '');
    $wordClass = ['done' => 'ok', 'missed' => 'late', 'not logged' => 'late', 'next' => '', 'planned' => ''];
@endphp

<section class="sx-card sx-today" aria-label="Today in the field" wire:poll.60s.visible>
    <div class="sx-card-head">
        <p class="sx-eyebrow">Today in the field</p>
        <span class="sx-small">{{ now($tz)->format('g:i a') }} &middot; refreshes every minute</span>
    </div>

    <div class="sx-tiles">
        <div class="sx-tile">
            <p class="sx-eyebrow">Meetings today</p>
            <b>{{ $t['meetings']['total'] }}</b>
            <p class="sx-small">{{ $t['meetings']['done'] }} done &middot; {{ $t['meetings']['to_go'] }} to go @if ($t['meetings']['missed']) &middot; <span class="sx-late">{{ $t['meetings']['missed'] }} missed</span>@endif</p>
        </div>
        <div class="sx-tile">
            <p class="sx-eyebrow">Routes</p>
            <b>{{ $t['routes']['total'] }}</b>
            <p class="sx-small">{{ $t['routes']['total'] ? $t['routes']['on_the_way'] . ' on the way' : 'None today' }}</p>
        </div>
        <div class="sx-tile">
            <p class="sx-eyebrow">Calls today</p>
            <b>{{ $t['calls']['total'] }}</b>
            <p class="sx-small">{{ $t['calls']['out'] }} out &middot; {{ $t['calls']['in'] }} in @if ($t['calls']['missed']) &middot; <span class="sx-late">{{ $t['calls']['missed'] }} missed</span>@endif</p>
        </div>
        <div class="sx-tile">
            <p class="sx-eyebrow">Reports in</p>
            <b>{{ $t['reports']['in'] }} of {{ $t['reports']['total'] }}</b>
            <p class="sx-small {{ $t['reports']['missing'] ? 'sx-late' : '' }}">{{ $t['reports']['missing'] ? 'Waiting: ' . $names($t['reports']['missing']) : 'Everybody has reported' }}</p>
        </div>
        <div class="sx-tile">
            <p class="sx-eyebrow">Reps synced</p>
            <b>{{ $t['synced']['in'] }} of {{ $t['synced']['total'] }}</b>
            <p class="sx-small {{ $t['synced']['missing'] ? 'sx-late' : '' }}">{{ $t['synced']['missing'] ? 'Not synced: ' . $names($t['synced']['missing']) : 'Every phone synced today' }}</p>
        </div>
    </div>

    @if (empty($today['reps']))
        <p class="sx-empty">No reps yet. Add one under Sales team &gt; Reps.</p>
    @else
        <div class="sx-tl" style="--sx-hours: {{ $segments }}">
            <div class="sx-tl-axis" aria-hidden="true">
                <div></div>
                <div class="sx-tl-hours">
                    @foreach ($axis['hours'] as $h)
                        <span style="left: {{ $h['pct'] }}%">{{ $h['label'] }}</span>
                    @endforeach
                    @if ($axis['now_pct'] !== null)
                        <span class="sx-now-tag" style="left: {{ $axis['now_pct'] }}%">NOW</span>
                    @endif
                </div>
            </div>

            @foreach ($today['reps'] as $rep)
                @php
                    $meetingUrl = fn (array $block) => $to('sales-meetings', SalesMeetingResource::class, ['tableFilters' => ['user_id' => ['value' => $rep['user_id']]], 'tableSearch' => $block['client']]);
                @endphp
                <div class="sx-tl-row" wire:key="tl-{{ $rep['user_id'] }}">
                    <div class="sx-tl-who">
                        <span class="sx-avatar">{{ $initials($rep['name']) }}</span>
                        <div class="sx-rep-text">
                            <p class="sx-name sx-trunc">{{ $rep['name'] }}</p>
                            <p class="sx-small sx-trunc"><span class="sx-dot {{ $rep['state'] }}" aria-hidden="true"></span>{{ $rep['status_line'] }}</p>
                        </div>
                    </div>

                    <div>
                        <div class="sx-tl-track" aria-hidden="true">
                            @if ($axis['now_pct'] !== null)
                                <span class="sx-now" style="left: {{ $axis['now_pct'] }}%"></span>
                            @endif
                            @foreach ($rep['lines'] as $line)
                                <span class="sx-route-label" style="left: {{ $line['label_left'] }}%">{{ $line['title'] }}</span>
                                @if ($line['stops'] > 1)
                                    <span class="sx-route-line" style="left: {{ $line['from'] }}%; width: {{ max(0, $line['to'] - $line['from']) }}%"></span>
                                @endif
                            @endforeach
                            @foreach ($rep['blocks'] as $block)
                                @php $url = $meetingUrl($block); @endphp
                                @if ($url)
                                    <a href="{{ $url }}" tabindex="-1" class="sx-tm {{ $block['state'] }} {{ $block['late'] ? 'late' : '' }}" style="left: {{ $block['left'] }}%; width: {{ $block['width'] }}%" title="{{ $block['time'] }} {{ $block['client'] }}, {{ $block['word'] }}">
                                @else
                                    <span class="sx-tm {{ $block['state'] }} {{ $block['late'] ? 'late' : '' }}" style="left: {{ $block['left'] }}%; width: {{ $block['width'] }}%" title="{{ $block['time'] }} {{ $block['client'] }}, {{ $block['word'] }}">
                                @endif
                                    <b>{{ $block['time'] }}</b><span>{{ $block['client'] }}</span>
                                @if ($url) </a> @else </span> @endif
                            @endforeach
                            @if ($rep['no_plan'])
                                <span class="sx-chip late sx-tl-empty"><x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-3.5 w-3.5" />No meetings planned today</span>
                            @endif
                        </div>

                        {{-- the text alternative: read by a screen reader on a wide screen, the list itself on a narrow one --}}
                        <ul class="sx-tl-list" aria-label="{{ $rep['name'] }}: meetings today">
                            @forelse ($rep['blocks'] as $block)
                                @php $url = $meetingUrl($block); @endphp
                                <li>
                                    <span class="t">{{ $block['time'] }}</span>
                                    @if ($url)<a class="c" href="{{ $url }}">{{ $block['client'] }}</a>@else<span class="c">{{ $block['client'] }}</span>@endif
                                    <span class="sx-chip {{ $wordClass[$block['word']] ?? '' }}">{{ ucfirst($block['word']) }}</span>
                                </li>
                            @empty
                                <li><span class="c sx-mute">No meetings planned today</span></li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endforeach

            <div class="sx-tl-legend" aria-hidden="true">
                <span><i class="done"></i>Done</span>
                <span><i class="next"></i>Next</span>
                <span><i></i>Planned</span>
                <span><i class="missed"></i>Missed</span>
                <span><i class="route"></i>One route</span>
            </div>
        </div>
    @endif
</section>
