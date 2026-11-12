<x-filament-panels::page>
    @php
        $s = $this->screen();
        $list = $s['list'];
        $detail = $s['detail'];
        $links = $s['links'];
        $tz = $s['tz'];
        $initials = fn (?string $name) => collect(preg_split('/\s+/', trim((string) $name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
        $dayLabel = function ($day) use ($s) {
            $d = \Carbon\CarbonImmutable::parse($day, config('sales.timezone'));
            $diff = (int) $s['today']->diffInDays($d->startOfDay(), false);

            return ($diff === 0 ? 'Today · ' : ($diff === -1 ? 'Yesterday · ' : '')) . $d->format('D j M') . ($d->year !== $s['today']->year ? ' ' . $d->year : '');
        };
        $chipDefs = ['away' => ['Away', 'alert'], 'none' => ['No check-in', ''], 'not_logged' => ['Not logged', 'alert']];
        $filtersActive = filled($this->rep) || $this->statuses || filled($this->result) || filled($this->checkin) || $this->inRoute || $this->followupDue || filled($this->from) || filled($this->until);
    @endphp

    <div class="mv-wrap" x-data
         x-on:keydown.window="
            if ($event.target.closest('input, textarea, select, [contenteditable]')) return;
            if ($event.key === 'j' || $event.key === 'ArrowDown' && $event.target.closest('.mv-list')) { $event.preventDefault(); $wire.move(1); }
            else if ($event.key === 'k' || $event.key === 'ArrowUp' && $event.target.closest('.mv-list')) { $event.preventDefault(); $wire.move(-1); }
            else if ($event.key === 'Enter' && $event.target.closest('.mv-list')) { $refs.panel?.focus(); }">

        {{-- ---- left: the list ---- --}}
        <aside class="mv-list" aria-label="Visits">
            <label class="ca-search">
                <span class="sx-sr">Search visits</span>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Client, rep or purpose" autocomplete="off">
            </label>

            <div class="ca-chips" role="group" aria-label="Show">
                <button type="button" wire:click="clearChips" class="ca-chip" aria-pressed="{{ $this->chips === [] ? 'true' : 'false' }}">
                    All visits <span class="ca-count">{{ $s['chipCounts']['all'] }}</span>
                </button>
                @foreach ($chipDefs as $key => [$label, $tone])
                    <button type="button" wire:click="toggleChip('{{ $key }}')" aria-pressed="{{ in_array($key, $this->chips, true) ? 'true' : 'false' }}"
                            class="ca-chip {{ $tone === 'alert' ? 'ca-chip-alert' : '' }}">
                        {{ $label }} <span class="ca-count">{{ $s['chipCounts'][$key] }}</span>
                    </button>
                @endforeach
                <button type="button" class="ca-chip mv-filter-btn" wire:click="$toggle('filtersOpen')" aria-expanded="{{ $this->filtersOpen ? 'true' : 'false' }}">
                    Filters @if ($filtersActive)<span class="ca-count">on</span>@endif
                </button>
            </div>

            @if ($this->filtersOpen)
                <div class="mv-filters">
                    <label>Rep
                        <select wire:model.live="rep"><option value="">All reps</option>@foreach ($s['reps'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
                    </label>
                    <label>Result
                        <select wire:model.live="result"><option value="">Any</option>@foreach (\App\Sales\Models\SalesMeeting::RESULTS as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                    </label>
                    <label>Check-in
                        <select wire:model.live="checkin"><option value="">Any</option><option value="none">No check-in</option><option value="verified">At client</option><option value="away">Away from client</option></select>
                    </label>
                    <fieldset class="mv-status"><legend>Status</legend>
                        @foreach (\App\Sales\Models\SalesMeeting::STATUSES as $k => $v)
                            <label class="mv-check"><input type="checkbox" value="{{ $k }}" wire:model.live="statuses"> {{ $v }}</label>
                        @endforeach
                    </fieldset>
                    <label class="mv-check"><input type="checkbox" wire:model.live="inRoute"> Part of a route</label>
                    <label class="mv-check"><input type="checkbox" wire:model.live="followupDue"> Follow-up due (7 days or overdue)</label>
                    <label>From <input type="date" wire:model.live="from"></label>
                    <label>Until <input type="date" wire:model.live="until"></label>
                    <button type="button" class="sx-btn" wire:click="resetFilters">Clear everything</button>
                </div>
            @endif

            <div class="mv-rows" role="listbox" aria-label="Visits, newest first">
                @php $lastDay = null; @endphp
                @forelse ($list['rows'] as $row)
                    @if ($row['day'] !== $lastDay)
                        @php $lastDay = $row['day']; @endphp
                        <p class="mv-day">{{ $dayLabel($row['day']) }}</p>
                    @endif
                    <button type="button" wire:key="visit-{{ $row['uuid'] }}" wire:click="select('{{ $row['uuid'] }}')" role="option"
                            aria-selected="{{ $row['uuid'] === $s['selected'] ? 'true' : 'false' }}"
                            class="mv-row {{ $row['uuid'] === $s['selected'] ? 'is-selected' : '' }}">
                        <span class="mv-circle {{ $row['has_selfie'] ? 'has-selfie' : 'no-selfie' }}" aria-hidden="true" title="{{ $row['has_selfie'] ? 'Selfie received' : 'No selfie' }}"></span>
                        <span class="ca-row-text">
                            <span class="ca-row-name">{{ $row['client'] }}</span>
                            <span class="ca-row-sub">{{ $row['at']->format('h:i A') }} · {{ $row['rep'] }}</span>
                        </span>
                        <span class="mv-chips">
                            <span class="mv-chip mv-{{ $row['status'] }}">{{ $row['status_label'] }}</span>
                            @if ($row['proof_chip'])
                                <span class="mv-chip mv-proof-{{ $row['proof_chip']['tone'] }}">{{ $row['proof_chip']['text'] }}</span>
                            @endif
                        </span>
                    </button>
                @empty
                    <p class="ca-empty">No visit matches. Visits the reps planned or wrote down appear here when they sync the app.</p>
                @endforelse
            </div>

            @if ($list['pages'] > 1)
                <div class="mv-pager">
                    <button type="button" class="sx-btn" wire:click="goToPage({{ $list['page'] - 1 }})" @disabled($list['page'] <= 1)>Newer</button>
                    <span class="sx-small sx-mute">Page {{ $list['page'] }} of {{ $list['pages'] }} · {{ $list['total'] }} visits</span>
                    <button type="button" class="sx-btn" wire:click="goToPage({{ $list['page'] + 1 }})" @disabled($list['page'] >= $list['pages'])>Older</button>
                </div>
            @endif
        </aside>

        {{-- ---- right: the open visit ---- --}}
        <section class="mv-panel" tabindex="-1" x-ref="panel" aria-label="Visit">
            @if (! $detail)
                <p class="ca-empty">Pick a visit on the left.</p>
            @else
                @php $m = $detail['m']; $p = $detail['proof']; @endphp
                <header class="ca-head">
                    <div>
                        <h2 class="ca-title">{{ $m->client_name }}</h2>
                        <p class="sx-small sx-mute">
                            {{ $detail['client']?->city ?: 'No place' }} · {{ $detail['when']->format('d M Y, h:i A') }} · {{ $m->user?->name }}
                        </p>
                    </div>
                    <span class="mv-chip mv-{{ $p['status'] }}">{{ $p['status_label'] }}</span>
                </header>

                {{-- 1. the proof --}}
                <div class="ca-card">
                    <h3 class="sx-eyebrow">Proof of the visit</h3>
                    @if ($p['state'] === 'none')
                        <p class="mv-empty">{{ $p['empty'] }}</p>
                    @else
                        <div class="mv-proof">
                            <div class="mv-selfie">
                                @if ($links['selfie'])
                                    <a href="{{ $links['selfie'] }}" target="_blank" rel="noopener" title="Open selfie">
                                        <img src="{{ $links['selfie'] }}" alt="Check-in selfie" loading="lazy" width="200" height="200">
                                    </a>
                                @else
                                    <span class="mv-selfie-empty">Selfie not received yet. It follows when the phone has signal.</span>
                                @endif
                            </div>
                            <div class="mv-proof-text">
                                <p class="mv-line mv-line-{{ $p['state'] }}">{{ $p['line'] }}</p>

                                @if ($p['scale'] !== null)
                                    <div class="mv-scale" role="img" aria-label="{{ $p['line'] }}">
                                        <span class="mv-zone" style="width: {{ $p['zone'] }}%"></span>
                                        <span class="mv-pin mv-pin-client" title="Client's saved spot"></span>
                                        <span class="mv-pin mv-pin-checkin mv-pin-{{ $p['state'] }}" style="left: {{ $p['scale'] }}%" title="Check-in"></span>
                                        <span class="mv-tick mv-tick-0">Client</span>
                                        <span class="mv-tick mv-tick-300" style="left: {{ $p['zone'] }}%">300 m</span>
                                        <span class="mv-tick mv-tick-label" style="left: {{ min($p['scale'], 90) }}%">{{ \App\Sales\Support\SalesInsights::distanceText((int) $p['distance_m']) }}</span>
                                    </div>
                                @elseif ($p['state'] === 'no_client_location')
                                    <p class="mv-empty">This client has no saved location, so the distance cannot be worked out. If the selfie shows the rep at the client, you can save this spot for the client.</p>
                                @endif

                                <dl class="mv-facts">
                                    <div><dt>Checked in</dt><dd>{{ $detail['checkin_at']?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                                    <div><dt>Reached the office</dt><dd>{{ $detail['received_at']?->format('d M Y, h:i A') ?: '—' }}</dd></div>
                                    <div><dt>GPS accuracy</dt><dd>{{ $m->checkin_accuracy_m !== null ? $m->checkin_accuracy_m . ' m' : '—' }}</dd></div>
                                    <div><dt>Client's spot set by</dt><dd>{{ $p['spot_source'] }}</dd></div>
                                </dl>

                                @if ($p['clock_gap'])
                                    <p class="mv-alert" role="alert">Clock gap: the phone's time and the office's differ by more than {{ \App\Sales\Support\SalesInsights::CLOCK_GAP_MINUTES }} minutes ({{ $p['gap_minutes'] }} min). The phone's clock may be wrong.</p>
                                @endif

                                <div class="mv-buttons">
                                    @if ($links['selfie'])<a class="sx-btn" href="{{ $links['selfie'] }}" target="_blank" rel="noopener">Open selfie</a>@endif
                                    @if ($links['checkinMap'])<a class="sx-btn" href="{{ $links['checkinMap'] }}" target="_blank" rel="noopener">Check-in spot in Maps</a>@endif
                                    @if ($links['clientMap'])<a class="sx-btn" href="{{ $links['clientMap'] }}" target="_blank" rel="noopener">Client's spot in Maps</a>@endif
                                    @if ($links['canSpot']){{ $this->useAsClientSpotAction }}@endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 2. the visit --}}
                <div class="ca-card">
                    <h3 class="sx-eyebrow">The visit</h3>
                    <dl class="mv-facts mv-facts-wide">
                        <div><dt>Purpose</dt><dd>{{ $m->purpose ?: '—' }}</dd></div>
                        <div><dt>Result</dt><dd>{{ $m->resultLabel() ?: '—' }}</dd></div>
                        <div><dt>Follow-up</dt><dd class="{{ $detail['followup_past'] ? 'mv-late' : '' }}">{{ $detail['followup']?->format('d M Y, h:i A') ?: '—' }}@if ($detail['followup_past']) (past)@endif</dd></div>
                        <div><dt>People met</dt><dd>{{ $detail['people'] ? implode(', ', $detail['people']) : '—' }}</dd></div>
                        <div><dt>Route</dt><dd>@if ($detail['route']){{ $detail['route']['title'] }} · stop {{ $detail['route']['stop'] ?? '?' }} of {{ $detail['route']['of'] }}@else Not part of a route @endif</dd></div>
                        <div><dt>Plan</dt><dd>
                            {{ $detail['planned'] ? 'Planned for ' . $detail['planned']->format('d M Y, h:i A') : 'Not planned ahead' }}
                            @if ($detail['moved_from']) · moved from {{ $detail['moved_from']->format('d M Y, h:i A') }} @endif
                            @if ($p['status'] === 'not_logged' && $m->meeting_at->lt(now()->subDay())) · not logged for over a day @endif
                        </dd></div>
                    </dl>
                    @if (filled($m->discussion) || filled($m->outcome))
                        <div class="mv-quiet">
                            @if (filled($m->discussion))<p>{{ $m->discussion }}</p>@endif
                            @if (filled($m->outcome))<p class="sx-small sx-mute">Outcome: {{ $m->outcome }}</p>@endif
                        </div>
                    @endif
                </div>

                <div class="mv-buttons">
                    @if ($links['whatsapp'])<a class="sx-btn" href="{{ $links['whatsapp'] }}" target="_blank" rel="noopener">Message {{ $links['repFirst'] }}</a>@endif
                    @if ($links['client'])<a class="sx-btn" href="{{ $links['client'] }}">Open client</a>@endif
                    @if ($links['enquiries'])<a class="sx-btn" href="{{ $links['enquiries'] }}">Enquiries of this client</a>@endif
                </div>
                <p class="sx-small sx-mute">{{ $m->user?->name }} owns this visit. Changes made on their phone appear here after their next sync.</p>
            @endif
        </section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
