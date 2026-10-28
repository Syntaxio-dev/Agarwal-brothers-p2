@use('App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource')
@use('App\Sales\Filament\Resources\SalesReps\SalesRepResource')
{{-- 5. The reps (a click opens the rep's detail in a slide-over), the open enquiries by stage, and where new enquiries came from --}}
@php
    $stageMax = max(1, ...array_values($enquiryStatuses));
    $sourceColors = ['call' => 'sx-c1', 'walkin' => 'sx-c2', 'whatsapp' => 'sx-c3', 'reference' => 'sx-c4', 'unknown' => 'sx-c5'];
@endphp

<section class="sx-row2">
    <div class="sx-card">
        <div class="sx-card-head">
            <p class="sx-eyebrow">Reps, last {{ $days }} days</p>
            @if ($to('sales-reps', SalesRepResource::class))
                <a class="sx-link" href="{{ $to('sales-reps', SalesRepResource::class) }}">Manage reps</a>
            @endif
        </div>

        @if ($reps->isEmpty())
            <p class="sx-empty">No reps yet. Add one under Sales team &gt; Reps.</p>
        @else
            <div class="sx-scroll">
                <table class="sx-table">
                    <thead>
                        <tr>
                            <th scope="col" class="sx-rep-cell">Rep</th>
                            @if ($showMoney)<th scope="col">Spent</th>@endif
                            <th scope="col">Meetings</th>
                            <th scope="col">Calls</th>
                            <th scope="col">Open enquiries</th>
                            <th scope="col">Won</th>
                            @if ($showMoney)<th scope="col">Pending</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reps as $rep)
                            @php $now = $todayByUser->get($rep['user_id']); @endphp
                            <tr class="sx-row" wire:key="rep-{{ $rep['id'] }}" tabindex="0" role="button" aria-label="Open {{ $rep['name'] }}" aria-selected="{{ $rep['id'] === $selectedId ? 'true' : 'false' }}"
                                wire:click="selectRep({{ $rep['id'] }})" wire:keydown.enter="selectRep({{ $rep['id'] }})" wire:keydown.space.prevent="selectRep({{ $rep['id'] }})">
                                <td class="sx-rep-cell">
                                    <div class="sx-rep">
                                        <span class="sx-avatar">{{ $initials($rep['name']) }}</span>
                                        <div class="sx-rep-text">
                                            <p class="sx-trunc"><span class="sx-name">{{ $rep['name'] }}</span><span class="sx-code">{{ $rep['code'] }}</span></p>
                                            <p class="sx-small sx-trunc">
                                                <span class="sx-dot {{ $now['state'] ?? ($rep['stale'] ? 'stale' : 'office') }}" aria-hidden="true"></span>{{ $now['status_line'] ?? ($rep['stale'] ? 'Not synced today' : 'Office') }}
                                                &middot; {{ $rep['report_today'] ? 'Report in' : 'No report' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                @if ($showMoney)<td>{{ $money($rep['spend_paise']) }}</td>@endif
                                <td>{{ $rep['meetings'] }}</td>
                                <td>{{ $rep['calls'] }}@if ($rep['talk_seconds'])<br><span class="sx-small">{{ $talk($rep['talk_seconds']) }}</span>@endif</td>
                                <td>{{ $rep['enquiries_open'] }}@if ($rep['enquiries_late'])<br><span class="sx-small sx-late">{{ $rep['enquiries_late'] }} late</span>@endif</td>
                                <td>{{ $rep['won'] }}</td>
                                @if ($showMoney)<td><span class="sx-chip {{ $rep['pending'] > 1 ? 'late' : '' }}">{{ $rep['pending'] }}</span></td>@endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div style="display:grid;gap:1rem;min-width:0">
        <div class="sx-card">
            <div class="sx-card-head" style="margin-bottom:.25rem">
                <p class="sx-eyebrow">Open enquiries by stage</p>
                @if ($to('sales-enquiries', SalesEnquiryResource::class))<a class="sx-link" href="{{ $to('sales-enquiries', SalesEnquiryResource::class) }}">Open</a>@endif
            </div>
            <p class="sx-small">
                {{ $s['enquiries_open'] }} open &middot; <span class="{{ $s['enquiries_overdue'] ? 'sx-late' : '' }}">{{ $s['enquiries_overdue'] }} overdue</span> &middot; {{ $s['enquiries_due_today'] }} due today<br>
                {{ $s['enquiries_new'] }} new &middot; {{ $s['enquiries_won'] }} orders &middot; {{ $s['enquiries_lost'] }} lost &middot; {{ $s['enquiries_idle'] }} untouched for 3 days
            </p>
            @foreach ($enquiryStatuses as $key => $n)
                <div style="margin-top:.75rem">
                    <div class="sx-line" style="margin-top:0"><span style="font-weight:500">{{ $statusNames[$key] }}</span><span class="sx-mute">{{ $n }}</span></div>
                    <div class="sx-track"><div class="sx-fill" style="width: {{ round(100 * $n / $stageMax) }}%"></div></div>
                </div>
            @endforeach
        </div>

        <div class="sx-card">
            <p class="sx-eyebrow">Where new enquiries came from <span class="sx-new">NEW</span></p>
            @if ($enquirySources['total'] === 0)
                <p class="sx-empty" style="padding-left:0">No enquiries in the last {{ $days }} days.</p>
            @else
                <div class="sx-stack" role="img" aria-label="Enquiries by how they came through">
                    @foreach ($enquirySources['items'] as $item)
                        @if ($item['count'] > 0)<span class="{{ $sourceColors[$item['key']] ?? 'sx-c5' }}" style="width: {{ $item['percent'] }}%"></span>@endif
                    @endforeach
                </div>
                <div class="sx-legend">
                    @foreach ($enquirySources['items'] as $item)
                        <div><span><i class="{{ $sourceColors[$item['key']] ?? 'sx-c5' }}"></i>{{ $item['label'] }}</span><span class="sx-mute">{{ $item['count'] }} &middot; {{ $item['percent'] }}%</span></div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
