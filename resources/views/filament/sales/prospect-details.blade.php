{{-- One prospect for the manager: where it is, who is there, and every call the team made (newest first). Text only: nothing here is ever HTML. --}}
<div style="display:grid;gap:1rem">
    <div>
        <p style="font-weight:600">{{ $prospect->territory }}@if ($prospect->address) &middot; {{ $prospect->address }}@endif</p>
        <p style="opacity:.7;font-size:.875rem">
            Added by {{ $prospect->addedBy?->name ?? 'the office' }}@if ($prospect->source) &middot; found through {{ \App\Sales\Models\SalesProspect::SOURCES[$prospect->source] ?? $prospect->source }}@endif
        </p>
        @if ($prospect->notes)<p style="margin-top:.5rem">{{ $prospect->notes }}</p>@endif
    </div>

    <div>
        <p style="font-weight:600;margin-bottom:.5rem">People ({{ $prospect->people->count() }})</p>
        @forelse ($prospect->people as $person)
            <div style="padding:.5rem 0;border-top:1px solid rgba(128,128,128,.25)">
                <p style="font-weight:500">{{ $person->name }}@if ($person->position) <span style="opacity:.7">&middot; {{ $person->position }}</span>@endif</p>
                <p style="font-size:.875rem">
                    @if ($person->phone)<a href="tel:{{ $person->phone }}" style="text-decoration:underline">{{ $person->phone }}</a>@endif
                    @if ($person->email) &middot; <a href="mailto:{{ $person->email }}" style="text-decoration:underline">{{ $person->email }}</a>@endif
                    @if ($person->linkedin) &middot; <a href="{{ $person->linkedin }}" target="_blank" rel="noopener noreferrer" style="text-decoration:underline">LinkedIn</a>@endif
                </p>
            </div>
        @empty
            <p style="opacity:.7">Nobody is saved here yet.</p>
        @endforelse
    </div>

    <div>
        <p style="font-weight:600;margin-bottom:.5rem">Calls ({{ $prospect->calls->count() }})</p>
        @forelse ($prospect->calls as $note)
            <div style="padding:.5rem 0;border-top:1px solid rgba(128,128,128,.25)">
                <p style="font-weight:500">{{ \App\Sales\Models\SalesProspectNote::labelOf($note->outcome) }}
                    <span style="opacity:.7;font-weight:400">&middot; {{ $note->user?->name ?? 'a rep' }} &middot; {{ $note->noted_at?->timezone(config('sales.timezone'))->format('d M Y, h:i a') }}</span></p>
                @if ($note->remark)<p>{{ $note->remark }}</p>@endif
                @if ($note->next_followup_at)<p style="opacity:.7;font-size:.875rem">Call again: {{ $note->next_followup_at->timezone(config('sales.timezone'))->format('d M Y, h:i a') }}</p>@endif
            </div>
        @empty
            <p style="opacity:.7">Nobody has called yet.</p>
        @endforelse
    </div>
</div>
