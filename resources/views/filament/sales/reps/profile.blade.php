@php
    use App\Sales\Filament\Resources\SalesCalls\SalesCallResource;
    use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
    use App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource;
    use App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource;
    use App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource;
    use App\Sales\Filament\Resources\SalesReports\SalesReportResource;
    use App\Sales\Models\SalesExpense;

    $tz = config('sales.timezone');
    $initials = collect(preg_split('/\s+/', trim($p['name'])))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $byRep = fn (string $area, string $resource, string $key = 'user_id') => $can($area) ? $resource::getUrl('index', ['tableFilters' => [$key => ['value' => $userId]]]) : null;
    $meetingsUrl = $byRep('sales-meetings', SalesMeetingResource::class);
    $enquiriesUrl = $byRep('sales-enquiries', SalesEnquiryResource::class);
    $expensesUrl = $can('sales-expenses') ? SalesExpenseResource::getUrl('index', ['tab' => 'all', 'rep' => $userId]) : null;
    $callsUrl = $byRep('sales-calls', SalesCallResource::class);
    $reportsUrl = $byRep('sales-reports', SalesReportResource::class);
    $clientsUrl = $byRep('sales-clients', SalesClientResource::class, 'assigned_to');
    $categoryNames = SalesExpense::CATEGORY_LABELS;
    $lastSync = $p['last_sync_at']?->timezone($tz);

    // the 14-day chart: each series has its own scale (the biggest day fills the height), and each is drawn in its own colour AND with its own shape (bar vs dot)
    $meetMax = max(1, ...($p['chart']['meetings'] ?? [0]));
    $callMax = max(1, ...($p['chart']['calls'] ?? [0]));
    $delta = function (array $k, string $period = 'the 30 days before') {
        if (($k['delta'] ?? null) === 'new') {
            return ['new', null, ''];
        }
        if (($k['delta'] ?? null) === null) {
            return [null, null, ''];
        }

        return [($k['dir'] === 'up' ? '+' : '') . $k['delta'] . '%', $k['tone'], ($k['dir'] === 'up' ? 'more' : ($k['dir'] === 'down' ? 'less' : 'the same')) . ' than ' . $period];
    };
@endphp

<x-filament-panels::page>
    <div class="sx-page rp-page">

        {{-- breadcrumb + header --}}
        <nav class="rp-crumb" aria-label="Breadcrumb"><a href="{{ $listUrl }}">Reps</a> <span aria-hidden="true">&rsaquo;</span> <span>{{ $p['name'] }}</span></nav>

        <header class="rp-profile-head">
            <span class="sx-avatar rp-avatar" aria-hidden="true">{{ $initials }}</span>
            <div class="rp-profile-id">
                <h1 class="sx-h1">{{ $p['name'] }}</h1>
                <p class="sx-sub"><span class="sx-dot {{ $p['state'] }}" aria-hidden="true"></span>{{ $p['status_line'] }}@php $reason = collect($p['attention'])->first(fn ($text) => ! str_contains(mb_strtolower($p['status_line']), mb_strtolower($text))); @endphp @if ($reason)<span class="sx-late">&middot; {{ $reason }}</span>@endif</p>
                <p class="sx-small rp-mono">
                    {{ collect([$p['code'], $phoneText ?: null, $p['device_model'], $p['app_version'] ? 'app ' . $p['app_version'] : null, $lastSync ? 'synced ' . $lastSync->diffForHumans() : 'never synced'])->filter()->implode(' · ') }}
                </p>
            </div>
            <div class="sx-actions">
                @if ($p['phone'])<a class="sx-btn" href="tel:{{ $p['phone'] }}">Call</a>@endif
                <button type="button" class="sx-btn" wire:click="mountAction('resetPassword')">Reset password</button>
                <a class="sx-btn sx-btn-primary" href="{{ $editUrl }}">Edit details</a>
            </div>
        </header>

        {{-- tabs --}}
        <div class="rp-tabs" role="tablist" aria-label="{{ $p['name'] }}">
            @foreach ($tabs as $key => $label)
                <button type="button" role="tab" class="sx-btn" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}" aria-pressed="{{ $activeTab === $key ? 'true' : 'false' }}" wire:click="setTab('{{ $key }}')">
                    {{ $label }}@if (($badges[$key] ?? null) !== null && $badges[$key] > 0) <span class="sx-tab-count {{ $key === 'expenses' ? 'late' : '' }}">{{ $badges[$key] }}</span>@endif
                </button>
            @endforeach
        </div>

        {{-- ===================== Overview ===================== --}}
        @if ($activeTab === 'overview')
            @php $kpis = $p['kpis']; @endphp

            @if ($kpis)
                <section class="rp-kpis" aria-label="Last 30 days, compared with the 30 days before">
                    @isset($kpis['spent'])
                        @php [$d, $tone, $words] = $delta($kpis['spent']); @endphp
                        <a href="{{ $expensesUrl ?? '#' }}" class="sx-card sx-kpi">
                            <p class="sx-eyebrow">Spent</p>
                            <p class="sx-kpi-value">{{ $money($kpis['spent']['paise']) }}</p>
                            @if ($d)<span class="sx-delta {{ $tone === 'none' ? '' : $tone }}">{{ $d }}<span class="sx-sr"> {{ $words }}</span></span>@endif
                            <p class="sx-stat-sub">{{ $money((int) $p['month_spend_paise']) }} in {{ $p['month_label'] }}</p>
                        </a>
                    @endisset
                    @isset($kpis['meetings'])
                        @php [$d, $tone, $words] = $delta($kpis['meetings']); @endphp
                        <a href="{{ $meetingsUrl ?? '#' }}" class="sx-card sx-kpi">
                            <p class="sx-eyebrow">Meetings done</p>
                            <p class="sx-kpi-value">{{ $kpis['meetings']['value'] }}</p>
                            @if ($d)<span class="sx-delta {{ $tone === 'none' ? '' : $tone }}">{{ $d }}<span class="sx-sr"> {{ $words }}</span></span>@endif
                            @if ($kpis['meetings']['best'])<p class="sx-stat-sub">best in the team</p>@endif
                        </a>
                    @endisset
                    @isset($kpis['calls'])
                        @php [$d, $tone, $words] = $delta($kpis['calls']); @endphp
                        <a href="{{ $callsUrl ?? '#' }}" class="sx-card sx-kpi">
                            <p class="sx-eyebrow">Client calls</p>
                            <p class="sx-kpi-value">{{ $kpis['calls']['value'] }}</p>
                            @if ($d)<span class="sx-delta {{ $tone === 'none' ? '' : $tone }}">{{ $d }}<span class="sx-sr"> {{ $words }}</span></span>@endif
                            <p class="sx-stat-sub">{{ $talk($kpis['calls']['talk_seconds']) }} talk</p>
                        </a>
                    @endisset
                    @isset($kpis['won'])
                        @php [$d, $tone, $words] = $delta($kpis['won']); @endphp
                        <a href="{{ $enquiriesUrl ?? '#' }}" class="sx-card sx-kpi">
                            <p class="sx-eyebrow">Orders won</p>
                            <p class="sx-kpi-value">{{ $kpis['won']['value'] }}</p>
                            @if ($d)<span class="sx-delta {{ $tone === 'none' ? '' : $tone }}">{{ $d }}<span class="sx-sr"> {{ $words }}</span></span>@endif
                            <p class="sx-stat-sub">{{ $kpis['won']['open'] }} open &middot; <span class="{{ $kpis['won']['late'] ? 'sx-late' : '' }}">{{ $kpis['won']['late'] }} late</span></p>
                        </a>
                    @endisset
                </section>
            @endif

            <div class="rp-grid">
                {{-- Today --}}
                @if ($p['visits'] !== null)
                    <section class="sx-card rp-wide" aria-label="Today">
                        <div class="sx-card-head">
                            <p class="sx-eyebrow">Today{{ $p['route_title'] ? ' · ' . $p['route_title'] : '' }}</p>
                            @php $done = collect($p['visits'])->where('state', 'done')->count(); @endphp
                            @if ($p['visits'])<span class="sx-small">{{ $done }} of {{ count($p['visits']) }} done</span>@endif
                        </div>
                        @forelse ($p['visits'] as $v)
                            <div class="sx-item {{ $loop->first ? 'first' : '' }}">
                                <span class="rp-time rp-mono">{{ $v['time'] }}</span>
                                <div class="sx-grow">
                                    <p class="sx-trunc" style="font-weight:600;font-size:.875rem">
                                        @if ($meetingsUrl)<a href="{{ $meetingsUrl }}" class="sx-name">{{ $v['client'] }}</a>@else{{ $v['client'] }}@endif
                                    </p>
                                    @if ($v['purpose'])<p class="sx-small sx-trunc">{{ $v['purpose'] }}</p>@endif
                                </div>
                                <span class="sx-chip {{ $v['state'] === 'done' ? 'ok' : ($v['state'] === 'missed' || $v['late'] ? 'late' : '') }}">
                                    {{ match (true) { $v['state'] === 'done' => 'Done', $v['state'] === 'missed' => 'Missed', $v['late'] => 'Not logged', $v['state'] === 'next' => 'Next' . ($v['in'] ? ' · ' . $v['in'] : ''), default => 'Planned' } }}
                                </span>
                            </div>
                        @empty
                            <p class="sx-empty"><span class="sx-chip late">No meetings planned today</span></p>
                        @endforelse
                    </section>
                @endif

                {{-- Daily report --}}
                @if ($p['report'])
                    <section class="sx-card" aria-label="Daily report">
                        <p class="sx-eyebrow">Daily report</p>
                        <p class="rp-big">{{ $p['report']['sent'] }} of {{ $p['report']['working'] }} working days sent @if ($p['report']['today_due']) <span class="sx-small">&middot; today not yet</span>@endif</p>
                        <ul class="rp-streak" aria-label="The last 14 days: {{ collect($p['report']['streak'])->map(fn ($s) => $s['date'] . ' ' . ['sent' => 'sent', 'missed' => 'missed', 'off' => 'not a working day', 'today' => 'not yet'][$s['state']])->implode(', ') }}">
                            @foreach ($p['report']['streak'] as $s)
                                <li class="rp-dot {{ $s['state'] }}" title="{{ $s['date'] }}"></li>
                            @endforeach
                        </ul>
                        <p class="sx-small rp-legend"><span><i class="rp-dot sent"></i> sent</span> <span><i class="rp-dot missed"></i> missed</span> <span><i class="rp-dot off"></i> not a working day</span></p>
                        @if ($reportsUrl)<a href="{{ $reportsUrl }}" class="sx-link">All reports</a>@endif
                    </section>
                @endif

                {{-- Last 14 days --}}
                @if ($p['chart']['meetings'] !== null || $p['chart']['calls'] !== null)
                    <section class="sx-card rp-wide" aria-label="Last 14 days">
                        <div class="sx-card-head">
                            <p class="sx-eyebrow">Last 14 days</p>
                            <span class="sx-small rp-legend">
                                @if ($p['chart']['meetings'] !== null)<span><i class="rp-key meet"></i> Meetings done</span>@endif
                                @if ($p['chart']['calls'] !== null)<span><i class="rp-key call"></i> Calls</span>@endif
                            </span>
                        </div>
                        <div class="rp-chart" aria-hidden="true">
                            @foreach ($p['chart']['dates'] as $i => $date)
                                <div class="rp-col" title="{{ $p['chart']['labels'][$i] }}">
                                    <div class="rp-stack">
                                        @if ($p['chart']['calls'] !== null)<span class="rp-bar call" style="height: {{ round(100 * $p['chart']['calls'][$i] / $callMax) }}%"></span>@endif
                                        @if ($p['chart']['meetings'] !== null)<span class="rp-bar meet" style="height: {{ round(100 * $p['chart']['meetings'][$i] / $meetMax) }}%"></span>@endif
                                    </div>
                                    <span class="rp-day">{{ $i % 2 === 0 ? \Carbon\CarbonImmutable::parse($date)->format('j') : '' }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="sx-sr">
                            @foreach ($p['chart']['dates'] as $i => $date)
                                {{ $p['chart']['labels'][$i] }}:@if ($p['chart']['meetings'] !== null) {{ $p['chart']['meetings'][$i] }} meetings done,@endif @if ($p['chart']['calls'] !== null) {{ $p['chart']['calls'][$i] }} calls.@endif
                            @endforeach
                        </p>
                    </section>
                @endif

                {{-- Open enquiries --}}
                @if ($p['enquiries'] !== null)
                    <section class="sx-card" aria-label="Open enquiries">
                        <div class="sx-card-head">
                            <p class="sx-eyebrow">Open enquiries</p>
                            @if ($enquiriesUrl)<a href="{{ $enquiriesUrl }}" class="sx-link">All {{ $p['enquiries']['total'] }}</a>@endif
                        </div>
                        @forelse ($p['enquiries']['top'] as $e)
                            <div class="sx-item {{ $loop->first ? 'first' : '' }}">
                                <div class="sx-grow">
                                    <p class="sx-trunc" style="font-weight:600;font-size:.875rem">{{ $e['client'] }}</p>
                                    <p class="sx-small sx-trunc">{{ $e['summary'] }} &middot; {{ $e['status'] }}</p>
                                </div>
                                @if ($e['late'])<span class="sx-chip late">{{ $e['late_days'] > 0 ? $e['late_days'] . ' d late' : 'late today' }}</span>@endif
                            </div>
                        @empty
                            <p class="sx-empty">No open enquiries.</p>
                        @endforelse
                    </section>
                @endif

                {{-- Expenses waiting --}}
                @if ($p['pending'] !== null && $can('sales-expenses'))
                    <section class="sx-card" aria-label="Expenses waiting">
                        <div class="sx-card-head">
                            <p class="sx-eyebrow">Expenses waiting</p>
                            <span class="sx-small">@if ($p['pending_count'] > 0){{ $p['pending_count'] }} @endif @if ($expensesUrl)<a href="{{ $expensesUrl }}" class="sx-link">Review</a>@endif</span>
                        </div>
                        @forelse ($p['pending'] as $e)
                            <div class="sx-item {{ $loop->first ? 'first' : '' }}" wire:key="pend-{{ $e->id }}">
                                <div class="sx-grow">
                                    <p class="sx-trunc" style="font-weight:600;font-size:.875rem">{{ $categoryNames[$e->category] ?? ucfirst($e->category) }} &middot; {{ $e->expense_date->format('j M') }}</p>
                                    <p class="sx-small">
                                        <span class="sx-chip {{ $e->bill_type === 'without_bill' ? 'late' : 'ok' }}">{{ $e->bill_type === 'without_bill' ? 'No bill' : ($e->hasBill() ? 'Bill attached' : 'No photo yet') }}</span>
                                    </p>
                                </div>
                                <span class="sx-chip">{{ $rupees($e->amount_paise) }}</span>
                                <button type="button" class="sx-btn sx-btn-approve" wire:click="approveExpense({{ $e->id }})" wire:loading.attr="disabled" wire:target="approveExpense({{ $e->id }})" aria-label="Approve {{ $rupees($e->amount_paise) }} of {{ $p['name'] }}">Approve</button>
                            </div>
                        @empty
                            <p class="sx-empty sx-ok">Nothing is waiting.</p>
                        @endforelse
                    </section>
                @endif

                {{-- Phone --}}
                <section class="sx-card" aria-label="Phone">
                    <p class="sx-eyebrow">Phone</p>
                    <p class="rp-big">{{ $p['device_model'] ?: 'Not reported yet' }}</p>
                    <p class="sx-small">
                        @if ($p['app_version'])
                            App {{ $p['app_version'] }} &middot; <span class="sx-chip {{ $p['outdated'] ? 'late' : 'ok' }}">{{ $p['outdated'] ? 'Update needed' : 'Up to date' }}</span>
                        @else
                            No app version yet
                        @endif
                    </p>
                    <p class="sx-small">{{ $p['tokens'] }} {{ $p['tokens'] === 1 ? 'phone' : 'phones' }} signed in &middot; {{ $lastSync ? 'last sync ' . $lastSync->diffForHumans() : 'never synced' }}</p>
                </section>
            </div>
        @endif

        {{-- ===================== Activity ===================== --}}
        @if ($activeTab === 'activity')
            <section class="sx-card" aria-label="Activity of the last 30 days">
                <div class="rp-chips" role="group" aria-label="Kind of activity">
                    <button type="button" class="sx-btn" aria-pressed="{{ $activeType === 'all' ? 'true' : 'false' }}" wire:click="setActivityType('all')">All</button>
                    @foreach ($activityTypes as $type)
                        <button type="button" class="sx-btn" aria-pressed="{{ $activeType === $type ? 'true' : 'false' }}" wire:click="setActivityType('{{ $type }}')">{{ ['meeting' => 'Meetings', 'call' => 'Calls', 'enquiry' => 'Enquiries', 'report' => 'Reports'][$type] }}</button>
                    @endforeach
                </div>
                @forelse ($activity as $row)
                    @php
                        $url = match ($row['type']) { 'meeting' => $meetingsUrl, 'call' => $callsUrl, 'enquiry' => $enquiriesUrl, 'report' => $reportsUrl };
                    @endphp
                    <div class="sx-item {{ $loop->first ? 'first' : '' }}">
                        <span class="sx-chip">{{ ['meeting' => 'Meeting', 'call' => 'Call', 'enquiry' => 'Enquiry', 'report' => 'Report'][$row['type']] }}</span>
                        <div class="sx-grow">
                            <p class="sx-trunc" style="font-weight:600;font-size:.875rem">@if ($url)<a href="{{ $url }}" class="sx-name">{{ $row['title'] }}</a>@else{{ $row['title'] }}@endif</p>
                            <p class="sx-small sx-trunc">{{ $row['text'] }}</p>
                        </div>
                        <span class="sx-small">{{ $row['at']->format('D j M, g:i a') }}</span>
                    </div>
                @empty
                    <p class="sx-empty">Nothing in the last 30 days.</p>
                @endforelse
            </section>
        @endif

        {{-- ===================== Expenses ===================== --}}
        @if ($activeTab === 'expenses')
            <section class="sx-card" aria-label="Expenses">
                <div class="sx-card-head">
                    <p class="sx-eyebrow">Expenses &middot; waiting first</p>
                    @if ($expensesUrl)<a href="{{ $expensesUrl }}" class="sx-link">Open in Expenses (to reject or filter)</a>@endif
                </div>
                @forelse ($expenses as $e)
                    <div class="sx-item {{ $loop->first ? 'first' : '' }}" wire:key="exp-{{ $e->id }}">
                        <div class="sx-grow">
                            <p class="sx-trunc" style="font-weight:600;font-size:.875rem">{{ $categoryNames[$e->category] ?? ucfirst($e->category) }} &middot; {{ $e->expense_date->format('j M Y') }}</p>
                            <p class="sx-small">{{ $e->bill_type === 'without_bill' ? 'No bill' : ($e->hasBill() ? 'Bill attached' : 'No photo yet') }} &middot; {{ ['pending' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Rejected'][$e->status] ?? $e->status }}</p>
                        </div>
                        <span class="sx-chip">{{ $rupees($e->amount_paise) }}</span>
                        @if ($e->isPending())
                            <button type="button" class="sx-btn sx-btn-approve" wire:click="approveExpense({{ $e->id }})" wire:loading.attr="disabled" wire:target="approveExpense({{ $e->id }})" aria-label="Approve {{ $rupees($e->amount_paise) }} of {{ $p['name'] }}">Approve</button>
                        @endif
                    </div>
                @empty
                    <p class="sx-empty">No expenses yet.</p>
                @endforelse
            </section>
        @endif

        {{-- ===================== Clients ===================== --}}
        @if ($activeTab === 'clients')
            <section class="sx-card" aria-label="Clients">
                <div class="sx-card-head">
                    <p class="sx-eyebrow">Clients given to {{ $p['name'] }}</p>
                    @if ($clientsUrl)<a href="{{ $clientsUrl }}" class="sx-link">Open in Clients</a>@endif
                </div>
                @forelse ($clients as $c)
                    <div class="sx-item {{ $loop->first ? 'first' : '' }}">
                        <div class="sx-grow">
                            <p class="sx-trunc" style="font-weight:600;font-size:.875rem">{{ $c['name'] }}</p>
                            <p class="sx-small sx-trunc">{{ $c['city'] ?: 'No place' }} &middot; {{ $c['last_visit'] ? 'last visit ' . $c['last_visit']->format('j M Y') : 'no visit yet' }}</p>
                        </div>
                        @if ($c['open_enquiries'])<span class="sx-chip">{{ $c['open_enquiries'] }} open {{ $c['open_enquiries'] === 1 ? 'enquiry' : 'enquiries' }}</span>@endif
                    </div>
                @empty
                    <p class="sx-empty">No client is given to this rep yet.</p>
                @endforelse
            </section>
        @endif

        {{-- ===================== Account ===================== --}}
        @if ($activeTab === 'account')
            <section class="sx-card rp-account" aria-label="Account">
                <dl class="rp-dl">
                    <div><dt class="sx-eyebrow">Name</dt><dd>{{ $p['name'] }}</dd></div>
                    <div><dt class="sx-eyebrow">E-mail</dt><dd>{{ $p['email'] ?: '—' }}</dd></div>
                    <div><dt class="sx-eyebrow">Phone</dt><dd>{{ $phoneText ?: '—' }}</dd></div>
                    <div><dt class="sx-eyebrow">Employee code</dt><dd class="rp-mono">{{ $p['code'] }}</dd><p class="sx-small">It cannot be changed.</p></div>
                    <div><dt class="sx-eyebrow">Status</dt><dd>{{ $p['active'] ? 'Active' : 'Switched off' }}</dd></div>
                </dl>
                <div class="rp-ready-actions">
                    <a class="sx-btn sx-btn-primary" href="{{ $editUrl }}">Edit details</a>
                    <button type="button" class="sx-btn" wire:click="mountAction('resetPassword')">Reset password</button>
                </div>

                <h2 class="rp-h2">{{ $p['active'] ? 'Switch off' : 'Switched off' }}</h2>
                @if ($p['active'])
                    <p class="sx-small">The phone is signed out on its next sync. Their meetings, expenses and enquiries stay.</p>
                    <button type="button" class="sx-btn rp-danger" wire:click="mountAction('switchOff')">Switch off {{ $p['name'] }}</button>
                @else
                    <p class="sx-small">The phone is signed out. Switch on to let {{ $p['name'] }} sign in again.</p>
                    <button type="button" class="sx-btn" wire:click="switchOn">Switch on</button>
                @endif

                <h2 class="rp-h2">Delete</h2>
                @if ($p['active'])
                    <p class="sx-small">To delete {{ $p['name'] }}, switch them off first.</p>
                @else
                    <p class="sx-small">Deleting removes {{ $p['name'] }} and every record they own, for good. Export their expenses first if you need them.</p>
                    <button type="button" class="sx-btn rp-danger" wire:click="mountAction('deleteRep')">Delete {{ $p['name'] }} for good</button>
                @endif
            </section>
        @endif
    </div>
</x-filament-panels::page>
