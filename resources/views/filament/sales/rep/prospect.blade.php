@php
    use App\Sales\Models\SalesProspect;
    use App\Sales\Models\SalesProspectNote;
    use App\Sales\Support\PhoneNumber;

    $last = $p->calls->first();
    $safeLinkedin = fn (?string $url) => $url && preg_match('#^https://([a-z0-9-]+\.)*linkedin\.com/[^\s<>"\']+$#i', $url) ? $url : null;
@endphp

<div class="sx-page">
    <div class="sx-card" style="padding:1rem 1.125rem">
        <div style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;align-items:baseline">
            <h2 class="sx-eyebrow">Company</h2>
            <span class="mv-chip">{{ SalesProspectNote::labelOf($last?->outcome) }}</span>
        </div>
        <dl class="rp-dl" style="margin-top:0.5rem">
            <div><dt class="sx-eyebrow">Territory</dt><dd>{{ $p->territory }}</dd></div>
            <div><dt class="sx-eyebrow">Address</dt><dd>{{ $p->address ?: '—' }}</dd></div>
            <div><dt class="sx-eyebrow">Found through</dt><dd>{{ SalesProspect::SOURCES[$p->source] ?? '—' }}</dd></div>
            <div><dt class="sx-eyebrow">Added by</dt><dd>{{ $mine ? 'You' : ($p->addedBy?->name ?? 'Office') }}</dd></div>
            @if ($last?->next_followup_at)
                <div><dt class="sx-eyebrow">Next follow-up</dt><dd>{{ $last->next_followup_at->timezone($tz)->format('D j M Y, h:i A') }}</dd></div>
            @endif
        </dl>
        @if (filled($p->notes))
            <p class="sx-small" style="margin-top:0.75rem;white-space:pre-line">{{ $p->notes }}</p>
        @endif
        @unless ($mine)
            <p class="sx-small sx-mute" style="margin-top:0.75rem">Only the rep who added this prospect can change it. You can log a call.</p>
        @endunless
    </div>

    <div class="sx-card" style="padding:1rem 1.125rem">
        <h2 class="sx-eyebrow">Contact persons ({{ $p->people->count() }})</h2>
        @forelse ($p->people as $person)
            <div class="ca-row" style="margin-top:0.25rem">
                <span class="ca-row-text">
                    <span class="ca-row-name">{{ $person->name }}</span>
                    <span class="ca-row-sub">{{ $person->position ?: 'Position not known' }}</span>
                </span>
                <span style="display:flex;gap:0.75rem;flex-wrap:wrap;justify-content:flex-end">
                    @if ($person->phone)
                        <a class="sx-link" href="tel:{{ preg_replace('/[^0-9+]/', '', $person->phone) }}">{{ PhoneNumber::display($person->phone) }}</a>
                    @endif
                    @if ($person->email && filter_var($person->email, FILTER_VALIDATE_EMAIL))
                        <a class="sx-link" href="mailto:{{ $person->email }}">{{ $person->email }}</a>
                    @endif
                    @if ($link = $safeLinkedin($person->linkedin))
                        <a class="sx-link" href="{{ $link }}" target="_blank" rel="noopener noreferrer nofollow">LinkedIn</a>
                    @endif
                </span>
            </div>
        @empty
            <p class="ca-empty">No contact person yet.{{ $mine ? ' Add one with Edit.' : '' }}</p>
        @endforelse
    </div>

    <div class="sx-card" style="padding:1rem 1.125rem">
        <h2 class="sx-eyebrow">Calls ({{ $p->calls->count() }})</h2>
        @forelse ($p->calls as $call)
            <div class="ca-row" style="margin-top:0.25rem">
                <span class="ca-row-text">
                    <span class="ca-row-name">{{ SalesProspectNote::labelOf($call->outcome) }}</span>
                    <span class="ca-row-sub">
                        {{ $call->noted_at?->timezone($tz)->format('j M Y, h:i A') }} · {{ (int) $call->user_id === (int) auth()->id() ? 'You' : ($call->user?->name ?? 'A rep') }}@if ($call->remark) · {{ $call->remark }}@endif
                    </span>
                </span>
                @if ($call->next_followup_at)
                    <span class="mv-chip">Follow-up {{ $call->next_followup_at->timezone($tz)->format('j M, h:i A') }}</span>
                @endif
            </div>
        @empty
            <p class="ca-empty">Nobody has called yet. Use "Log a call" after you call.</p>
        @endforelse
    </div>
</div>
