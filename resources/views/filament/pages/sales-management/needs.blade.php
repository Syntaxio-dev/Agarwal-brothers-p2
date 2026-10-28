@use('App\Sales\Filament\Resources\SalesClients\SalesClientResource')
@use('App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource')
@use('App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource')
@use('App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource')
@use('Illuminate\Support\Str')
{{-- 3. Needs your action (tabs) and Needs a word (flags) --}}
@php
    $tabLabels = ['approvals' => 'Approvals', 'enquiries' => 'Overdue enquiries', 'notlogged' => 'Not logged', 'followups' => 'Follow-ups', 'reach' => 'Not reached'];
    $tabCounts = ['approvals' => (int) $s['pending_count'], 'enquiries' => $s['enquiries_overdue'], 'notlogged' => $s['meetings_not_logged'], 'followups' => $s['followups_due'], 'reach' => $neglectedTotal];
    $lateTabs = ['approvals', 'enquiries', 'notlogged'];
    $hoursLate = fn ($m) => \Carbon\CarbonImmutable::instance($m->plannedOrActualAt())->diffForHumans(['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 1]) . ' late';

    $rows = match ($activeTab) {
        'enquiries' => $tabRows['enquiries']->map(fn ($e) => [
            'title' => $e->client_name, 'sub' => $e->user->name . ' · ' . $e->itemsSummary(2) . ' · ' . $e->statusLabel(),
            'chip' => $dayShort($e->next_followup_at), 'late' => true, 'who' => $e->user->name,
            'url' => $to('sales-enquiries', SalesEnquiryResource::class, ['tableFilters' => ['overdue' => ['isActive' => true]]]),
        ]),
        'notlogged' => $tabRows['notlogged']->map(fn ($m) => [
            'title' => $m->client_name, 'sub' => $m->user->name . ' · ' . \Carbon\CarbonImmutable::instance($m->plannedOrActualAt())->timezone($tz)->format('D j M, g:i a') . ($m->purpose ? ' · ' . Str::limit($m->purpose, 30) : ''),
            'chip' => $hoursLate($m), 'late' => true, 'who' => $m->user->name,
            'url' => $to('sales-meetings', SalesMeetingResource::class, ['tableFilters' => ['not_logged' => ['isActive' => true]]]),
        ]),
        'followups' => $tabRows['followups']->map(fn ($m) => [
            'title' => $m->client_name, 'sub' => $m->user->name . ($m->purpose ? ' · ' . Str::limit($m->purpose, 40) : ''),
            'chip' => $dayShort($m->next_followup_at), 'late' => $m->next_followup_at->isPast(), 'who' => $m->user->name,
            'url' => $to('sales-meetings', SalesMeetingResource::class),
        ]),
        'reach' => $tabRows['reach']->map(fn ($c) => [
            'title' => $c->name, 'sub' => collect([$c->city, $c->assignee?->name ?? 'Not given to a rep'])->filter()->implode(' · '),
            'chip' => $c->days_since_contact === null ? 'Never reached' : $c->days_since_contact . ' days', 'late' => false, 'who' => $c->name,
            'url' => $to('sales-clients', SalesClientResource::class, ['record' => $c->getKey()], 'edit'),
        ]),
        default => collect(),
    };
    $emptyText = ['approvals' => 'Nothing is waiting. All caught up.', 'enquiries' => 'No follow-up is overdue.', 'notlogged' => 'Every planned visit was written up.', 'followups' => 'No follow-ups due.', 'reach' => 'Every client was reached in the last 30 days.'];
@endphp

<section class="sx-row2">
    <div class="sx-card">
        <div class="sx-card-head">
            <p class="sx-eyebrow">Needs your action</p>
            @if ($activeTab === 'approvals' && $showMoney && $withBill['count'] > 0)
                <button type="button" class="sx-link" wire:click="mountAction('approveAll')">Approve all {{ $withBill['count'] }} with a bill</button>
            @endif
        </div>

        <div class="sx-tabs" role="group" aria-label="What needs your action">
            @foreach ($visibleTabs as $key)
                <button type="button" class="sx-btn" aria-pressed="{{ $activeTab === $key ? 'true' : 'false' }}" wire:click="setTab('{{ $key }}')">
                    {{ $tabLabels[$key] }} <span class="sx-tab-count {{ in_array($key, $lateTabs, true) && $tabCounts[$key] > 0 ? 'late' : '' }}">{{ $tabCounts[$key] }}</span>
                </button>
            @endforeach
        </div>

        @if (empty($visibleTabs))
            <p class="sx-empty">You do not have access to these lists.</p>
        @elseif ($activeTab === 'approvals')
            @php $approvalsUrl = $to('sales-expenses', SalesExpenseResource::class, ['tab' => 'waiting']); @endphp
            @forelse ($tabRows['approvals'] as $e)
                <div class="sx-item {{ $loop->first ? 'first' : '' }}" wire:key="approval-{{ $e->id }}">
                    <span class="sx-avatar">{{ $initials($e->user->name) }}</span>
                    @if ($approvalsUrl)<a href="{{ $approvalsUrl }}" class="sx-grow">@else<div class="sx-grow">@endif
                        <p class="sx-trunc sx-name-line" style="font-weight:600;font-size:.875rem">{{ $e->user->name }} &middot; {{ $categoryNames[$e->category] ?? ucfirst($e->category) }}</p>
                        <p class="sx-small sx-trunc">{{ $e->expense_date->format('d M Y') }}@if ($e->note) &middot; {{ Str::limit($e->note, 40) }}@endif</p>
                    @if ($approvalsUrl)</a>@else</div>@endif
                    <span class="sx-chip">{{ $money($e->amount_paise) }}</span>
                    @if ($e->bill_type === 'without_bill')
                        <span class="sx-chip late">No bill</span>
                    @endif
                    <button type="button" class="sx-btn sx-btn-approve" wire:click="approveExpense({{ $e->id }})" wire:loading.attr="disabled" wire:target="approveExpense({{ $e->id }})" aria-label="Approve {{ $money($e->amount_paise) }} of {{ $e->user->name }}">Approve</button>
                </div>
            @empty
                <p class="sx-empty sx-ok">{{ $emptyText['approvals'] }}</p>
            @endforelse
        @else
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
                <p class="sx-empty sx-ok">{{ $emptyText[$activeTab] ?? '' }}</p>
            @endforelse
        @endif
    </div>

    <div class="sx-card">
        <div class="sx-card-head" style="margin-bottom:.25rem">
            <p class="sx-eyebrow">Needs a word</p>
        </div>
        @forelse ($flags as $flag)
            <div class="sx-flag {{ $flag['tone'] }}">
                <span class="sx-flag-icon"><x-filament::icon :icon="$flag['icon']" class="h-4 w-4" /></span>
                <div>
                    <p class="sx-flag-title">{{ $flag['title'] }}</p>
                    <p class="sx-small">{{ $flag['text'] }}</p>
                </div>
            </div>
        @empty
            <p class="sx-empty sx-ok">Nothing needs a word right now.</p>
        @endforelse
    </div>
</section>
