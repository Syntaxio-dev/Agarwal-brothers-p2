{{-- Cookie notice. With analytics configured it asks; without, it just informs. Reopened from the footer "Cookie settings" link. --}}
@php($analytics = filled(config('services.analytics_id')))

<div x-data x-cloak x-show="$store.consent.open"
     x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-4 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
     x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="pointer-events-none fixed inset-x-0 bottom-4 z-[70] flex justify-center px-4 lg:pl-[calc(16%+1rem)]"
     role="dialog" aria-label="Cookie notice" aria-live="polite">

    <div class="pointer-events-auto w-full max-w-3xl rounded-2xl border border-gray-200 bg-white p-5 shadow-[0_24px_60px_-20px_rgba(11,37,69,0.45)] sm:p-6"
         style="box-shadow: inset 0 -3px 0 #00B4D8, 0 24px 60px -20px rgba(11,37,69,0.45);">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <p class="flex items-center gap-2 font-mono text-[11px] font-medium uppercase tracking-[0.22em] text-link">
                    <span class="h-0.5 w-5 bg-cyan" style="box-shadow: 3px 0 0 #0B2545;"></span>
                    Cookies &amp; saved data
                </p>
                <p class="mt-2 text-sm leading-relaxed text-slate">
                    @if ($analytics)
                        We use essential cookies to keep the site working. With your permission we also use analytics cookies to see which pages help visitors.
                    @else
                        We use only essential cookies to keep the site working. Your compare list, enquiry list and recently viewed products are saved on your own device.
                    @endif
                    <a href="{{ route('privacy') }}" class="font-semibold text-link hover:underline">Read our privacy policy</a>.
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                @if ($analytics)
                    <button type="button" @click="$store.consent.set('essential')" class="btn-ghost !px-5 !py-2.5">Essential only</button>
                    <button type="button" @click="$store.consent.set('all')" class="btn-primary inline-flex items-center justify-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Accept all</button>
                @else
                    <button type="button" @click="$store.consent.set('essential')" class="btn-primary inline-flex items-center justify-center rounded-full px-7 py-2.5 text-sm font-semibold text-white">Got it</button>
                @endif
            </div>
        </div>
    </div>
</div>
