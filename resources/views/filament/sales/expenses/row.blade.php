@php
    $on = $current && $current->uuid === $row->uuid;
    [$chipText, $chipTone] = $chip($row);
    $who = $row->user?->name ?? 'Rep';
@endphp
<div class="ex-row {{ $on ? 'on' : '' }}" wire:key="row-{{ $row->uuid }}" data-uuid="{{ $row->uuid }}">
    @if ($row->isPending())
        <label class="ex-check"><input type="checkbox" value="{{ $row->uuid }}" wire:model.live="selected" aria-label="Select {{ $who }}, {{ \App\Sales\Models\SalesExpense::categoryLabel($row->category) }}, {{ $rupees($row->amount_paise) }}"></label>
    @else
        <span class="ex-check"></span>
    @endif
    <button type="button" class="ex-pick" wire:click="selectExpense('{{ $row->uuid }}')" aria-current="{{ $on ? 'true' : 'false' }}">
        <span class="sx-avatar" aria-hidden="true">{{ $initials($who) }}</span>
        <span class="ex-row-text">
            <span class="ex-row-title">{{ $who }} &middot; {{ \App\Sales\Models\SalesExpense::categoryLabel($row->category) }}</span>
            <span class="ex-row-sub">{{ $row->expense_date->format('j M') }} &middot; {{ $areaText($row->work_area) }}@if ($row->note) &middot; {{ $row->note }}@endif</span>
            @if (! $row->isPending())
                <span class="ex-row-sub">{{ $row->status === 'approved' ? 'Approved' : 'Rejected' }} by {{ $row->reviewer?->name ?? 'a manager' }}@if ($row->reviewed_at), {{ \Carbon\CarbonImmutable::instance($row->reviewed_at)->timezone(config('sales.timezone'))->format('j M, g:i a') }}@endif</span>
                @if ($row->status === 'rejected' && $row->reject_reason)<span class="ex-row-sub">Reason: {{ $row->reject_reason }}</span>@endif
            @endif
        </span>
        <span class="ex-row-side">
            <span class="ex-row-amount">{{ $rupees($row->amount_paise) }}</span>
            <span class="sx-chip {{ $chipTone }}">{{ $chipText }}</span>
            @if (! $row->isPending())<span class="sx-chip {{ $row->status === 'approved' ? 'ok' : 'late' }}">{{ $row->status === 'approved' ? 'Approved' : 'Rejected' }}</span>@endif
        </span>
    </button>
</div>
