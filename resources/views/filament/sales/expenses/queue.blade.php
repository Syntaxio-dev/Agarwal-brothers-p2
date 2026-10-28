@php
    $tz = config('sales.timezone');
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $tabLabels = ['waiting' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'];
    $areaText = fn (?string $a) => match ($a) { 'outstation' => 'Outstation', 'local' => 'Local', default => 'Not told' };
    $chip = fn (\App\Sales\Models\SalesExpense $e) => match (true) {
        $e->bill_type === null => ['Not told', ''],
        $e->bill_type === 'without_bill' => ['No bill', 'late'],
        $e->hasBill() => ['Bill photo', 'ok'],
        default => ['Photo not here yet', ''],
    };
    $when = fn ($t) => $t ? \Carbon\CarbonImmutable::instance($t)->timezone($tz)->format('j M, g:i a') : '';
    $filtered = collect([$filters['rep'], $filters['category'], $filters['area'], $filters['bill'], $filters['q']])->filter(fn ($v) => filled($v))->count();
    $e = $detail['expense'] ?? null;
@endphp

<x-filament-panels::page>
    {{-- keys: A approve, R reject, J / K move; ignored while typing and while a window is open --}}
    <div class="sx-page ex-page"
         x-data="{ keys(ev) {
             if (ev.repeat || ev.ctrlKey || ev.metaKey || ev.altKey || ev.target.closest('input, textarea, select, [contenteditable]') || document.querySelector('.fi-modal-open')) return;
             const k = ev.key.toLowerCase();
             const shown = document.querySelector('.ex-row.on')?.dataset.uuid ?? null;
             if (k === 'a') $wire.approveCurrent(shown);
             else if (k === 'r') $wire.rejectCurrent(shown);
             else if (k === 'j') $wire.moveSelection(1);
             else if (k === 'k') $wire.moveSelection(-1);
         }, filters: false }"
         x-on:keydown.window="keys($event)">

        <header class="sx-head">
            <div>
                <p class="sx-eyebrow">Sales team</p>
                <h1 class="sx-h1">Expenses</h1>
                <p class="sx-sub">{{ $summary['waiting'] }} waiting &middot; {{ $rupees($summary['waiting_paise']) }} &middot; {{ $summary['reviewed'] }} reviewed {{ $win['custom'] ? 'in this period' : 'this month' }}</p>
            </div>
            <div class="sx-actions">
                <label class="ex-field">
                    <span class="sx-sr">Month</span>
                    <select wire:model.live="period" aria-label="Month">
                        @foreach ($months as $key => $label)<option value="{{ $key }}" @selected(! $win['custom'] && $monthKey === $key)>{{ $label }}</option>@endforeach
                    </select>
                </label>
                <button type="button" class="sx-btn" wire:click="mountAction('export')">
                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" /> Export
                </button>
            </div>
        </header>

        {{-- tabs and filters --}}
        <div class="ex-bar">
            <div class="ex-tabs" role="group" aria-label="Show expenses">
                @foreach ($tabLabels as $key => $label)
                    <button type="button" class="sx-btn" aria-pressed="{{ $activeTab === $key ? 'true' : 'false' }}" wire:click="setTab('{{ $key }}')">
                        {{ $label }} <span class="sx-tab-count {{ $key === 'waiting' && $counts[$key] > 0 ? 'late' : '' }}">{{ $counts[$key] }}</span>
                    </button>
                @endforeach
            </div>
            <button type="button" class="sx-btn ex-filters-toggle" x-on:click="filters = ! filters" x-bind:aria-expanded="filters">Filters @if ($filtered) <span class="sx-tab-count">{{ $filtered }}</span>@endif</button>
        </div>

        <div class="ex-filters" x-bind:class="{ 'open': filters }">
            <label class="ex-field ex-search">
                <span class="sx-sr">Search by rep or note</span>
                <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-4 w-4" />
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search rep or note" autocomplete="off">
            </label>
            <label class="ex-field"><span class="sx-sr">Rep</span>
                <select wire:model.live="rep" aria-label="Rep"><option value="">All reps</option>@foreach ($reps as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select>
            </label>
            <label class="ex-field"><span class="sx-sr">Category</span>
                <select wire:model.live="category" aria-label="Category"><option value="">All categories</option>@foreach ($categories as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
            </label>
            <label class="ex-field"><span class="sx-sr">Work area</span>
                <select wire:model.live="area" aria-label="Work area"><option value="">Any work area</option><option value="local">Local (in Jaipur)</option><option value="outstation">Outstation</option><option value="none">Not told</option></select>
            </label>
            <label class="ex-field"><span class="sx-sr">Bill</span>
                <select wire:model.live="bill" aria-label="Bill"><option value="">Any bill</option><option value="with_bill">With bill</option><option value="without_bill">Without bill</option><option value="none">Not told</option></select>
            </label>
            <label class="ex-field"><span class="ex-lab">Custom from</span><input type="date" wire:model.live="from" aria-label="Custom range from"></label>
            <label class="ex-field"><span class="ex-lab">until</span><input type="date" wire:model.live="until" aria-label="Custom range until"></label>
        </div>

        <div class="ex-grid">
            {{-- ===================== the queue ===================== --}}
            <section class="ex-queue" aria-label="Expenses">

                @if ($selectedRows->isNotEmpty())
                    <div class="ex-selbar" role="region" aria-label="Selected expenses">
                        <span><b>{{ $selectedRows->count() }} selected</b> &middot; {{ $rupees((int) $selectedRows->sum('amount_paise')) }}</span>
                        <span class="ex-selactions">
                            <button type="button" class="sx-btn sx-btn-approve" wire:click="mountAction('approveSelected')">Approve</button>
                            <button type="button" class="sx-btn" wire:click="mountAction('exportSelected')">Export selected</button>
                            <button type="button" class="sx-btn" wire:click="clearSelection">Clear</button>
                        </span>
                    </div>
                @endif

                @if ($activeTab === 'waiting')
                    @forelse (collect($groups)->filter(fn ($g) => $g['count'] > 0) as $g)
                        <div class="ex-group" wire:key="grp-{{ $g['key'] }}">
                            <div class="ex-group-head">
                                <div>
                                    <h2 class="ex-group-title">{{ $g['title'] }} <span class="sx-tab-count">{{ $g['count'] }}</span> <span class="sx-small">&middot; {{ $rupees($g['paise']) }}</span></h2>
                                    <p class="sx-small">{{ $g['text'] }}</p>
                                </div>
                                @if ($g['can_approve_all'])
                                    <button type="button" class="sx-btn sx-btn-approve" wire:click="mountAction('approveAll')">Approve all {{ $g['count'] }}</button>
                                @endif
                            </div>
                            @foreach ($g['rows'] as $row)
                                @include('filament.sales.expenses.row', ['row' => $row])
                            @endforeach
                        </div>
                    @empty
                        <div class="sx-empty ex-empty">
                            <p class="rp-big">All caught up</p>
                            <p class="sx-small">{{ $counts['approved'] }} approved and {{ $counts['rejected'] }} rejected in {{ $win['label'] }}.</p>
                            @if ($filtered)<p class="sx-small">A filter is set: it may be hiding what waits.</p>@endif
                            <button type="button" class="sx-link ex-linkbtn" wire:click="setTab('approved')">Open Approved</button>
                        </div>
                    @endforelse
                @else
                    <div class="ex-group">
                        @forelse ($flat as $row)
                            @include('filament.sales.expenses.row', ['row' => $row])
                        @empty
                            <p class="sx-empty">Nothing here for {{ $win['label'] }}{{ $filtered ? ' with these filters' : '' }}.</p>
                        @endforelse
                    </div>
                @endif
            </section>

            {{-- ===================== the detail pane ===================== --}}
            <aside class="ex-detail" aria-label="Expense detail">
                @if (! $e)
                    <p class="sx-empty">Pick an expense to see its bill and decide.</p>
                @else
                    @php [$billLabel, $billTone] = $chip($e); @endphp
                    <div class="sx-card ex-pane" wire:key="pane-{{ $e->uuid }}">
                        <div class="sx-card-head">
                            <p class="sx-eyebrow">{{ \App\Sales\Models\SalesExpense::categoryLabel($e->category) }} &middot; {{ $e->expense_date->format('j M Y') }}</p>
                            <span class="sx-chip {{ $e->status === 'approved' ? 'ok' : ($e->status === 'rejected' ? 'late' : '') }}">{{ ['pending' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Rejected'][$e->status] ?? ucfirst($e->status) }}</span>
                        </div>
                        <p class="ex-amount">{{ $rupees($e->amount_paise) }}</p>
                        <p class="ex-note">{{ $e->note ?: 'No note' }}</p>

                        {{-- the bill photo: only through the authorised route, never a public link --}}
                        @if ($detail['photo'])
                            <figure class="ex-photo" x-data="{ zoom: 1, rot: 0 }">
                                <div class="ex-photo-frame">
                                    <img src="{{ route('sales.bill', $e->uuid) }}" alt="Bill photo, {{ \App\Sales\Models\SalesExpense::categoryLabel($e->category) }}, {{ $rupees($e->amount_paise) }}, {{ $e->expense_date->format('j M') }}" loading="lazy" x-bind:style="`transform: rotate(${rot}deg) scale(${zoom})`">
                                </div>
                                <figcaption class="ex-photo-tools">
                                    <button type="button" class="sx-btn" x-on:click="zoom = zoom >= 2.5 ? 1 : zoom + 0.5">Zoom</button>
                                    <button type="button" class="sx-btn" x-on:click="rot = (rot + 90) % 360">Rotate</button>
                                    <a class="sx-btn" href="{{ route('sales.bill', $e->uuid) }}" target="_blank" rel="noopener noreferrer">Open full size</a>
                                </figcaption>
                            </figure>
                        @else
                            <div class="ex-nophoto">
                                @if ($e->bill_type === 'without_bill')
                                    <p><b>No bill photo.</b></p><p class="sx-small">The rep said there is no bill.</p>
                                @elseif ($e->bill_type === 'with_bill')
                                    <p><b>Photo not here yet.</b></p><p class="sx-small">The photo has not reached the office yet. It comes after the next sync.</p>
                                @else
                                    <p><b>No bill photo.</b></p><p class="sx-small">An older app did not say if there is a bill.</p>
                                @endif
                            </div>
                        @endif

                        <dl class="ex-fields">
                            <div><dt class="sx-eyebrow">Rep</dt><dd>{{ $e->user->name }} <span class="rp-mono sx-small">{{ $detail['profile']?->employee_code }}</span></dd></div>
                            <div><dt class="sx-eyebrow">Work area</dt><dd>{{ $e->work_area ? \App\Sales\Models\SalesExpense::WORK_AREAS[$e->work_area] ?? $e->work_area : 'Not told (older app)' }}</dd></div>
                            <div><dt class="sx-eyebrow">Bill</dt><dd>{{ $e->bill_type === 'with_bill' ? 'With bill' . ($e->hasBill() ? ' · photo received' : ' · photo not here yet') : ($e->bill_type === 'without_bill' ? 'Without bill' : 'Not told') }}</dd></div>
                            <div><dt class="sx-eyebrow">Sent from</dt><dd>{{ collect([$detail['profile']?->device_model, $detail['profile']?->app_version ? 'app ' . $detail['profile']->app_version : null, $detail['profile']?->last_sync_at ? 'synced ' . $detail['profile']->last_sync_at->timezone($tz)->diffForHumans() : null])->filter()->implode(' · ') ?: '—' }}</dd></div>
                        </dl>

                        @foreach ($detail['flags'] as $flag)
                            <div class="ex-flag" role="note"><b>{{ $flag['title'] }}.</b> {{ $flag['text'] }}</div>
                        @endforeach

                        {{-- decide, or show who decided --}}
                        @if ($e->isPending())
                            @if ($detail['can_review'])
                                <div class="ex-decide">
                                    <button type="button" class="sx-btn sx-btn-primary" wire:click="approveOne('{{ $e->uuid }}')" wire:loading.attr="disabled" wire:target="approveOne" aria-keyshortcuts="A">Approve <kbd>A</kbd></button>
                                    <button type="button" class="sx-btn rp-danger" wire:click="mountAction('reject', { uuid: '{{ $e->uuid }}' })" aria-keyshortcuts="R">Reject&hellip; <kbd>R</kbd></button>
                                    <span class="sx-small ex-pos">{{ $detail['position'] }} of {{ $detail['waiting_total'] }} &middot; <kbd>J</kbd> <kbd>K</kbd> next / back</span>
                                </div>
                            @else
                                <p class="sx-small ex-pos">You cannot review your own expense.</p>
                            @endif
                        @else
                            <div class="ex-reviewed">
                                <p><b>{{ $e->status === 'approved' ? 'Approved' : 'Rejected' }}</b> by {{ $e->reviewer?->name ?? 'a manager' }}, {{ $when($e->reviewed_at) }}</p>
                                @if ($e->status === 'rejected' && $e->reject_reason)<p class="sx-small">Reason: {{ $e->reject_reason }}</p>@endif
                            </div>
                        @endif
                    </div>

                    <div class="sx-card ex-context" aria-label="{{ $e->user->name }} in {{ $win['label'] }}">
                        <p class="sx-eyebrow">{{ $e->user->name }} &middot; {{ $win['label'] }}</p>
                        <p class="sx-small">Spent <b>{{ $short($detail['context']['spend_paise']) }}</b> &middot; {{ $detail['context']['approved'] }} approved &middot; {{ $detail['context']['waiting'] }} waiting ({{ $short($detail['context']['waiting_paise']) }}) &middot; {{ $detail['context']['rejected'] }} rejected</p>
                        @if ($detail['profile_url'])<a href="{{ $detail['profile_url'] }}" class="sx-link">Open {{ $e->user->name }}'s profile</a>@endif
                    </div>
                @endif
            </aside>
        </div>
    </div>
</x-filament-panels::page>
