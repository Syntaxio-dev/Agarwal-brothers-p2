{{-- $faqs: [['question' => ..., 'answer' => ...], ...]; $title optional --}}
@php $faqs = collect($faqs ?? [])->filter(fn ($f) => filled($f['question'] ?? null) && filled($f['answer'] ?? null))->values(); @endphp
@if ($faqs->count())
    <section class="mt-14" x-data="{ open: null }">
        <div class="text-center">
            <span class="section-badge">FAQs</span>
            <h2 class="mt-3 text-xl sm:text-2xl font-bold text-navy">{{ $title ?? 'Frequently Asked Questions' }}</h2>
        </div>

        <div class="mx-auto mt-8 max-w-4xl space-y-3">
            @foreach ($faqs as $i => $faq)
                <div class="overflow-hidden rounded-xl border bg-white transition-colors"
                     :class="open === {{ $i }} ? 'border-cyan/60 shadow-md' : 'border-gray-200'">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                            class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
                        <span class="text-sm sm:text-base font-semibold text-navy">{{ $faq['question'] }}</span>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-gray-200 text-link transition-transform duration-300"
                              :class="open === {{ $i }} ? 'rotate-180 bg-navy text-white border-navy' : ''">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                        </span>
                    </button>
                    <div x-show="open === {{ $i }}" x-cloak x-transition.opacity.duration.200ms>
                        <p class="border-t border-gray-100 px-5 py-4 text-sm leading-relaxed text-slate">{{ $faq['answer'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
