@props(['verticals' => true, 'contact' => true, 'tight' => false])
{{-- Useful next steps for error pages and empty states: popular verticals and who to call. --}}
@php
    $vs = $verticals ? \App\Support\HelpLinks::verticals() : [];
    $people = $contact ? \App\Support\HelpLinks::contacts() : [];
    $hours = \App\Support\HelpLinks::hours();
@endphp

<div {{ $attributes->class([$tight ? 'mt-8' : 'mt-10', 'text-left']) }}>
    @if (count($vs))
        <div>
            <p class="flex items-center justify-center gap-2 font-mono text-[11px] font-medium uppercase tracking-[0.22em] text-link">
                <span class="h-0.5 w-5 bg-cyan" style="box-shadow: 3px 0 0 #0B2545;"></span>
                Popular verticals
            </p>
            <div class="mt-4 flex flex-wrap justify-center gap-2.5">
                @foreach ($vs as $v)
                    <a href="{{ $v['url'] }}" class="chip !pl-3.5 hover:text-link">{{ $v['name'] }}</a>
                @endforeach
                <a href="{{ route('verticals.index') }}" class="chip chip-active !pl-3.5">All verticals &rarr;</a>
            </div>
        </div>
    @endif

    @if (count($people))
        <div class="{{ count($vs) ? 'mt-9' : '' }}">
            <p class="flex items-center justify-center gap-2 font-mono text-[11px] font-medium uppercase tracking-[0.22em] text-link">
                <span class="h-0.5 w-5 bg-cyan" style="box-shadow: 3px 0 0 #0B2545;"></span>
                Talk to our team
            </p>

            <div class="mt-4 grid grid-cols-1 gap-3 {{ [1 => 'sm:grid-cols-1', 2 => 'sm:grid-cols-2'][count($people)] ?? 'sm:grid-cols-3' }}">
                @foreach ($people as $person)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm transition hover:border-cyan/60 hover:shadow-md">
                        <p class="font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-slate">{{ $person['title'] }}</p>
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $person['phone']) }}"
                           class="mt-1.5 inline-flex items-center gap-2 text-base font-bold text-navy hover:text-link">
                            <svg class="h-4 w-4 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                            {{ $person['phone'] }}
                        </a>
                        @if ($person['email'])
                            <a href="mailto:{{ $person['email'] }}" class="mt-1 block truncate text-xs font-medium text-link hover:underline">{{ $person['email'] }}</a>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($hours)
                <p class="mt-3 text-center text-xs text-slate">{{ $hours }}</p>
            @endif
        </div>
    @endif
</div>
