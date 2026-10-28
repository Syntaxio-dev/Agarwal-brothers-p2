@php
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $stageKeys = array_keys($stages);
    $chips = ['all' => 'All open', 'overdue' => 'Overdue', 'today' => 'Due today', 'idle' => 'Idle 3+ days'];
    $chipCount = ['all' => $board['counts']['open'], 'overdue' => $board['counts']['overdue'], 'today' => $board['counts']['today'], 'idle' => $board['counts']['idle']];
@endphp

{{-- keyboard drag: Space picks a card up, left / right choose the stage, Enter drops, Escape cancels; every move is announced in the live region --}}
<div class="sx-page en-page"
     @if ($canMove) x-data="{
        held: null, target: 0, live: '',
        stageKeys: @js($stageKeys), labels: @js($stages),
        pick(el) { if (! @js($canMove) || this.held) return; this.held = { item: el.dataset.item, name: el.dataset.name, from: el.dataset.stage }; this.target = this.stageKeys.indexOf(el.dataset.stage); this.live = 'Picked up ' + this.held.name + ' from ' + this.labels[this.held.from] + '. Use the left and right arrow keys to choose a stage, Enter to drop, Escape to cancel.'; },
        step(d) { this.target = Math.max(0, Math.min(this.stageKeys.length - 1, this.target + d)); this.live = this.labels[this.stageKeys[this.target]] + '.'; },
        drop() { const to = this.stageKeys[this.target]; const h = this.held; this.held = null; if (to === h.from) { this.live = 'Dropped in ' + this.labels[to] + ', no change.'; return; } this.live = 'Dropped in ' + this.labels[to] + '.'; $wire.dropEnquiry(h.item, to, 0); },
        cancel() { this.held = null; this.live = 'Cancelled.'; },
        key(ev) {
            if (this.held) {
                if (ev.key === 'ArrowRight') { ev.preventDefault(); this.step(1); }
                else if (ev.key === 'ArrowLeft') { ev.preventDefault(); this.step(-1); }
                else if (ev.key === 'Enter') { ev.preventDefault(); this.drop(); }
                else if (ev.key === 'Escape') { ev.preventDefault(); this.cancel(); }
            } else if (ev.key === ' ' && ev.target.classList.contains('en-card')) { ev.preventDefault(); this.pick(ev.target); }
            else if (ev.key === 'Enter' && ev.target.classList.contains('en-card')) { $wire.openEnquiry(ev.target.dataset.uuid); }
        }
     }"
     @else x-data="{ key(ev) { if (ev.key === 'Enter' && ev.target.classList.contains('en-card')) { $wire.openEnquiry(ev.target.dataset.uuid); } } }" @endif
     x-on:keydown="key($event)">

    <header class="sx-head">
        <div>
            <p class="sx-eyebrow">Sales team</p>
            <h1 class="sx-h1">Enquiries</h1>
            <p class="sx-sub">{{ $board['summary']['open'] }} open &middot; {{ $short($board['summary']['paise']) }} in play &middot; {{ $board['summary']['need'] }} need a follow-up today</p>
        </div>
        <div class="sx-actions">
            <div class="en-toggle" role="group" aria-label="View">
                <button type="button" class="sx-btn" aria-pressed="true" wire:click="setMode('board')">Board</button>
                <button type="button" class="sx-btn" aria-pressed="false" wire:click="setMode('list')">List</button>
            </div>
            <label class="ex-field"><span class="sx-sr">Rep</span>
                <select wire:model.live="boardRep" aria-label="Rep"><option value="">All reps</option>@foreach ($reps as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
            </label>
            <label class="ex-field"><span class="sx-sr">Came through</span>
                <select wire:model.live="boardSource" aria-label="Came through"><option value="">Came through: any</option>@foreach ($sources as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
            </label>
            <button type="button" class="sx-btn" wire:click="mountAction('exportBoard')"><x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" /> Export</button>
        </div>
    </header>

    <div class="en-bar">
        <div class="en-chips" role="group" aria-label="Show">
            @foreach ($chips as $key => $label)
                <button type="button" class="sx-btn" aria-pressed="{{ $chip === $key ? 'true' : 'false' }}" wire:click="setChip('{{ $key }}')">
                    {{ $label }} <span class="sx-tab-count {{ $key === 'overdue' && $chipCount[$key] > 0 ? 'late' : '' }}">{{ $chipCount[$key] }}</span>
                </button>
            @endforeach
        </div>
        <label class="ex-field ex-search">
            <span class="sx-sr">Search enquiries</span>
            <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
            <input type="search" wire:model.live.debounce.300ms="boardSearch" placeholder="Search client, product, rep" autocomplete="off">
        </label>
    </div>

    @if ($canMove)<p class="sx-small en-hint">Drag a card to another stage, or use Move to&hellip; (keyboard: Space to pick up, arrows, Enter).</p>@endif
    <p class="sx-sr" aria-live="polite" x-text="live"></p>

    @if ($board['summary']['open'] === 0)
        <div class="sx-empty ex-empty">
            <p class="rp-big">No enquiries yet</p>
            <p class="sx-small">They appear here when a rep writes one in the app and syncs.</p>
        </div>
    @endif

    <div class="en-board" x-bind:class="{ 'is-holding': held }">
        @foreach ($board['columns'] as $col)
            <section class="en-col" x-bind:class="{ 'is-target': held && stageKeys[target] === '{{ $col['key'] }}' }" aria-labelledby="col-{{ $col['key'] }}">
                <header class="en-col-head">
                    <h2 id="col-{{ $col['key'] }}" class="en-col-title">{{ $col['label'] }}</h2>
                    <span class="sx-tab-count">{{ $col['count'] }}</span>
                    <span class="sx-small en-col-paise">{{ $short($col['paise']) }}</span>
                </header>
                <div class="en-list" role="list" aria-label="{{ $col['label'] }}"
                     @if ($canMove) x-sort="$wire.dropEnquiry($item, '{{ $col['key'] }}', $position)" x-sort:group="pipeline" @endif>
                    @foreach ($col['cards'] as $c)
                        @php $key = $c['uuid'] . '|' . $c['version'] . '|' . $col['key']; @endphp
                        <article class="en-card u-{{ $c['urgency']['key'] }} {{ $c['dim'] ? 'is-dim' : '' }}" role="listitem" tabindex="0" wire:key="enq-{{ $c['uuid'] }}"
                                 data-item="{{ $key }}" data-uuid="{{ $c['uuid'] }}" data-name="{{ $c['client'] }}" data-stage="{{ $col['key'] }}"
                                 x-bind:class="{ 'is-held': held && held.item === $el.dataset.item }"
                                 @if ($canMove) x-sort:item="'{{ $key }}'" @endif
                                 aria-label="{{ $c['client'] }}, {{ $col['label'] }}{{ $c['urgency']['label'] ? ', ' . $c['urgency']['label'] : '' }}">
                            <div class="en-card-top">
                                @if ($canMove)<span class="en-grip" x-sort:handle aria-hidden="true" title="Drag to another stage">&#8942;&#8942;</span>@endif
                                <button type="button" class="en-name" wire:click="openEnquiry('{{ $c['uuid'] }}')">{{ $c['client'] }}</button>
                                @if ($c['paise'] > 0)<span class="en-budget">{{ $short($c['paise']) }}</span>@endif
                            </div>
                            <p class="en-sum">{{ $c['summary'] }}</p>
                            <div class="en-card-foot">
                                <span class="sx-avatar en-avatar" aria-hidden="true" title="{{ $c['rep'] }}">{{ $initials($c['rep']) }}</span>
                                @if ($c['urgency']['label'])
                                    <span class="sx-chip {{ $c['urgency']['key'] === 'overdue' ? 'late' : ($c['urgency']['key'] === 'today' ? 'ok' : '') }}">{{ $c['urgency']['label'] }}</span>
                                @endif
                                @if ($c['source'])<span class="sx-small en-source">{{ $c['source'] }}</span>@endif
                                @if ($canMove)
                                    <span class="en-move" x-data="{ open: false }" x-on:keydown.escape.stop="open = false" x-on:click.outside="open = false">
                                        <button type="button" class="sx-btn en-movebtn" x-on:click="open = ! open" aria-haspopup="menu" x-bind:aria-expanded="open">Move to&hellip;</button>
                                        <ul class="en-menu" role="menu" x-show="open" x-cloak>
                                            @foreach ($stages as $sk => $sl)
                                                <li role="none"><button type="button" role="menuitemradio" aria-checked="{{ $sk === $col['key'] ? 'true' : 'false' }}" x-on:click="open = false; $wire.moveEnquiry('{{ $c['uuid'] }}', '{{ $sk }}', '{{ $col['key'] }}', '{{ $c['version'] }}')">{{ $sk === $col['key'] ? '✓ ' : '' }}{{ $sl }}</button></li>
                                            @endforeach
                                        </ul>
                                    </span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                    @if ($col['count'] === 0)<p class="en-emptycol">Nothing here</p>@endif
                </div>
                @if ($col['hidden'] > 0)
                    <button type="button" class="sx-link en-more" wire:click="showStage('{{ $col['key'] }}')">+{{ $col['hidden'] }} more in the List</button>
                @endif
            </section>
        @endforeach
    </div>

    {{-- the closed ones: while a card is dragged these become drop zones --}}
    <div class="en-closed" aria-label="Closed this month">
        @foreach (['won' => ['Order received', $closed['won'], $closed['rate'] !== null ? $closed['rate'] . '% of decided' : '—'], 'lost' => ['Lost', $closed['lost'], null]] as $key => [$label, $part, $extra])
            <div class="sx-card en-zone" x-bind:class="{ 'is-target': held && stageKeys[target] === '{{ $key }}' }"
                 @if ($canMove) x-sort="$wire.dropEnquiry($item, '{{ $key }}', $position)" x-sort:group="pipeline" @endif>
                <p class="sx-eyebrow">{{ $label }} &middot; {{ $closed['label'] }}</p>
                <p class="rp-big">{{ $part['count'] }} <span class="sx-small">&middot; {{ $short($part['paise']) }}@if ($extra) &middot; {{ $extra }}@endif</span></p>
                @if ($canMove)<p class="sx-small en-zonehint">Drop here to close as {{ $label }}</p>@endif
            </div>
        @endforeach
    </div>
</div>

<x-filament-actions::modals />
