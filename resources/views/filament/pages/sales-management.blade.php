@php
    use App\Sales\Filament\Resources\SalesCalls\SalesCallResource;
    use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
    use App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource;
    use App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource;
    use App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource;
    use App\Sales\Filament\Resources\SalesReports\SalesReportResource;
    use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
    use App\Sales\Models\SalesEnquiry;
    use Illuminate\Support\Str;

    $tz = config('sales.timezone');
    $statusNames = SalesEnquiry::STATUSES;
    $categoryNames = \App\Sales\Models\SalesExpense::CATEGORY_LABELS;

    // The view only formats: every figure comes from SalesInsights. A link exists only when the manager may open that screen.
    $to = fn (string $area, string $resource, array $params = [], string $page = 'index') => $can($area) ? $resource::getUrl($page, $params) : null;
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    $dayShort = fn ($time) => $time ? \Carbon\CarbonImmutable::instance($time)->timezone($tz)->format('j M') : '';
    $people = fn (int $n) => $n . ' ' . ($n === 1 ? 'person' : 'people');

    $s = $summary;
    $reportsMissing = $withoutReport->map(fn ($p) => $p->user->name);

    // the eight small cards: label, value, sub-line parts [text, late?], icon, link
    $cards = [
        ['Waiting for approval', $s['pending_count'], $s['pending_count'] > 0, [[$s['pending_count'] ? $money($s['pending_paise']) . ' to review' : 'All caught up', false]], 'heroicon-o-clock', $to('sales-expenses', SalesExpenseResource::class, ['tableFilters' => ['status' => ['value' => 'pending']]])],
        ['Meetings', $s['meetings'], false, [[$s['meetings_today'] . ' today', false], [$s['followups_due'] . ' follow-ups due', false]], 'heroicon-o-calendar-days', $to('sales-meetings', SalesMeetingResource::class)],
        ['Open enquiries', $s['enquiries_open'], false, [[$s['enquiries_overdue'] . ' overdue', $s['enquiries_overdue'] > 0], [$s['enquiries_due_today'] . ' due today', false]], 'heroicon-o-clipboard-document-list', $to('sales-enquiries', SalesEnquiryResource::class)],
        ["Today's reports", $s['reports_today'] . ' of ' . $s['reps_active'], false, [[$reportsMissing->isNotEmpty() ? 'Waiting: ' . $reportsMissing->take(3)->implode(', ') . ($reportsMissing->count() > 3 ? ' +' . ($reportsMissing->count() - 3) : '') : 'Everybody has reported', $reportsMissing->isNotEmpty()]], 'heroicon-o-document-text', $to('sales-reports', SalesReportResource::class)],
        ['Client calls', $s['calls'], false, [[$s['calls_out'] . ' out', false], [$s['calls_in'] . ' in', false], [$s['calls_missed'] . ' missed', $s['calls_missed'] > 0], [$talk($s['talk_seconds']) . ' talk', false]], 'heroicon-o-phone', $to('sales-calls', SalesCallResource::class)],
        ['Client pool', $s['clients'], false, [[$people($s['people']), false], [$s['people_alone'] . ' without a company', false], [$s['clients_called'] . ' called', false]], 'heroicon-o-building-office', $to('sales-clients', SalesClientResource::class)],
        ['Field reps', $s['reps_active'], false, [[$s['reps_not_synced_today'] . ' not synced today', $s['reps_not_synced_today'] > 0], [$s['scripts'] . ' own scripts from ' . $s['scripts_reps'] . ' reps', false]], 'heroicon-o-user-group', $to('sales-reps', SalesRepResource::class)],
        ['Orders won', $s['enquiries_won'], false, [[$s['enquiries_lost'] . ' lost', false], ['last ' . $days . ' days', false]], 'heroicon-o-trophy', $to('sales-enquiries', SalesEnquiryResource::class, ['tableFilters' => ['status' => ['values' => ['won']]]])],
    ];

    $tabLabels = ['approvals' => 'Approvals', 'enquiries' => 'Overdue enquiries', 'followups' => 'Follow-ups', 'reach' => 'Not reached'];
    $tabCounts = ['approvals' => $s['pending_count'], 'enquiries' => $s['enquiries_overdue'], 'followups' => $s['followups_due'], 'reach' => $neglectedTotal];
    $stageMax = max(1, ...array_values($enquiryStatuses));
    $lastSync = $latestSync?->timezone($tz);
@endphp

<x-filament-panels::page>
    <div class="sx-page">

        {{-- 1. header --}}
        <header class="sx-head">
            <div>
                <p class="sx-eyebrow">Sales team</p>
                <h1 class="sx-h1">Sales management</h1>
                <p class="sx-sub">
                    {{ now($tz)->format('l, j F Y') }} &middot; {{ $s['reps_active'] }} active {{ $s['reps_active'] === 1 ? 'rep' : 'reps' }}
                    &middot; {{ $lastSync ? 'last sync ' . $lastSync->diffForHumans() : 'nobody has synced yet' }}
                </p>
            </div>
            <div class="sx-actions">
                @foreach ($ranges as $r)
                    <button type="button" class="sx-btn" aria-pressed="{{ $r === $days ? 'true' : 'false' }}" wire:click="setRange({{ $r }})">Last {{ $r }} days</button>
                @endforeach
                @if ($can('sales-expenses'))
                    <button type="button" class="sx-btn" wire:click="exportReport" wire:loading.attr="disabled" wire:target="exportReport">
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4" />
                        Export report
                    </button>
                @endif
            </div>
        </header>

        {{-- 2. the figures --}}
        <section class="sx-kpis" aria-label="Key figures">
            <div class="sx-hero">
                <svg class="sx-orbit" viewBox="0 0 100 100" aria-hidden="true">
                    <ellipse cx="50" cy="50" rx="44" ry="16" transform="rotate(35 50 50)" fill="none" stroke="currentColor" stroke-width="1" />
                    <ellipse cx="50" cy="50" rx="44" ry="16" transform="rotate(-35 50 50)" fill="none" stroke="currentColor" stroke-width="1" />
                    <circle cx="50" cy="50" r="3" fill="currentColor" />
                </svg>
                <p class="sx-eyebrow">Spent, last {{ $days }} days</p>
                <p class="sx-hero-amount">{{ $money($s['spend_paise']) }}</p>
                <p class="sx-hero-sub">{{ $s['expense_count'] }} {{ $s['expense_count'] === 1 ? 'expense' : 'expenses' }} &middot; {{ $money($s['approved_paise']) }} approved &middot; {{ $money($s['pending_paise']) }} waiting</p>

                @if ($s['spend_paise'] === 0)
                    <p class="sx-hero-sub" style="margin-top:auto;padding-top:1.5rem">No expenses in the last {{ $days }} days.</p>
                @else
                    <div class="sx-bars {{ $days > 30 ? 'dense' : '' }}" role="img" aria-label="Spending per day, last {{ $days }} days">
                        @foreach ($daily as $d)
                            <div class="sx-bar {{ $d['date'] === now($tz)->toDateString() ? 'today' : '' }} {{ $d['paise'] ? '' : 'empty' }}"
                                 title="{{ $d['label'] }}: {{ $money($d['paise']) }}"
                                 style="height: {{ max(2, round(100 * $d['paise'] / $dailyMax)) }}%"></div>
                        @endforeach
                    </div>
                    <div class="sx-axis"><span>{{ $daily[0]['label'] }}</span><span>{{ end($daily)['label'] }} (today)</span></div>
                @endif
            </div>

            @foreach ($cards as [$label, $value, $valueLate, $parts, $icon, $url])
                @if ($url)
                    <a href="{{ $url }}" class="sx-card sx-stat">
                @else
                    <div class="sx-card sx-stat">
                @endif
                    <span class="sx-icon"><x-filament::icon :icon="$icon" class="h-4 w-4" /></span>
                    <p class="sx-eyebrow">{{ $label }}</p>
                    <p class="sx-stat-value {{ $valueLate ? 'sx-late' : '' }}">{{ $value }}</p>
                    <p class="sx-stat-sub">
                        @foreach ($parts as [$text, $late])
                            @if (! $loop->first) &middot; @endif<span class="{{ $late ? 'sx-late' : '' }}">{{ $text }}</span>
                        @endforeach
                    </p>
                @if ($url)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </section>

        {{-- 3. the reps, and the selected rep --}}
        <section class="sx-split">
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
                                    <th scope="col">Spent</th>
                                    <th scope="col">Meetings</th>
                                    <th scope="col">Calls</th>
                                    <th scope="col">Enquiries</th>
                                    <th scope="col">Won</th>
                                    <th scope="col">Pending</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reps as $rep)
                                    <tr class="sx-row" wire:key="rep-{{ $rep['id'] }}" tabindex="0" aria-selected="{{ $rep['id'] === $selectedId ? 'true' : 'false' }}"
                                        wire:click="selectRep({{ $rep['id'] }})" wire:keydown.enter="selectRep({{ $rep['id'] }})" wire:keydown.space.prevent="selectRep({{ $rep['id'] }})">
                                        <td class="sx-rep-cell">
                                            <div class="sx-rep">
                                                <span class="sx-avatar">{{ $initials($rep['name']) }}</span>
                                                <div class="sx-rep-text">
                                                    <p class="sx-trunc"><span class="sx-name">{{ $rep['name'] }}</span><span class="sx-code">{{ $rep['code'] }}</span></p>
                                                    <p class="sx-small sx-trunc">
                                                        <span class="sx-dot {{ $rep['stale'] ? 'late' : '' }}" aria-hidden="true"></span>{{ $rep['last_sync_at'] ? ($rep['stale'] ? 'Not synced today' : 'Synced ' . $rep['last_sync_at']->diffForHumans()) : 'Never synced' }}
                                                        &middot; {{ $rep['report_today'] ? 'Report in' : 'No report' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $money($rep['spend_paise']) }}</td>
                                        <td>{{ $rep['meetings'] }}</td>
                                        <td>{{ $rep['calls'] }}@if ($rep['talk_seconds'])<br><span class="sx-small">{{ $talk($rep['talk_seconds']) }}</span>@endif</td>
                                        <td>{{ $rep['enquiries_open'] }}@if ($rep['enquiries_late'])<br><span class="sx-small sx-late">{{ $rep['enquiries_late'] }} late</span>@endif</td>
                                        <td>{{ $rep['won'] }}</td>
                                        <td><span class="sx-chip {{ $rep['pending'] ? 'late' : '' }}">{{ $rep['pending'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- the selected rep --}}
            <aside class="sx-card" aria-label="Selected rep">
                @if ($detail)
                    @php $r = $detail['rep']; @endphp
                    <div class="sx-rep">
                        <span class="sx-avatar" style="width:2.75rem;height:2.75rem">{{ $initials($r['name']) }}</span>
                        <div class="sx-rep-text" style="flex:1">
                            <p class="sx-name sx-trunc">{{ $r['name'] }}</p>
                            <p class="sx-small sx-trunc">{{ $r['code'] }}@if ($detail['phone']) &middot; {{ $detail['phone'] }}@endif</p>
                        </div>
                        <span class="sx-chip {{ $detail['stale'] ? 'late' : 'ok' }}">{{ $detail['last_sync_at'] ? ($detail['stale'] ? 'Not synced' : 'Synced') : 'Never synced' }}</span>
                    </div>
                    <p class="sx-small" style="margin-top:.5rem">
                        {{ $detail['device_model'] ?: 'Phone not known' }}@if ($detail['app_version']) &middot; app {{ $detail['app_version'] }}@endif
                        @if ($detail['last_sync_at']) &middot; {{ $detail['last_sync_at']->diffForHumans() }}@endif
                    </p>

                    <div class="sx-figures">
                        <div class="sx-figure"><span class="sx-eyebrow">Spent</span><b>{{ $money($r['spend_paise']) }}</b></div>
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
                            <a class="sx-btn" href="{{ $to('sales-expenses', SalesExpenseResource::class, ['tableFilters' => ['user_id' => ['value' => $r['user_id']]]]) }}">Expenses</a>
                        @endif
                    </div>
                @else
                    <p class="sx-empty">Pick a rep in the table to see their day.</p>
                @endif
            </aside>
        </section>

        {{-- 4. what needs the manager --}}
        <section class="sx-bottom">
            <div class="sx-card wide">
                <p class="sx-eyebrow" style="margin-bottom:.625rem">Needs your action</p>
                <div class="sx-tabs" role="group" aria-label="What needs your action">
                    @foreach ($tabLabels as $key => $label)
                        <button type="button" class="sx-btn" aria-pressed="{{ $tab === $key ? 'true' : 'false' }}" wire:click="setTab('{{ $key }}')">
                            {{ $label }} <span class="sx-tab-count {{ $key !== 'followups' && $key !== 'reach' && $tabCounts[$key] > 0 ? 'late' : '' }}">{{ $tabCounts[$key] }}</span>
                        </button>
                    @endforeach
                </div>

                @php
                    $rows = match ($tab) {
                        'enquiries' => $tabs['enquiries']->map(fn ($e) => [
                            'title' => $e->client_name, 'sub' => $e->user->name . ' · ' . $e->itemsSummary(2) . ' · ' . $e->statusLabel(),
                            'chip' => $dayShort($e->next_followup_at), 'late' => true, 'who' => $e->user->name,
                            'url' => $to('sales-enquiries', SalesEnquiryResource::class, ['tableFilters' => ['overdue' => ['isActive' => true]]]),
                        ]),
                        'followups' => $tabs['followups']->map(fn ($m) => [
                            'title' => $m->client_name, 'sub' => $m->user->name . ($m->purpose ? ' · ' . Str::limit($m->purpose, 40) : ''),
                            'chip' => $dayShort($m->next_followup_at), 'late' => $m->next_followup_at->isPast(), 'who' => $m->user->name,
                            'url' => $to('sales-meetings', SalesMeetingResource::class),
                        ]),
                        'reach' => $tabs['reach']->map(fn ($c) => [
                            'title' => $c->name, 'sub' => collect([$c->city, $c->assignee?->name ?? 'Not given to a rep'])->filter()->implode(' · '),
                            'chip' => $c->days_since_contact === null ? 'Never reached' : $c->days_since_contact . ' days', 'late' => false, 'who' => $c->name,
                            'url' => $to('sales-clients', SalesClientResource::class, ['record' => $c->getKey()], 'edit'),
                        ]),
                        default => $tabs['approvals']->map(fn ($e) => [
                            'title' => $e->user->name . ' · ' . ($categoryNames[$e->category] ?? ucfirst($e->category)),
                            'sub' => $e->expense_date->format('d M Y') . ($e->note ? ' · ' . Str::limit($e->note, 40) : ''),
                            'chip' => $money($e->amount_paise), 'late' => false, 'who' => $e->user->name,
                            'url' => $to('sales-expenses', SalesExpenseResource::class, ['tableFilters' => ['status' => ['value' => 'pending']]]),
                        ]),
                    };
                @endphp

                @forelse ($rows as $row)
                    @if ($row['url'])
                        <a href="{{ $row['url'] }}" class="sx-item {{ $loop->first ? 'first' : '' }}">
                    @else
                        <div class="sx-item {{ $loop->first ? 'first' : '' }}">
                    @endif
                        <span class="sx-avatar">{{ $initials($row['who']) }}</span>
                        <div class="sx-item-text">
                            <p class="sx-trunc" style="font-weight:600;font-size:.875rem">{{ $row['title'] }}</p>
                            <p class="sx-small sx-trunc">{{ $row['sub'] }}</p>
                        </div>
                        <span class="sx-chip {{ $row['late'] ? 'late' : '' }}">{{ $row['chip'] }}</span>
                        @if ($row['url'])<x-filament::icon icon="heroicon-o-chevron-right" class="sx-chev" />@endif
                    @if ($row['url'])
                        </a>
                    @else
                        </div>
                    @endif
                @empty
                    <p class="sx-empty sx-ok">
                        {{ ['approvals' => 'Nothing is waiting. All caught up.', 'enquiries' => 'No follow-up is overdue.', 'followups' => 'No follow-ups due.', 'reach' => 'Every client was reached in the last 30 days.'][$tab] }}
                    </p>
                @endforelse
            </div>

            <div class="sx-card">
                <div class="sx-card-head" style="margin-bottom:.25rem">
                    <p class="sx-eyebrow">Enquiries by stage</p>
                    @if ($to('sales-enquiries', SalesEnquiryResource::class))<a class="sx-link" href="{{ $to('sales-enquiries', SalesEnquiryResource::class) }}">Open</a>@endif
                </div>
                <p class="sx-small">{{ $s['enquiries_new'] }} new &middot; {{ $s['enquiries_won'] }} orders &middot; {{ $s['enquiries_lost'] }} lost &middot; {{ $s['enquiries_idle'] }} untouched for 3 days</p>
                @foreach ($enquiryStatuses as $key => $n)
                    <div style="margin-top:.75rem">
                        <div class="sx-line" style="margin-top:0"><span style="font-weight:500">{{ $statusNames[$key] }}</span><span class="sx-mute">{{ $n }}</span></div>
                        <div class="sx-track"><div class="sx-fill" style="width: {{ round(100 * $n / $stageMax) }}%"></div></div>
                    </div>
                @endforeach
            </div>

            <div class="sx-card">
                <p class="sx-eyebrow" style="margin-bottom:.25rem">Spend by category</p>
                @forelse ($categories as $c)
                    <div style="margin-top:.75rem">
                        <div class="sx-line" style="margin-top:0"><span style="font-weight:500">{{ $categoryNames[$c['category']] ?? ucfirst($c['category']) }}</span><span class="sx-mute">{{ $money($c['paise']) }} &middot; {{ round(100 * $c['paise'] / $categoryTotal) }}%</span></div>
                        <div class="sx-track"><div class="sx-fill" style="width: {{ round(100 * $c['paise'] / $categoryTotal) }}%"></div></div>
                    </div>
                @empty
                    <p class="sx-empty">Nothing to show yet.</p>
                @endforelse
            </div>

            @php
                $splits = [
                    'Local or outstation' => [$expenseSplit['work_area'], ['local' => 'Local (in Jaipur)', 'outstation' => 'Outstation', 'unknown' => 'Not told (older app)']],
                    'With or without bill' => [$expenseSplit['bill'], ['with_bill' => 'With bill', 'without_bill' => 'Without bill', 'unknown' => 'Not told (older app)']],
                ];
            @endphp
            <div class="sx-card">
                @foreach ($splits as $title => [$figures, $names])
                    @php($splitTotal = max(1, array_sum($figures)))
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
        </section>
    </div>
</x-filament-panels::page>
