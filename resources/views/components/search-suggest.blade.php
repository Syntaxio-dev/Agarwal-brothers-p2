{{-- Live suggestions dropdown. Goes inside a `relative` search form that uses x-data="ghostSearch()". --}}
<div x-cloak x-show="open" x-transition.opacity.duration.150ms
     class="absolute inset-x-0 top-full z-40 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white text-left shadow-[0_24px_48px_-20px_rgba(11,37,69,0.35)]"
     role="listbox">

    {{-- Loading edge --}}
    <div class="h-0.5 w-full bg-ice"><div x-show="loading" class="h-full w-1/3 animate-pulse bg-cyan"></div></div>

    <div class="max-h-[60vh] overflow-y-auto p-2">
        <template x-for="(r, i) in rows" :key="r.type + r.url">
            <div>
                <p x-show="showHeading(i)" :class="i === 0 ? 'pt-1.5' : 'pt-4'"
                   class="flex items-center gap-2 px-3 pb-1.5 font-mono text-[10px] font-medium uppercase tracking-[0.22em] text-link">
                    <span class="h-0.5 w-4 bg-cyan" style="box-shadow: 2px 0 0 #0B2545;"></span>
                    <span x-text="r.type"></span>
                </p>

                <a :href="r.url" role="option" :aria-selected="active === i" @mouseenter="active = i"
                   :class="active === i ? 'bg-ice' : ''"
                   :style="active === i ? 'box-shadow: inset 3px 0 0 #00B4D8' : ''"
                   class="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-100 bg-ice p-1">
                        <img x-show="r.image" :src="r.image" alt="" class="max-h-full max-w-full object-contain" loading="lazy">
                        <svg x-show="!r.image" class="h-4 w-4 text-slate/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/>
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-semibold transition-colors" :class="active === i ? 'text-link' : 'text-navy'">
                            <template x-for="(part, k) in parts(r.label)" :key="k">
                                <span :class="part.hit ? 'rounded-sm bg-cyan/20 text-navy' : ''" x-text="part.t"></span>
                            </template>
                        </span>
                        <span x-show="r.sub" x-text="r.sub" class="mt-0.5 block truncate font-mono text-[11px] uppercase tracking-wider text-slate"></span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 transition-all" :class="active === i ? 'translate-x-0.5 text-link' : 'text-slate/30'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                </a>
            </div>
        </template>

        <div x-show="!rows.length && !loading" class="px-3 py-6 text-center">
            <p class="text-sm font-semibold text-navy">No quick matches</p>
            <p class="mt-1 text-xs text-slate">Nothing found for “<span x-text="lastQuery"></span>”. Press Enter to search everything.</p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between gap-3 border-t border-gray-200 bg-ice px-4 py-2.5">
        <a :href="'{{ route('search') }}?q=' + encodeURIComponent(lastQuery)" class="min-w-0 truncate text-xs font-semibold text-link hover:text-navy">
            See all results for “<span x-text="lastQuery"></span>” &rarr;
        </a>
        <span class="hidden shrink-0 font-mono text-[10px] uppercase tracking-wider text-slate sm:block">&uarr; &darr; to move &middot; Enter to open</span>
    </div>
</div>
