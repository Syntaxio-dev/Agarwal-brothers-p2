{{-- Compare basket: a small sticky panel at the bottom right, left of the floating buttons that stays open while products are picked (hidden on the compare page itself). --}}
<div x-data="{ open: true }"
     x-init="$watch('$store.compare.items.length', (n, o) => { if (n > o) open = true })">

    {{-- Limit message --}}
    <p x-cloak x-show="$store.compare.notice" x-text="$store.compare.notice" x-transition.opacity
       class="fixed right-4 top-4 z-[60] max-w-xs rounded-lg bg-navy px-4 py-2.5 text-xs font-medium text-white shadow-lg"></p>

    {{-- Panel --}}
    <aside x-cloak x-show="$store.compare.items.length && open" x-transition.opacity
           class="fixed bottom-6 right-20 z-30 w-56 overflow-hidden rounded-2xl border border-navy/15 bg-white shadow-xl sm:w-64"
           role="region" aria-label="Compare list">

        <div class="flex items-center justify-between border-b border-gray-200 bg-ice px-3.5 py-2.5">
            <p class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-link">
                Compare <span class="text-navy" x-text="$store.compare.items.length + '/' + $store.compare.max"></span>
            </p>
            <button type="button" @click="open = false" aria-label="Minimise compare list" title="Minimise"
                    class="flex h-6 w-6 items-center justify-center rounded-md text-slate hover:bg-white hover:text-navy">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
            </button>
        </div>

        <div class="max-h-[32vh] space-y-2 overflow-y-auto p-3">
            <template x-for="p in $store.compare.items" :key="p.slug">
                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-white p-1.5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-ice p-1">
                        <img x-show="p.image" :src="p.image" :alt="p.name" class="max-h-full max-w-full object-contain">
                        <span x-show="!p.image" class="font-mono text-[10px] font-semibold text-navy" x-text="p.name.slice(0, 3).toUpperCase()"></span>
                    </div>
                    <a :href="'/products/' + p.slug" class="line-clamp-2 min-w-0 flex-1 text-xs font-semibold leading-snug text-navy hover:text-link" x-text="p.name"></a>
                    <button type="button" @click="$store.compare.remove(p.slug)" :aria-label="'Remove ' + p.name"
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-slate hover:bg-ice hover:text-navy">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>

        <div class="space-y-2 border-t border-gray-200 bg-ice p-3">
            <p x-show="$store.compare.items.length === 1" class="text-center text-[11px] text-slate">Add one more to compare.</p>
            <a :href="$store.compare.items.length > 1 ? $store.compare.url : null"
               :class="$store.compare.items.length < 2 ? 'pointer-events-none opacity-50' : ''"
               class="btn-primary flex w-full items-center justify-center rounded-full px-4 py-2.5 text-xs font-semibold text-white">Compare now</a>
            <button type="button" @click="$store.compare.clear()"
                    class="w-full text-center text-[11px] font-semibold text-slate hover:text-navy">Cancel &amp; clear all</button>
        </div>
    </aside>

    {{-- Small tab shown when the panel is minimised --}}
    <button type="button" x-cloak x-show="$store.compare.items.length && !open" x-transition.opacity @click="open = true"
            aria-label="Open compare list"
            class="fixed bottom-6 right-20 z-30 flex items-center gap-2 rounded-xl bg-navy px-3.5 py-2.5 text-white shadow-lg transition hover:bg-link"
            style="box-shadow: inset 0 -2px 0 #00B4D8;">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
        </svg>
        <span class="font-mono text-[11px] font-medium uppercase tracking-[0.16em]">Compare</span>
        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-cyan font-mono text-[11px] font-semibold text-navy" x-text="$store.compare.items.length"></span>
    </button>
</div>
