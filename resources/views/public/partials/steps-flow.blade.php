{{-- Connected step flow used by Contact Us and Careers. Pass 'steps' => [['title', 'text', 'icon'], ...]. --}}
{{-- Connected step flow: on lg a dashed line runs behind the four icons (it draws left to right, each icon pops as it arrives) --}}
<div data-reveal="draw" class="relative grid grid-cols-1 gap-x-6 gap-y-7 sm:grid-cols-2 sm:gap-y-10 lg:grid-cols-4">
    {{-- phones: a dashed line runs down behind the icons --}}
    <div aria-hidden="true" class="pointer-events-none absolute bottom-6 left-[1.625rem] top-6 border-l-2 border-dashed border-cyan-ink/40 sm:hidden"></div>
    <div aria-hidden="true" class="flow-line pointer-events-none absolute left-[calc(12.5%-9px)] right-[calc(12.5%-9px)] top-[1.625rem] hidden border-t-2 border-dashed border-cyan-ink/40 lg:block"></div>

    @foreach ($steps as $step)
        @php $last = $loop->last; @endphp
        <div class="group relative flex items-start gap-4 text-left sm:block sm:text-center" style="--flow-delay: {{ $loop->index * 230 }}ms">
            <div class="flow-tile relative z-10 flex h-[3.25rem] w-[3.25rem] shrink-0 items-center justify-center rounded-xl shadow-md ring-4 ring-white sm:mx-auto sm:ring-8 transition-all duration-300 group-hover:-translate-y-1 group-hover:bg-link group-hover:text-white {{ $last ? 'flow-pulse bg-cyan text-navy' : 'bg-navy text-white' }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path @if ($last) class="flow-check" pathLength="1" @endif stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                </svg>
            </div>
            <div class="flow-text min-w-0 flex-1">
                <p class="font-mono max-sm:text-[10px] sm:mt-4 text-[11px] font-medium uppercase tracking-[0.2em] text-cyan-ink">Step {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                <h3 class="mt-1 text-base font-bold leading-snug text-navy sm:mt-1.5">{{ $step['title'] }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-slate sm:mx-auto sm:mt-2 sm:max-w-[16rem]">{{ $step['text'] }}</p>
            </div>
        </div>
    @endforeach
</div>
