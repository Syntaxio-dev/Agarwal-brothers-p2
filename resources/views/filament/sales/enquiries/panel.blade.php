{{-- the side panel of one enquiry: J / K = the next / previous one of the same column (ignored while typing) --}}
<div class="en-panel"
     x-data="{ keys(ev) { if (ev.ctrlKey || ev.metaKey || ev.altKey || ev.target.closest('input, textarea, select')) return; const k = ev.key.toLowerCase(); if (k === 'j') $wire.openNeighbour(@js($e->uuid), 1); else if (k === 'k') $wire.openNeighbour(@js($e->uuid), -1); } }"
     x-on:keydown.window="keys($event)">

    <div class="en-panel-head">
        <span class="sx-chip {{ $e->status === 'won' ? 'ok' : ($e->status === 'lost' ? 'late' : '') }}">{{ $e->statusLabel() }}</span>
        @if ($canMove)
            <label class="ex-field">
                <span class="ex-lab">Move to</span>
                <select aria-label="Move to another stage" x-on:change="if ($event.target.value) { $wire.moveEnquiry(@js($e->uuid), $event.target.value, @js($e->status), @js($version)); $event.target.value = ''; }">
                    <option value="">Choose a stage&hellip;</option>
                    @foreach ($stages as $key => $label)@if ($key !== $e->status)<option value="{{ $key }}">{{ $label }}</option>@endif @endforeach
                </select>
            </label>
        @endif
    </div>

    <p class="sx-small">{{ collect([$e->contact_name, $e->user->name . '\'s client'])->filter()->implode(' · ') }}</p>

    @if ($urgency['key'] !== 'none')
        <div class="en-banner u-{{ $urgency['key'] }}" role="note">
            @if ($followup)
                Follow-up {{ $urgency['key'] === 'overdue' ? 'was' : 'is' }} {{ $followup->format('j M, g:i a') }} &middot; <b>{{ $urgency['label'] }}</b>
            @else
                No follow-up set &middot; <b>{{ $urgency['label'] }}</b>
            @endif
        </div>
    @endif

    <dl class="ex-fields">
        <div><dt class="sx-eyebrow">Rep</dt><dd>{{ $e->user->name }}</dd></div>
        <div><dt class="sx-eyebrow">Came through</dt><dd>{{ $e->sourceLabel() ?? '—' }}</dd></div>
        <div><dt class="sx-eyebrow">Opened</dt><dd>{{ $opened->format('j M Y, g:i a') }}</dd></div>
        <div><dt class="sx-eyebrow">Next follow-up</dt><dd>{{ $followup ? $followup->format('j M Y, g:i a') : '—' }}</dd></div>
    </dl>

    <h3 class="en-h3">What they asked for</h3>
    <table class="sx-table en-items">
        <thead><tr><th scope="col" class="rp-left">Product</th><th scope="col">Qty</th><th scope="col" class="rp-left">Brands</th><th scope="col">Budget</th></tr></thead>
        <tbody>
            @foreach ($items as $i)
                <tr>
                    <td class="rp-left">{{ $i['product'] ?? '' }}</td>
                    <td>{{ $i['quantity'] ?? 1 }}</td>
                    <td class="rp-left">{{ $i['brands'] ?? '' }}</td>
                    <td>{{ isset($i['budget_paise']) && $i['budget_paise'] ? $money((int) $i['budget_paise']) : '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        @if ($e->budgetPaise() > 0)<tfoot><tr><th scope="row" colspan="3" class="rp-left">Total</th><td>{{ $money($e->budgetPaise()) }}</td></tr></tfoot>@endif
    </table>

    <h3 class="en-h3">Notes and status changes</h3>
    <ol class="en-timeline">
        @forelse ($timeline as $t)
            <li>
                <span class="sx-small">{{ $t['at']->format('j M, g:i a') }}</span>
                @if ($t['status'])<span class="sx-chip">{{ $t['status'] }}</span>@endif
                <span class="en-tl-text">{{ $t['text'] ?: 'Note' }}</span>
            </li>
        @empty
            <li class="sx-small">No notes yet.</li>
        @endforelse
    </ol>

    <div class="rp-ready-actions en-links">
        @if ($whatsapp)<a class="sx-btn" href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer">Message {{ $e->user->name }}</a>@endif
        @if ($callPhone)<a class="sx-btn" href="tel:{{ $callPhone }}">Call {{ $callText }}</a>@endif
        @if ($clientUrl)<a class="sx-btn" href="{{ $clientUrl }}">Open client</a>@endif
        @if ($meetingsUrl)<a class="sx-btn" href="{{ $meetingsUrl }}">Meetings</a>@endif
    </div>

    <p class="sx-small en-owner">{{ $e->user->name }} owns this enquiry and its follow-up. Changes made on their phone appear here after their next sync.</p>
</div>
