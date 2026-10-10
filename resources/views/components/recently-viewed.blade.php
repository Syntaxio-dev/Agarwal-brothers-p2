@props(['current' => null])
{{-- Small card (bottom left) reminding the visitor of the last product they looked at. --}}
<div x-data="{ show: false, p: null, slug: @js($current) }"
     x-init="p = $store.recent.latest(slug); if (p) setTimeout(() => show = true, 1200)"
     x-cloak x-show="show && p && $store.recent.dismissed !== p.slug"
     x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed bottom-6 left-4 z-30 w-[min(20rem,calc(100vw-6.5rem))] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-[0_24px_48px_-20px_rgba(11,37,69,0.35)] lg:left-[calc(16%+1.5rem)]"
     role="complementary" aria-label="Recently viewed product">

    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5">
        <p class="flex items-center gap-2 font-mono text-[10px] font-medium uppercase tracking-[0.22em] text-link">
            <span class="h-0.5 w-4 bg-cyan" style="box-shadow: 2px 0 0 #0B2545;"></span>
            Recently viewed
        </p>
        <button type="button" @click="$store.recent.dismiss(p.slug)" aria-label="Close"
                class="flex h-6 w-6 items-center justify-center rounded-md text-slate hover:bg-ice hover:text-navy">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <template x-if="p">
        <div>
            <a :href="'/products/' + p.slug" class="flex items-center gap-3 px-4 py-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-ice p-1.5">
                    <img x-show="p.image" :src="p.image" :alt="p.name" class="max-h-full max-w-full object-contain">
                </span>
                <span class="min-w-0">
                    <span x-show="p.brand" x-text="p.brand" class="block truncate font-mono text-[10px] font-medium uppercase tracking-[0.16em] text-link"></span>
                    <span x-text="p.name" class="line-clamp-2 text-sm font-bold leading-snug text-navy"></span>
                </span>
            </a>

            <div class="grid grid-cols-2 gap-2 px-4 pb-4">
                <a :href="'/products/' + p.slug" class="btn-primary inline-flex items-center justify-center rounded-full px-3 py-2 text-xs font-semibold text-white">View product</a>
                <a :href="'/products/' + p.slug + '#enquiry-form'" class="btn-ghost justify-center !px-3 !py-2 !text-xs">Enquire</a>
            </div>
        </div>
    </template>
</div>
