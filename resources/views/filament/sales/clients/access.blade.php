@php
    /** @var \App\Sales\Filament\Resources\SalesClients\Pages\EditSalesClient $page */
    $page = $getLivewire();
    $s = $page->screen();
    $client = $s['client'];
    $one = $page->mode === 'one';
    $enabled = $s['canManage'] && $s['poolOn'];
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp

<div class="ca-wrap" x-data="{ dirty: @js($s['dirty']) }" x-effect="dirty = @js($s['dirty'])"
     x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }">

    {{-- ---- left: the client list ---- --}}
    <aside class="ca-list" aria-label="Clients">
        <label class="ca-search">
            <span class="sx-sr">Search clients</span>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Client, place or person" autocomplete="off">
        </label>

        <div class="ca-chips" role="group" aria-label="Show">
            @foreach (['all' => 'All', 'nobody' => 'Nobody', 'one' => 'One rep', 'several' => 'Several'] as $key => $label)
                <button type="button" wire:click="$set('chip', '{{ $key }}')" aria-pressed="{{ $page->chip === $key ? 'true' : 'false' }}"
                        class="ca-chip {{ $key === 'nobody' ? 'ca-chip-alert' : '' }}">
                    {{ $label }} <span class="ca-count">{{ $s['chips'][$key] }}</span>
                </button>
            @endforeach
        </div>

        <ul class="ca-rows">
            @forelse ($s['list'] as $row)
                <li>
                    <a href="{{ $row['url'] }}" wire:key="client-row-{{ $row['id'] }}" class="ca-row {{ $row['id'] === $client->id ? 'is-selected' : '' }}"
                       @if ($row['id'] === $client->id) aria-current="true" @endif
                       x-on:click="if ($event.ctrlKey || $event.metaKey || $event.shiftKey || $event.button) return; $event.preventDefault(); if ({{ $row['id'] === $client->id ? 'true' : 'false' }}) return; if (! dirty || confirm('You have not saved who sees this client. Leave without saving?')) $wire.selectClient({{ $row['id'] }})">
                        <span class="ca-row-text">
                            <span class="ca-row-name">{{ $row['name'] }}</span>
                            <span class="ca-row-sub">{{ $row['city'] ?: 'No place' }} · {{ $row['people'] }} {{ \Illuminate\Support\Str::plural('person', $row['people']) }}</span>
                        </span>
                        @if ($row['reps']->isEmpty())
                            <span class="ca-nobody">Nobody</span>
                        @else
                            <span class="ca-stack" aria-label="{{ $row['reps']->pluck('name')->implode(', ') }}">
                                @foreach ($row['reps']->take(3) as $r)
                                    <span class="ca-av ca-av-sm" title="{{ $r['name'] }}">{{ $initials($r['name']) }}</span>
                                @endforeach
                                @if ($row['reps']->count() > 3)
                                    <span class="ca-av ca-av-sm ca-av-more">+{{ $row['reps']->count() - 3 }}</span>
                                @endif
                            </span>
                        @endif
                    </a>
                </li>
            @empty
                <li class="ca-empty">No client matches.</li>
            @endforelse
        </ul>
    </aside>

    {{-- ---- right: who sees the selected client ---- --}}
    <section class="ca-main">
        <header class="ca-head">
            <div>
                <h2 class="ca-title">{{ $client->name }}</h2>
                <p class="sx-small sx-mute">
                    {{ $client->city ?: 'No place' }} · {{ $s['peopleCount'] }} {{ \Illuminate\Support\Str::plural('person', $s['peopleCount']) }}
                    @if ($s['addedBy']) · added by {{ $s['addedBy'] }} @endif
                    @if ($client->created_at) on {{ $client->created_at->timezone(config('sales.timezone'))->format('j M Y') }} @endif
                </p>
            </div>
            <button type="button" class="sx-btn" wire:click="toggleDetails" aria-pressed="{{ $page->details ? 'true' : 'false' }}">
                {{ $page->details ? 'Hide details' : 'Edit details' }}
            </button>
        </header>

        <div class="ca-card">
            <div class="ca-card-head">
                <h3 class="sx-eyebrow">Who sees this client</h3>
                @if (! $s['poolOn'])
                    <p class="ca-note">Every rep sees every client while the private pool is off.</p>
                @endif
            </div>

            @if ($enabled)
                <div class="ca-seg" role="group" aria-label="How many reps">
                    <button type="button" wire:click="setMode('one')" aria-pressed="{{ $one ? 'true' : 'false' }}">One rep</button>
                    <button type="button" wire:click="setMode('several')" aria-pressed="{{ $one ? 'false' : 'true' }}">Several reps</button>
                </div>
                <p class="sx-small sx-mute ca-help">
                    @if ($one)
                        Only one rep (plus whoever added the client) sees this client and its people. Picking another rep moves it.
                    @else
                        Every ticked rep sees this client and all its people. Tick as many as you like.
                    @endif
                </p>
            @endif

            <ul class="ca-reps" role="list">
                @if ($s['locked'])
                    <li class="ca-rep is-on is-locked">
                        <span class="ca-av">{{ $initials($s['locked']['name']) }}</span>
                        <span class="ca-rep-text">
                            <span class="ca-rep-name">{{ $s['locked']['name'] }} <span class="ca-badge">Locked</span>
                                @if ($page->main === $s['locked']['id']) <span class="ca-badge ca-badge-main">Main rep</span> @endif
                            </span>
                            <span class="sx-small sx-mute">Added this client, so always sees it and its people</span>
                        </span>
                    </li>
                @endif

                @foreach ($s['reps'] as $rep)
                    @php $on = in_array($rep['id'], $page->selected, true); $isMain = $page->main === $rep['id']; @endphp
                    <li class="ca-rep {{ $on ? 'is-on' : '' }}">
                        <button type="button" class="ca-rep-hit" @disabled(! $enabled) wire:click="toggleRep({{ $rep['id'] }})"
                                role="{{ $one ? 'radio' : 'checkbox' }}" aria-checked="{{ $on ? 'true' : 'false' }}" aria-pressed="{{ $on ? 'true' : 'false' }}"
                                x-on:keydown.arrow-down.prevent="$el.closest('li').nextElementSibling?.querySelector('.ca-rep-hit')?.focus()"
                                x-on:keydown.arrow-up.prevent="$el.closest('li').previousElementSibling?.querySelector('.ca-rep-hit')?.focus()">
                            <span class="ca-box {{ $one ? 'ca-box-round' : '' }}" aria-hidden="true">@if ($on)<svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 8.5l3.2 3L13 4.5"/></svg>@endif</span>
                            <span class="ca-av">{{ $initials($rep['name']) }}</span>
                            <span class="ca-rep-text">
                                <span class="ca-rep-name">{{ $rep['name'] }}</span>
                                <span class="sx-small sx-mute">{{ $rep['code'] }} · {{ $s['counts'][$rep['id']] ?? 0 }} clients now</span>
                            </span>
                        </button>
                        @if ($on && $enabled)
                            <button type="button" class="ca-star {{ $isMain ? 'is-main' : '' }}" wire:click="makeMain({{ $rep['id'] }})"
                                    aria-pressed="{{ $isMain ? 'true' : 'false' }}" title="{{ $isMain ? 'Main rep' : 'Make main' }}">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="{{ $isMain ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8"><path d="M12 3.6l2.5 5.2 5.6.7-4.1 3.9 1 5.6L12 16.4 7 19l1-5.6L3.9 9.5l5.6-.7L12 3.6z"/></svg>
                                <span>{{ $isMain ? 'Main rep' : 'Make main' }}</span>
                            </button>
                        @elseif ($isMain)
                            <span class="ca-badge ca-badge-main">Main rep</span>
                        @endif
                    </li>
                @endforeach

                @if ($s['reps']->isEmpty() && ! $s['locked'])
                    <li class="ca-empty">There are no active reps yet.</li>
                @endif
            </ul>
            <p class="sx-small sx-mute ca-foot-note">The main rep is the one who calls and visits this client. It is optional.</p>
        </div>

        <div class="ca-card">
            <div class="ca-card-head">
                <h3 class="sx-eyebrow">People at this client <span class="ca-count">{{ $s['peopleCount'] }}</span></h3>
            </div>
            <p class="sx-small sx-mute">They go with the client: every rep above sees all of them, nobody else does.</p>
            @if ($s['peopleCount'] > 0)
                <ul class="ca-people">
                    @foreach ($s['people'] as $p)
                        <li><span class="ca-rep-name">{{ $p->name }}</span> <span class="sx-small sx-mute">{{ $p->position ?: '' }}</span></li>
                    @endforeach
                </ul>
                <div class="ca-more">
                    @if ($s['peopleCount'] > 6)<span class="sx-small sx-mute">+ {{ $s['peopleCount'] - 6 }} more</span>@endif
                    <button type="button" class="sx-btn" wire:click="$set('details', true)">Open all {{ $s['peopleCount'] }}</button>
                </div>
            @else
                <p class="ca-empty">No people saved at this client yet.</p>
            @endif
        </div>

        {{-- ---- sticky footer ---- --}}
        <div class="ca-bar" role="region" aria-label="Save who sees this client">
            <div class="ca-bar-text">
                <p class="ca-summary" aria-live="polite">{{ $s['summary'] }}</p>
                <p class="sx-small sx-mute">Phones update on their next sync. A rep who is taken off will no longer see it.</p>
            </div>
            @if ($enabled)
                <div class="ca-bar-actions">
                    <button type="button" class="sx-btn" wire:click="cancelChanges" @disabled(! $s['dirty'])>Cancel</button>
                    <button type="button" class="sx-btn sx-btn-primary" wire:click="saveAccess" wire:loading.attr="disabled" @disabled(! $s['dirty'])>Save who sees it</button>
                </div>
            @endif
        </div>
    </section>
</div>
