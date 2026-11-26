<x-filament-panels::page>
    <div class="sx-page">
        @if ($meetings !== null)
            <div class="sx-card" style="padding:1rem 1.125rem">
                <div style="display:flex;justify-content:space-between;align-items:baseline;gap:0.5rem;flex-wrap:wrap">
                    <h2 class="sx-eyebrow">Visits today</h2>
                    <a class="sx-link" href="{{ $meetingsUrl }}">All meetings</a>
                </div>
                @forelse ($meetings as $m)
                    <a href="{{ $meetingsUrl }}" class="ca-row" style="margin-top:0.25rem">
                        <span class="ca-row-text">
                            <span class="ca-row-name">{{ $m->client_name }}</span>
                            <span class="ca-row-sub">{{ $m->purpose ?: ($m->prospect_uuid ? 'Prospect' : 'Visit') }}</span>
                        </span>
                        <span class="mv-chip {{ $m->meeting_at->lt(now()->subHour()) ? 'mv-missed' : '' }}">{{ $m->meeting_at->timezone($tz)->format('h:i A') }}</span>
                    </a>
                @empty
                    <p class="ca-empty">No visit planned for today.</p>
                @endforelse
            </div>
        @endif

        @if ($tasks !== null && $tasks->isNotEmpty())
            <div class="sx-card" style="padding:1rem 1.125rem">
                <h2 class="sx-eyebrow">Tasks from your manager</h2>
                @foreach ($tasks as $t)
                    <a href="{{ $taskUrl($t) }}" class="ca-row" style="margin-top:0.25rem">
                        <span class="ca-row-text">
                            <span class="ca-row-name">{{ $t->title }}</span>
                            <span class="ca-row-sub">{{ \App\Sales\Models\SalesTask::STATUSES[$t->status] ?? $t->status }}</span>
                        </span>
                        <span class="mv-chip {{ $t->isOverdue() ? 'mv-missed' : '' }}">{{ $t->due_at ? 'Due ' . $t->due_at->timezone($tz)->format('j M') : 'No date' }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($todos !== null && $todos->isNotEmpty())
            <div class="sx-card" style="padding:1rem 1.125rem">
                <div style="display:flex;justify-content:space-between;align-items:baseline;gap:0.5rem;flex-wrap:wrap">
                    <h2 class="sx-eyebrow">To-do due</h2>
                    <a class="sx-link" href="{{ $todoUrl }}">My to-do list</a>
                </div>
                @foreach ($todos as $todo)
                    <a href="{{ $todoUrl }}" class="ca-row" style="margin-top:0.25rem">
                        <span class="ca-row-text"><span class="ca-row-name">{{ $todo->title }}</span></span>
                        <span class="mv-chip {{ $todo->due_date->lt(today()) ? 'mv-missed' : '' }}">{{ $todo->due_date->format('j M') }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($desk !== null)
        <div class="rp-kpis" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:0.75rem">
            @foreach ([
                ['Open enquiries', $desk['open'], ''],
                ['Late follow-ups', $desk['overdue'], $desk['overdue'] > 0 ? 'late' : ''],
                ['Due today', $desk['today'], ''],
                ['Idle 3+ days', $desk['idle'], ''],
                ['In play', $money($desk['paise']), ''],
            ] as [$label, $value, $tone])
                <div class="sx-card" style="padding:0.875rem 1rem">
                    <p class="sx-eyebrow">{{ $label }}</p>
                    <p class="rp-big {{ $tone === 'late' ? 'mv-late' : '' }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="sx-card" style="padding:1rem 1.125rem">
            <div style="display:flex;justify-content:space-between;align-items:baseline;gap:0.5rem;flex-wrap:wrap">
                <h2 class="sx-eyebrow">Needs your action</h2>
                <a class="sx-link" href="{{ $listUrl }}">All enquiries</a>
            </div>
            @forelse ($desk['needs'] as $row)
                @php $e = $row['e']; $u = $row['u']; @endphp
                <a href="{{ $edit($e) }}" class="ca-row" style="margin-top:0.25rem">
                    <span class="ca-row-text">
                        <span class="ca-row-name">{{ $e->client_name }}</span>
                        <span class="ca-row-sub">{{ $e->statusLabel() }} · {{ $e->itemsSummary(2) }}</span>
                    </span>
                    <span class="mv-chip {{ $u['key'] === 'overdue' ? 'mv-missed' : '' }}">{{ $u['label'] }}</span>
                </a>
            @empty
                <p class="ca-empty">Nothing is waiting. Every follow-up is on time.</p>
            @endforelse
        </div>

        <div class="sx-card" style="padding:1rem 1.125rem">
            <h2 class="sx-eyebrow">Coming up in the next 7 days</h2>
            @forelse ($desk['upcoming'] as $row)
                @php $e = $row['e']; @endphp
                <a href="{{ $edit($e) }}" class="ca-row" style="margin-top:0.25rem">
                    <span class="ca-row-text">
                        <span class="ca-row-name">{{ $e->client_name }}</span>
                        <span class="ca-row-sub">{{ $e->statusLabel() }} · {{ $e->itemsSummary(2) }}</span>
                    </span>
                    <span class="mv-chip">{{ \Carbon\CarbonImmutable::instance($e->next_followup_at)->timezone($tz)->format('D j M, h:i A') }}</span>
                </a>
            @empty
                <p class="ca-empty">No follow-up is planned for the coming week.</p>
            @endforelse
        </div>

        @endif

        <p class="sx-small sx-mute">This is the same data as on your phone. What you change here reaches your phone the next time it syncs, and what you wrote on the phone is here after its sync.</p>
    </div>
</x-filament-panels::page>
