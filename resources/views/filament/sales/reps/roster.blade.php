@php
    $tz = config('sales.timezone');
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $chips = ['all' => 'All', 'active' => 'Active', 'off' => 'Switched off', 'attention' => 'Needs attention'];
    $cols = 4 + ($showMeetings ? 1 : 0) + ($showEnquiries ? 1 : 0) + ($showClients ? 1 : 0) + ($showMoney ? 2 : 0);

    // a sortable column header: a real button, aria-sort says which way
    $head = function (string $label, string $key, string $class = '') use ($activeSort, $activeDir) {
        $on = $activeSort === $key;

        return [$label, $key, $on ? ($activeDir === 'asc' ? 'ascending' : 'descending') : 'none', $class];
    };
@endphp

<x-filament-panels::page>
    <div class="sx-page rp-page">

        <header class="sx-head">
            <div>
                <p class="sx-eyebrow">Sales team</p>
                <h1 class="sx-h1">Reps</h1>
                <p class="sx-sub">{{ $subline }}</p>
            </div>
            <div class="sx-actions">
                <label class="rp-search">
                    <span class="sx-sr">Search reps</span>
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search name, code, phone" autocomplete="off">
                </label>
                <a href="{{ $createUrl }}" class="sx-btn sx-btn-primary">
                    <x-filament::icon icon="heroicon-m-plus" class="h-4 w-4" />
                    Add rep
                </a>
            </div>
        </header>

        <div class="rp-chips" role="group" aria-label="Show reps">
            @foreach ($chips as $key => $label)
                <button type="button" class="sx-btn" aria-pressed="{{ $activeFilter === $key ? 'true' : 'false' }}" wire:click="setFilter('{{ $key }}')">
                    {{ $label }} <span class="sx-tab-count {{ $key === 'attention' && $counts[$key] > 0 ? 'late' : '' }}">{{ $counts[$key] }}</span>
                </button>
            @endforeach
            <button type="button" class="sx-btn" aria-pressed="{{ $activeSort === 'order' ? 'true' : 'false' }}" wire:click="sortBy('order')">My order</button>
            <button type="button" class="sx-btn rp-email-toggle" aria-pressed="{{ $showEmail ? 'true' : 'false' }}" wire:click="$toggle('showEmail')">E-mail</button>
        </div>

        <section class="sx-card rp-card" aria-label="Reps">
            @if ($counts['all'] === 0)
                <p class="sx-empty">No reps yet. Add a rep, then give them the employee code and the password to sign in on the phone.</p>
            @elseif ($rows->isEmpty())
                <p class="sx-empty">No rep matches this filter or search.</p>
            @else
                <div class="sx-scroll">
                    @if ($canReorder)<p class="sx-small rp-dragnote">Drag a row by its handle to change the order. It is saved at once.</p>@endif
                    <table class="sx-table rp-roster">
                        <thead>
                            <tr>
                                @if ($canReorder)<th scope="col" class="rp-handle-col"><span class="sx-sr">Drag to reorder</span></th>@endif
                                @foreach ([$head('Rep', 'name', 'rp-first')] as [$label, $key, $aria, $class])
                                    <th scope="col" class="sx-rep-cell" aria-sort="{{ $aria }}"><button type="button" class="rp-sort" wire:click="sortBy('{{ $key }}')">{{ $label }}</button></th>
                                @endforeach
                                <th scope="col" class="rp-left">Today</th>
                                @foreach ([$head('Last sync', 'sync')] as [$label, $key, $aria])
                                    <th scope="col" class="rp-left" aria-sort="{{ $aria }}"><button type="button" class="rp-sort" wire:click="sortBy('{{ $key }}')">{{ $label }}</button></th>
                                @endforeach
                                @if ($showMeetings)
                                    @foreach ([$head('Meetings', 'meetings')] as [$label, $key, $aria])
                                        <th scope="col" class="rp-hide-sm" aria-sort="{{ $aria }}"><button type="button" class="rp-sort" wire:click="sortBy('{{ $key }}')">{{ $label }}</button></th>
                                    @endforeach
                                @endif
                                @if ($showEnquiries)<th scope="col" class="rp-hide-sm">Open enq.</th>@endif
                                @if ($showClients)<th scope="col" class="rp-hide-sm">Clients</th>@endif
                                @if ($showMoney)
                                    @foreach ([$head('Spent, ' . $monthName, 'spend')] as [$label, $key, $aria])
                                        <th scope="col" aria-sort="{{ $aria }}"><button type="button" class="rp-sort" wire:click="sortBy('{{ $key }}')">{{ $label }}</button></th>
                                    @endforeach
                                    <th scope="col" class="rp-hide-sm">Pending</th>
                                @endif
                                <th scope="col">Active</th>
                            </tr>
                        </thead>
                        <tbody @if ($canReorder) x-sort="$wire.moveRep($item, $position)" @endif>
                            @foreach ($rows as $r)
                                @php $phone = $phoneText($r['phone']); @endphp
                                <tr class="rp-row {{ $r['active'] ? '' : 'rp-off' }}" wire:key="rep-{{ $r['id'] }}" x-sort:item="{{ $r['id'] }}">
                                    @if ($canReorder)<td class="rp-handle-col"><button type="button" class="rp-handle" x-sort:handle aria-label="Drag {{ $r['name'] }} to a new place">&#8942;&#8942;</button></td>@endif
                                    <td class="sx-rep-cell">
                                        <div class="sx-rep">
                                            <span class="sx-avatar" aria-hidden="true">{{ $initials($r['name']) }}</span>
                                            <div class="sx-rep-text">
                                                <p class="sx-trunc"><a href="{{ $profileUrl($r['id']) }}" class="sx-name rp-name">{{ $r['name'] }}</a></p>
                                                <p class="sx-small rp-mono">{{ $r['code'] }}@if ($phone) &middot; {{ $phone }}@endif</p>
                                                @if ($showEmail && $r['email'])<p class="sx-small sx-trunc">{{ $r['email'] }}</p>@endif
                                                <p class="rp-copy">
                                                    @if ($r['phone'])
                                                        <button type="button" class="rp-copybtn" x-data="{ done: false }" x-on:click="navigator.clipboard?.writeText(@js($r['phone'])); done = true; setTimeout(() => done = false, 1500)" aria-label="Copy the phone number of {{ $r['name'] }}">
                                                            <span x-show="!done">Copy phone</span><span x-show="done" x-cloak>Copied</span>
                                                        </button>
                                                    @endif
                                                    @if (! $r['active'])
                                                        <button type="button" class="rp-copybtn rp-del" wire:click="mountAction('deleteRep', { id: {{ $r['id'] }} })" aria-label="Delete {{ $r['name'] }}">Delete</button>
                                                    @endif
                                                    @if ($r['email'])
                                                        <button type="button" class="rp-copybtn" x-data="{ done: false }" x-on:click="navigator.clipboard?.writeText(@js($r['email'])); done = true; setTimeout(() => done = false, 1500)" aria-label="Copy the e-mail of {{ $r['name'] }}">
                                                            <span x-show="!done">Copy e-mail</span><span x-show="done" x-cloak>Copied</span>
                                                        </button>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="rp-left" data-label="Today">
                                        <div class="rp-cell">
                                        <span class="sx-dot {{ $r['state'] }}" aria-hidden="true"></span>{{ $r['status_line'] }}
                                        @php $reason = collect($r['attention'])->first(fn ($text) => ! str_contains(mb_strtolower($r['status_line']), mb_strtolower($text))); @endphp
                                        @if ($reason)<p class="sx-small {{ $r['active'] ? 'sx-late' : '' }}">{{ $reason }}</p>@endif
                                        </div>
                                    </td>
                                    <td class="rp-left" data-label="Last sync">
                                        <div class="rp-cell">
                                        {{ $r['last_sync_at'] ? $r['last_sync_at']->timezone($tz)->diffForHumans() : 'Never' }}
                                        @if ($r['device_model'] || $r['app_version'])
                                            <p class="sx-small">{{ collect([$r['device_model'], $r['app_version'] ? 'app ' . $r['app_version'] : null])->filter()->implode(' · ') }}@if ($r['outdated']) &middot; <span class="sx-chip late">update</span>@endif</p>
                                        @endif
                                        </div>
                                    </td>
                                    @if ($showMeetings)<td class="rp-hide-sm" data-label="Meetings">{{ $r['active'] ? $r['meetings_month'] : '—' }}</td>@endif
                                    @if ($showEnquiries)
                                        <td class="rp-hide-sm" data-label="Open enquiries">
                                            @if ($r['active']){{ $r['enquiries_open'] }}@if ($r['enquiries_late'])<p class="sx-small sx-late">{{ $r['enquiries_late'] }} late</p>@endif @else — @endif
                                        </td>
                                    @endif
                                    @if ($showClients)<td class="rp-hide-sm" data-label="Clients">{{ $r['active'] ? $r['clients'] : '—' }}</td>@endif
                                    @if ($showMoney)
                                        <td data-label="Spent, {{ $monthName }}">{{ $r['active'] ? $money($r['month_spend_paise']) : '—' }}</td>
                                        <td class="rp-hide-sm" data-label="Pending">
                                            @if ($r['active'])<span class="sx-chip {{ $r['pending'] > 1 ? 'late' : '' }}">{{ $r['pending'] }}</span>@else — @endif
                                        </td>
                                    @endif
                                    <td data-label="Active">
                                        <button type="button" role="switch" class="rp-switch" aria-checked="{{ $r['active'] ? 'true' : 'false' }}" aria-label="Active: {{ $r['name'] }}" wire:click="toggleActive({{ $r['id'] }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $r['id'] }})">
                                            <span class="rp-knob"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
