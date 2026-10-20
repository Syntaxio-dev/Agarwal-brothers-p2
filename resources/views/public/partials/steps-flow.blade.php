{{-- Connected step flow used by Contact Us and Careers. Pass 'steps' => [['title', 'text', 'icon'], ...]. --}}
{{-- Connected step flow: on lg a dashed line runs behind the four icons (it draws left to right, each icon pops as it arrives) --}}
<div data-reveal="draw" class="relative grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
    <div aria-hidden="true" class="flow-line pointer-events-none absolute left-[calc(12.5%-9px)] right-[calc(12.5%-9px)] top-[1.625rem] hidden border-t-2 border-dashed border-cyan-ink/40 lg:block"></div>

    @foreach ($steps as $step)
        @php $last = $loop->last; @endphp
        <div class="group relative text-center" style="--flow-delay: {{ $loop->index * 230 }}ms">
            <div class="flow-tile relative z-10 mx-auto flex h-[3.25rem] w-[3.25rem] items-center justify-center rounded-xl shadow-md ring-8 ring-white transition-all duration-300 group-hover:-translate-y-1 group-hover:bg-link group-hover:text-white {{ $last ? 'flow-pulse bg-cyan text-navy' : 'bg-navy text-white' }}">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <path @if ($last) class="flow-check" pathLength="1" @endif stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                </svg>
            </div>
            <div class="flow-text">
                <p class="mt-4 font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-cyan-ink">Step {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                <h3 class="mt-1.5 text-base font-bold leading-snug text-navy">{{ $step['title'] }}</h3>
                <p class="mx-auto mt-2 max-w-[16rem] text-sm leading-relaxed text-slate">{{ $step['text'] }}</p>
            </div>
        </div>
    @endforeach
</div>
