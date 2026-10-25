@php
    $card = 'rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10';
    $eyebrow = 'font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600';
    $expenses = \App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource::class;
    $meetings = \App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource::class;
    $repsResource = \App\Sales\Filament\Resources\SalesReps\SalesRepResource::class;
    $callsResource = \App\Sales\Filament\Resources\SalesCalls\SalesCallResource::class;
    $clientsResource = \App\Sales\Filament\Resources\SalesClients\SalesClientResource::class;
    $enquiriesResource = \App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource::class;
    $reportsResource = \App\Sales\Filament\Resources\SalesReports\SalesReportResource::class;
    $statusNames = \App\Sales\Models\SalesEnquiry::STATUSES;
    $categoryNames = ['travel' => 'Travel', 'food' => 'Food', 'lodging' => 'Lodging', 'fuel' => 'Fuel', 'other' => 'Other'];
@endphp

<x-filament-panels::page>
    {{-- period switch --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="{{ $eyebrow }} me-2">Show numbers for</span>
        @foreach ($ranges as $r)
            <x-filament::button size="sm" :color="$r === $days ? 'primary' : 'gray'" :outlined="$r !== $days" wire:click="setRange({{ $r }})">Last {{ $r }} days</x-filament::button>
        @endforeach
    </div>

    {{-- headline numbers --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem;">
        <div class="{{ $card }}">
            <p class="{{ $eyebrow }}">Spent, last {{ $days }} days</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $money($summary['spend_paise']) }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $summary['expense_count'] }} expenses &middot; {{ $money($summary['approved_paise']) }} approved</p>
        </div>
        <a href="{{ $expenses::getUrl() }}" class="{{ $card }} block transition hover:ring-primary-500/40">
            <p class="{{ $eyebrow }}">Waiting for approval</p>
            <p class="mt-2 text-2xl font-semibold {{ $summary['pending_count'] ? 'text-danger-600' : 'text-success-600' }}">{{ $summary['pending_count'] }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $summary['pending_count'] ? $money($summary['pending_paise']) . ' to review' : 'All caught up' }}</p>
        </a>
        <a href="{{ $meetings::getUrl() }}" class="{{ $card }} block transition hover:ring-primary-500/40">
            <p class="{{ $eyebrow }}">Meetings</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['meetings'] }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $summary['meetings_today'] }} today &middot; {{ $summary['followups_due'] }} follow-ups due</p>
        </a>
        <a href="{{ $enquiriesResource::getUrl() }}" class="{{ $card }} block transition hover:ring-primary-500/40">
            <p class="{{ $eyebrow }}">Open enquiries</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['enquiries_open'] }}</p>
            <p class="mt-1 text-sm {{ $summary['enquiries_overdue'] ? 'text-danger-600' : 'text-gray-500' }}">{{ $summary['enquiries_overdue'] }} follow-ups overdue &middot; {{ $summary['enquiries_due_today'] }} due today</p>
        </a>
        <a href="{{ $reportsResource::getUrl() }}" class="{{ $card }} block transition hover:ring-primary-500/40">
            <p class="{{ $eyebrow }}">Today's reports</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['reports_today'] }} <span class="text-base font-normal text-gray-500">of {{ $summary['reps_active'] }}</span></p>
            <p class="mt-1 text-sm {{ $withoutReport->isNotEmpty() ? 'text-warning-600' : 'text-success-600' }}">{{ $withoutReport->isNotEmpty() ? 'Waiting: ' . $withoutReport->map(fn ($p) => $p->user->name)->take(3)->implode(', ') . ($withoutReport->count() > 3 ? ' +' . ($withoutReport->count() - 3) : '') : 'Everybody has reported' }}</p>
        </a>
        <a href="{{ $callsResource::getUrl() }}" class="{{ $card }} block transition hover:ring-primary-500/40">
            <p class="{{ $eyebrow }}">Client calls</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['calls'] }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $summary['calls_out'] }} out &middot; {{ $summary['calls_in'] }} in &middot; <span class="{{ $summary['calls_missed'] ? 'text-warning-600' : '' }}">{{ $summary['calls_missed'] }} missed</span> &middot; {{ $talk($summary['talk_seconds']) }} talk</p>
        </a>
        <a href="{{ $clientsResource::getUrl() }}" class="{{ $card }} block transition hover:ring-primary-500/40">
            <p class="{{ $eyebrow }}">Client pool</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['clients'] }}</p>
            <p class="mt-1 text-sm text-gray-500">{{ $summary['people'] }} {{ $summary['people'] === 1 ? 'person' : 'people' }} &middot; {{ $summary['clients_called'] }} called in {{ $days }} days</p>
        </a>
        <a href="{{ $repsResource::getUrl() }}" class="{{ $card }} block transition hover:ring-primary-500/40">
            <p class="{{ $eyebrow }}">Field reps</p>
            <p class="mt-2 text-2xl font-semibold text-gray-950 dark:text-white">{{ $summary['reps_active'] }}</p>
            <p class="mt-1 text-sm {{ $summary['reps_not_synced_today'] ? 'text-warning-600' : 'text-gray-500' }}">{{ $summary['reps_not_synced_today'] }} not synced today</p>
        </a>
    </div>

    {{-- spend per day --}}
    <div class="{{ $card }}">
        <p class="{{ $eyebrow }}">Spending per day</p>
        @if ($summary['spend_paise'] === 0)
            <p class="mt-3 text-sm text-gray-500">No expenses in the last {{ $days }} days.</p>
        @else
            <div style="display:flex;align-items:flex-end;gap:{{ $days > 30 ? '1px' : '3px' }};height:140px;margin-top:1rem;" role="img" aria-label="Spending per day">
                @foreach ($daily as $d)
                    <div title="{{ $d['label'] }}: {{ $money($d['paise']) }}"
                         style="flex:1;min-width:0;height:{{ max(2, round(100 * $d['paise'] / $dailyMax)) }}%;border-radius:3px;background:{{ $d['date'] === now(config('sales.timezone'))->toDateString() ? '#00B4D8' : '#0077B6' }};opacity:{{ $d['paise'] ? 1 : .25 }};"></div>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-xs text-gray-500"><span>{{ $daily[0]['label'] }}</span><span>{{ end($daily)['label'] }} (today)</span></div>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem;">
        {{-- by category --}}
        <div class="{{ $card }}">
            <p class="{{ $eyebrow }}">By category</p>
            @forelse ($categories as $c)
                <div class="mt-3">
                    <div class="flex justify-between text-sm"><span class="font-medium text-gray-900 dark:text-white">{{ $categoryNames[$c['category']] ?? ucfirst($c['category']) }}</span><span class="text-gray-600 dark:text-gray-300">{{ $money($c['paise']) }} <span class="text-gray-400">&middot; {{ round(100 * $c['paise'] / $categoryTotal) }}%</span></span></div>
                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10"><div style="height:100%;width:{{ round(100 * $c['paise'] / $categoryTotal) }}%;background:#0077B6;border-radius:9999px;"></div></div>
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-500">Nothing to show yet.</p>
            @endforelse
        </div>

        {{-- reps --}}
        <div class="{{ $card }}">
            <p class="{{ $eyebrow }}">Reps, last {{ $days }} days</p>
            @forelse ($reps as $rep)
                <div class="flex items-center gap-3 border-t border-gray-100 py-3 first:border-t-0 dark:border-white/10">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $rep['name'] }} <span class="font-mono text-xs text-gray-400">{{ $rep['code'] }}</span></p>
                        <p class="text-xs {{ $rep['stale'] ? 'text-warning-600' : 'text-gray-500' }}">
                            {{ $rep['last_sync_at'] ? 'Synced ' . $rep['last_sync_at']->diffForHumans() : 'Never synced' }}
                            &middot; {{ $rep['meetings'] }} meetings
                            &middot; {{ $rep['calls'] }} calls @if ($rep['talk_seconds']) ({{ $talk($rep['talk_seconds']) }}) @endif
                            &middot; {{ $rep['enquiries_open'] }} open enquiries @if ($rep['enquiries_late']) (<span class="text-danger-600">{{ $rep['enquiries_late'] }} late</span>) @endif
                            @if ($rep['pending']) &middot; <span class="text-danger-600">{{ $rep['pending'] }} to approve</span> @endif
                        </p>
                    </div>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $money($rep['spend_paise']) }}</p>
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-500">No reps yet. Add one under Sales team > Reps.</p>
            @endforelse
        </div>
    </div>

    {{-- enquiries: the pipeline, what is late, what is asked for --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem;">
        <div class="{{ $card }}">
            <div class="flex items-center justify-between"><p class="{{ $eyebrow }}">Enquiries by stage</p><a class="text-sm font-medium text-primary-600" href="{{ $enquiriesResource::getUrl() }}">Open enquiries</a></div>
            <p class="mt-2 text-xs text-gray-500">{{ $summary['enquiries_new'] }} new &middot; {{ $summary['enquiries_won'] }} orders &middot; {{ $summary['enquiries_lost'] }} lost, last {{ $days }} days &middot; {{ $summary['enquiries_idle'] }} untouched for 3 days</p>
            @php($stageMax = max(1, ...array_values($enquiryStatuses)))
            @foreach ($enquiryStatuses as $key => $n)
                <div class="mt-3">
                    <div class="flex justify-between text-sm"><span class="font-medium text-gray-900 dark:text-white">{{ $statusNames[$key] }}</span><span class="text-gray-600 dark:text-gray-300">{{ $n }}</span></div>
                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10"><div style="height:100%;width:{{ round(100 * $n / $stageMax) }}%;background:#0077B6;border-radius:9999px;"></div></div>
                </div>
            @endforeach
        </div>

        <div class="{{ $card }}">
            <p class="{{ $eyebrow }}">Asked for most, last {{ $days }} days</p>
            @forelse ($topProducts as $p)
                <div class="flex items-center gap-3 border-t border-gray-100 py-2 first:border-t-0 dark:border-white/10"><p class="min-w-0 flex-1 truncate text-sm text-gray-900 dark:text-white">{{ $p['product'] }}</p><p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $p['count'] }}</p></div>
            @empty
                <p class="mt-3 text-sm text-gray-500">No enquiries in this period.</p>
            @endforelse

            @if ($overdueEnquiries->isNotEmpty())
                <p class="{{ $eyebrow }} mt-6">Follow-up overdue</p>
                @foreach ($overdueEnquiries as $e)
                    <div class="flex items-center gap-3 border-t border-gray-100 py-2 first:border-t-0 dark:border-white/10">
                        <div class="min-w-0 flex-1"><p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $e->client_name }} <span class="text-gray-400">&middot; {{ $e->itemsSummary(2) }}</span></p><p class="text-xs text-gray-500">{{ $e->user->name }} &middot; {{ $e->statusLabel() }}</p></div>
                        <p class="text-xs font-semibold text-danger-600">{{ $e->next_followup_at->timezone(config('sales.timezone'))->format('d M') }}</p>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    {{-- clients nobody reached --}}
    <div class="{{ $card }}">
        <div class="flex items-center justify-between"><p class="{{ $eyebrow }}">Not called or met in 30 days</p><a class="text-sm font-medium text-primary-600" href="{{ $clientsResource::getUrl() }}">Open clients</a></div>
        @if ($summary['clients_without_people'] || $summary['clients_unassigned'])
            <p class="mt-2 text-xs text-gray-500">{{ $summary['clients_without_people'] }} clients have no person saved yet &middot; {{ $summary['clients_unassigned'] }} are not given to a rep.</p>
        @endif
        @forelse ($neglected as $c)
            <div class="flex items-center gap-3 border-t border-gray-100 py-3 first:border-t-0 dark:border-white/10">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $c->name }}@if ($c->city) <span class="text-gray-400">&middot; {{ $c->city }}</span>@endif</p>
                    <p class="text-xs text-gray-500">{{ $c->assignee?->name ?? 'Not given to a rep' }}</p>
                </div>
            </div>
        @empty
            <p class="mt-3 text-sm text-success-600">Every client was reached in the last 30 days.</p>
        @endforelse
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem;">
        {{-- to approve --}}
        <div class="{{ $card }}">
            <div class="flex items-center justify-between"><p class="{{ $eyebrow }}">Oldest waiting for approval</p><a class="text-sm font-medium text-primary-600" href="{{ $expenses::getUrl() }}">Open expenses</a></div>
            @forelse ($pending as $e)
                <div class="flex items-center gap-3 border-t border-gray-100 py-3 first:border-t-0 dark:border-white/10">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $e->user->name }} &middot; {{ $categoryNames[$e->category] ?? ucfirst($e->category) }}</p>
                        <p class="text-xs text-gray-500">{{ $e->expense_date->format('d M Y') }}@if ($e->note) &middot; {{ \Illuminate\Support\Str::limit($e->note, 40) }}@endif</p>
                    </div>
                    <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $money($e->amount_paise) }}</p>
                </div>
            @empty
                <p class="mt-3 text-sm text-success-600">Nothing is waiting. All caught up.</p>
            @endforelse
        </div>

        {{-- follow-ups + stale --}}
        <div class="{{ $card }}">
            <p class="{{ $eyebrow }}">Follow-ups due (next 7 days and overdue)</p>
            @forelse ($followups as $m)
                @php($late = $m->next_followup_at->isPast())
                <div class="flex items-center gap-3 border-t border-gray-100 py-3 first:border-t-0 dark:border-white/10">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $m->client_name }}</p>
                        <p class="text-xs text-gray-500">{{ $m->user->name }}@if ($m->purpose) &middot; {{ \Illuminate\Support\Str::limit($m->purpose, 40) }}@endif</p>
                    </div>
                    <p class="text-xs font-semibold {{ $late ? 'text-danger-600' : 'text-gray-600 dark:text-gray-300' }}">{{ $m->next_followup_at->timezone(config('sales.timezone'))->format('d M, h:i A') }}</p>
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-500">No follow-ups due.</p>
            @endforelse

            @if ($stale->isNotEmpty())
                <p class="{{ $eyebrow }} mt-6">Not synced today</p>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    @foreach ($stale as $p){{ $p->user->name }}@if (! $loop->last), @endif @endforeach
                </p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
