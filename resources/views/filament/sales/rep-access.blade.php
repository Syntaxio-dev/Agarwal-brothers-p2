@php
    $initials = fn (string $name) => collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
    // short column heads; the full name and what it opens are in the tooltip and in the card below
    $short = ['leads' => 'Leads', 'expenses' => 'Expenses', 'prospects' => 'Prospects', 'meetings' => 'Meetings', 'tasks' => 'Tasks', 'notes' => 'To-do & notes'];
@endphp

<x-filament-panels::page>
    <div class="sx-page rp-page">

        <header class="sx-head">
            <div>
                <p class="sx-eyebrow">Sales team</p>
                <h1 class="sx-h1">Rep access</h1>
                <p class="sx-sub">What each rep may use on the web desk (sales.agarwalbrothers.com/rep). Nothing is open for a new rep. The phone app is not affected.</p>
            </div>
            <div class="sx-actions">
                <a href="{{ $repsUrl }}" class="sx-btn">Reps</a>
            </div>
        </header>

        <section class="sx-card rp-card" aria-label="Web desk access per rep">
            @if ($reps->isEmpty())
                <p class="sx-empty">No reps yet. Add a rep first, then open the parts of the web desk they may use.</p>
            @else
                <div class="sx-scroll">
                    <table class="sx-table rp-roster" style="min-width:54rem">
                        <thead>
                            <tr>
                                <th scope="col" class="sx-rep-cell">Rep</th>
                                @foreach ($areas as $key => [$label, $what])
                                    <th scope="col" style="text-align:center" title="{{ $label }}: {{ $what }}">{{ $short[$key] ?? $label }}</th>
                                @endforeach
                                <th scope="col" style="text-align:center"><span class="sx-sr">Every part</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reps as $r)
                                <tr class="rp-row {{ $r['active'] ? '' : 'rp-off' }}" wire:key="access-{{ $r['id'] }}">
                                    <td class="sx-rep-cell">
                                        <div class="sx-rep">
                                            <span class="sx-avatar" aria-hidden="true">{{ $initials($r['name']) }}</span>
                                            <div class="sx-rep-text">
                                                <p class="sx-trunc"><a href="{{ $profileUrl($r['id']) }}" class="sx-name rp-name">{{ $r['name'] }}</a></p>
                                                <p class="sx-small rp-mono">{{ $r['code'] }}</p>
                                                @unless ($r['active'])<p><span class="sx-chip late">Switched off</span></p>@endunless
                                            </div>
                                        </div>
                                    </td>
                                    @foreach ($areas as $key => [$label, $what])
                                        @php $on = in_array($key, $r['areas'], true); @endphp
                                        <td style="text-align:center">
                                            <button type="button" role="switch" class="rp-switch" aria-checked="{{ $on ? 'true' : 'false' }}"
                                                aria-label="{{ $label }} for {{ $r['name'] }}" title="{{ $label }}"
                                                wire:click="toggle({{ $r['id'] }}, '{{ $key }}')" wire:loading.attr="disabled">
                                                <span class="rp-knob"></span>
                                            </button>
                                        </td>
                                    @endforeach
                                    <td style="text-align:center">
                                        <div style="display:flex;gap:0.375rem;justify-content:center;flex-wrap:nowrap;margin-top:0.375rem">
                                            <button type="button" class="sx-btn" wire:click="setAllFor({{ $r['id'] }}, true)">Open all</button>
                                            <button type="button" class="sx-btn" wire:click="setAllFor({{ $r['id'] }}, false)"
                                                wire:confirm="Close the whole web desk for {{ $r['name'] }}? The phone app keeps working.">Close all</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row" class="sx-rep-cell">
                                    <p class="sx-eyebrow">Every active rep</p>
                                    <p class="sx-small">{{ $activeCount }} {{ $activeCount === 1 ? 'rep' : 'reps' }}</p>
                                </th>
                                @foreach ($areas as $key => [$label, $what])
                                    @php $n = $openCount($key); @endphp
                                    <td style="text-align:center">
                                        <span class="sx-chip {{ $activeCount > 0 && $n === $activeCount ? 'ok' : '' }}">{{ $n }} of {{ $activeCount }}</span>
                                        <div style="display:flex;gap:0.375rem;justify-content:center;flex-wrap:nowrap;margin-top:0.375rem">
                                            <button type="button" class="sx-link" style="background:none;border:0;padding:0;cursor:pointer" wire:click="setAreaForAll('{{ $key }}', true)" wire:confirm="Open {{ $label }} for every active rep?">Open</button>
                                            <button type="button" class="sx-link" style="background:none;border:0;padding:0;cursor:pointer" wire:click="setAreaForAll('{{ $key }}', false)" wire:confirm="Close {{ $label }} for every rep?">Close</button>
                                        </div>
                                    </td>
                                @endforeach
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </section>

        <section class="sx-card rp-card" aria-label="What each part opens">
            <h2 class="sx-eyebrow">What each part opens</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(16rem,1fr));gap:0.875rem 1.5rem;margin-top:0.75rem">
                @foreach ($areas as $key => [$label, $what])
                    <div>
                        <p class="sx-name">{{ $label }}</p>
                        <p class="sx-small">{{ $what }}</p>
                    </div>
                @endforeach
            </div>
            <p class="sx-small sx-mute" style="margin-top:1rem">A change works at once, also on a page the rep already has open, and is kept in the activity log. Phone calls are never on the web desk.</p>
        </section>
    </div>
</x-filament-panels::page>
