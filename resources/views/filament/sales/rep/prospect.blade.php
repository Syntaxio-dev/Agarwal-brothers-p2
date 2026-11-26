@php
    use App\Sales\Models\SalesProspect;
    use App\Sales\Models\SalesProspectNote;
    use App\Sales\Support\PhoneNumber;

    $last = $p->calls->first();
    $open = $last && (SalesProspectNote::OUTCOMES[$last->outcome][1] ?? false);
    $safeLinkedin = fn (?string $url) => $url && preg_match('#^https://([a-z0-9-]+\.)*linkedin\.com/[^\s<>"\']+$#i', $url) ? $url : null;
    $chipTone = fn (?string $outcome) => match (true) {
        $outcome === null => '',
        in_array($outcome, ['interested', 'meeting_fixed', 'converted'], true) => 'ok',
        (bool) (SalesProspectNote::OUTCOMES[$outcome][1] ?? false) => '',
        default => 'late',
    };
    // NOT the class "sx-page": the Sales theme hides a page's own header (title + buttons) when it finds one
    $card = 'background:var(--sx-card);border:1px solid var(--sx-line);border-radius:0.75rem;padding:1.125rem;min-width:0';
@endphp

<div style="display:grid;gap:1rem">
    <section style="{{ $card }}" aria-label="Company">
        <div style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;align-items:center">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center">
                <span class="sx-chip" style="background:var(--sx-tint);color:var(--sx-link)">{{ $p->territory }}</span>
                <span class="sx-chip {{ $chipTone($last?->outcome) }}">{{ SalesProspectNote::labelOf($last?->outcome) }}</span>
                @if ($open && $last->next_followup_at)
                    <span class="sx-chip {{ $last->next_followup_at->isPast() ? 'late' : '' }}">Follow-up {{ $last->next_followup_at->timezone($tz)->format('D j M, h:i A') }}</span>
                @endif
            </div>
        </div>
        <dl class="rp-dl" style="margin-top:0.875rem">
            <div><dt class="sx-eyebrow">Address</dt><dd>{{ $p->address ?: '—' }}</dd></div>
            <div><dt class="sx-eyebrow">Found through</dt><dd>{{ SalesProspect::SOURCES[$p->source] ?? '—' }}</dd></div>
        </dl>
        @if (filled($p->notes))
            <p class="sx-small" style="margin-top:0.75rem;white-space:pre-line">{{ $p->notes }}</p>
        @endif
        @unless ($mine)
            <p class="sx-small sx-mute" style="margin-top:0.75rem">Only the rep who added this prospect changes it or its people. You can log a call and plan a visit.</p>
        @endunless
    </section>

    <section style="{{ $card }}" aria-label="Contact persons">
        <div style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;align-items:center">
            <h2 class="sx-eyebrow">Contact persons ({{ $p->people->count() }})</h2>
            @if ($mine && $p->people->count() < \App\Sales\Rep\Support\DeskProspects::MAX_PEOPLE)
                <button type="button" class="sx-btn" wire:click="mountAction('addPerson')">+ Add contact person</button>
            @endif
        </div>
        @forelse ($p->people as $person)
            <div wire:key="person-{{ $person->id }}" style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;align-items:center;padding:0.75rem 0;border-top:1px solid var(--sx-line);margin-top:{{ $loop->first ? '0.75rem' : '0' }}">
                <div style="min-width:12rem">
                    <p class="sx-name">{{ $person->name }}</p>
                    <p class="sx-small sx-mute">{{ $person->position ?: 'Position not known' }}</p>
                </div>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;justify-content:flex-end">
                    @if ($person->phone)
                        <a class="sx-btn" href="tel:{{ preg_replace('/[^0-9+]/', '', $person->phone) }}">{{ PhoneNumber::display($person->phone) }}</a>
                    @endif
                    @if ($person->email && filter_var($person->email, FILTER_VALIDATE_EMAIL))
                        <a class="sx-btn" href="mailto:{{ $person->email }}">E-mail</a>
                    @endif
                    @if ($link = $safeLinkedin($person->linkedin))
                        <a class="sx-btn" href="{{ $link }}" target="_blank" rel="noopener noreferrer nofollow">LinkedIn</a>
                    @endif
                    @if ($mine)
                        <button type="button" class="sx-link" style="background:none;border:0;cursor:pointer;padding:0 0.25rem" wire:click="mountAction('editPerson', { person: {{ (int) $person->id }} })">Change</button>
                        <button type="button" class="sx-link" style="background:none;border:0;cursor:pointer;padding:0 0.25rem;color:var(--sx-alert-ink)" wire:click="mountAction('removePerson', { person: {{ (int) $person->id }} })">Remove</button>
                    @endif
                </div>
            </div>
        @empty
            <p class="sx-empty">No contact person yet.{{ $mine ? ' Add the people you can call there.' : '' }}</p>
        @endforelse
    </section>

    <section style="{{ $card }}" aria-label="Calls">
        <h2 class="sx-eyebrow">Calls ({{ $p->calls->count() }})</h2>
        @forelse ($p->calls as $call)
            <div style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;align-items:center;padding:0.75rem 0;border-top:1px solid var(--sx-line);margin-top:{{ $loop->first ? '0.75rem' : '0' }}">
                <div style="min-width:12rem">
                    <p><span class="sx-chip {{ $chipTone($call->outcome) }}">{{ SalesProspectNote::labelOf($call->outcome) }}</span></p>
                    @if ($call->remark)<p class="sx-small" style="margin-top:0.375rem">{{ $call->remark }}</p>@endif
                    <p class="sx-small sx-mute" style="margin-top:0.25rem">{{ $call->noted_at?->timezone($tz)->format('j M Y, h:i A') }} · {{ (int) $call->user_id === (int) auth()->id() ? 'You' : ($call->user?->name ?? 'A rep') }}</p>
                </div>
                @if ($call->next_followup_at)
                    <span class="sx-chip">Follow-up {{ $call->next_followup_at->timezone($tz)->format('j M, h:i A') }}</span>
                @endif
            </div>
        @empty
            <p class="sx-empty">Nobody has called yet. Use "Log a call" after you call.</p>
        @endforelse
    </section>
</div>
