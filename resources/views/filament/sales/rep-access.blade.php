<x-filament-panels::page>
    <div class="sx-page">
        @if ($reps->isEmpty())
            <div class="sx-card" style="padding:1rem 1.125rem">
                <p class="ca-empty">No reps yet. Add one in Reps first.</p>
            </div>
        @else
            <div class="sx-card" style="padding:0;overflow-x:auto">
                <table class="sx-table rp-roster" style="min-width:48rem">
                    <thead>
                        <tr>
                            <th scope="col" class="rp-left">Rep</th>
                            @foreach ($areas as $key => [$label, $what])
                                <th scope="col" title="{{ $what }}" style="text-align:center">{{ $label }}</th>
                            @endforeach
                            <th scope="col" style="text-align:center">All</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reps as $r)
                            <tr class="rp-row {{ $r['active'] ? '' : 'rp-off' }}" wire:key="access-{{ $r['id'] }}">
                                <td class="rp-left">
                                    <div class="rp-cell">
                                        <span>
                                            <span class="ca-row-name">{{ $r['name'] }}</span>
                                            <span class="ca-row-sub">{{ $r['code'] }}@unless ($r['active']) · switched off: no web desk @endunless</span>
                                        </span>
                                    </div>
                                </td>
                                @foreach ($areas as $key => [$label, $what])
                                    @php $on = in_array($key, $r['areas'], true); @endphp
                                    <td style="text-align:center">
                                        <button type="button" role="switch" class="rp-switch" aria-checked="{{ $on ? 'true' : 'false' }}"
                                            aria-label="{{ $label }} for {{ $r['name'] }}"
                                            wire:click="toggle({{ $r['id'] }}, '{{ $key }}')" wire:loading.attr="disabled">
                                            <span class="rp-knob"></span>
                                        </button>
                                    </td>
                                @endforeach
                                <td style="text-align:center;white-space:nowrap">
                                    <button type="button" class="sx-btn" wire:click="setAllFor({{ $r['id'] }}, true)">Open all</button>
                                    <button type="button" class="sx-btn" wire:click="setAllFor({{ $r['id'] }}, false)"
                                        wire:confirm="Close the whole web desk for {{ $r['name'] }}? The phone app keeps working.">Close all</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th scope="row" class="rp-left"><span class="sx-small">Every active rep ({{ $activeCount }})</span></th>
                            @foreach ($areas as $key => [$label, $what])
                                <td style="text-align:center;white-space:nowrap">
                                    <span class="sx-small sx-mute" style="display:block">{{ $openCount($key) }} of {{ $activeCount }}</span>
                                    <button type="button" class="sx-link" style="background:none;border:0;cursor:pointer" wire:click="setAreaForAll('{{ $key }}', true)"
                                        wire:confirm="Open {{ $label }} for every active rep?">Open</button>
                                    ·
                                    <button type="button" class="sx-link" style="background:none;border:0;cursor:pointer" wire:click="setAreaForAll('{{ $key }}', false)"
                                        wire:confirm="Close {{ $label }} for every rep?">Close</button>
                                </td>
                            @endforeach
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <div class="sx-card" style="padding:1rem 1.125rem">
            <h2 class="sx-eyebrow">What each part opens</h2>
            <dl class="rp-dl" style="margin-top:0.5rem">
                @foreach ($areas as $key => [$label, $what])
                    <div><dt class="sx-eyebrow">{{ $label }}</dt><dd class="sx-small">{{ $what }}</dd></div>
                @endforeach
            </dl>
            <p class="sx-small sx-mute" style="margin-top:0.75rem">A change works at once, also on a page the rep already has open. Every change is kept in the activity log. Phone calls are never on the web desk.</p>
        </div>
    </div>
</x-filament-panels::page>
