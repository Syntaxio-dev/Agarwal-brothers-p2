{{-- Our clients, bento layout: tall navy quote card (left), number tile + rotating category list (top right),
     rotating 4-logo cluster (bottom right).
     Needs $clientWall (active clients, top clients first), $featuredReviews, $siteSetting and $categoryWheel.
     One 5-second timer turns the logo page and the quote together; a 2-second timer moves the category list.
     Both pause on hover/focus and while the review popup is open, and are off for reduced motion. --}}
@php
    $clientCount = $clientWall->count();
    $perPage = 4;
    $rotates = $clientCount > $perPage;           // fewer than 5 clients: shown as they are, no rotation
    $pageCount = $rotates ? (int) ceil($clientCount / $perPage) : 1;
    $pages = [];
    for ($p = 0; $p < $pageCount; $p++) {
        $items = [];
        for ($k = 0; $k < ($rotates ? $perPage : $clientCount); $k++) {
            $items[] = $clientWall[($p * $perPage + $k) % $clientCount];
        }
        $pages[] = $items;
    }
    $moreCount = max(0, $clientCount - $perPage);
    $statNumber = filled(optional($siteSetting)->clients_stat_number) ? $siteSetting->clients_stat_number : $clientCount . '+';
    $statLabel = filled(optional($siteSetting)->clients_stat_label) ? $siteSetting->clients_stat_label : 'Active clients';
    $reviewCount = $featuredReviews->count();
    $wheelCount = $categoryWheel->count();
    $longReview = 170;                              // reviews longer than this get "Read full review"
    $reviewData = $featuredReviews->map(fn ($r) => [
        'name' => $r->name,
        'meta' => collect([$r->designation, $r->organization])->filter()->implode(' · '),
        'content' => $r->content,
        'avatar' => $r->avatar ? asset('storage/' . $r->avatar) : null,
        'initial' => strtoupper(mb_substr($r->name, 0, 1)),
    ])->values();
@endphp

@if ($clientCount)
<section class="py-10 sm:py-12 bg-white" id="clients">
    <div class="w-[98%] mx-auto md:px-5 lg:px-20">

        <div data-reveal class="mb-6 flex flex-col items-center gap-3 text-center">
            <span class="section-badge">Our Clients</span>
            <h2 class="text-xl font-semibold leading-tight text-navy sm:text-2xl">
                The labs that rely <span class="text-cyan-ink">on us</span>
            </h2>
        </div>

        <div x-data="{
                 page: 0, pages: {{ $pageCount }},
                 rev: 0, revs: {{ $reviewCount }}, reviews: @js($reviewData), open: false,
                 cats: @js($categoryWheel), ci: {{ $wheelCount }}, snap: false,
                 paused: false, timer: null, wheelTimer: null,
                 get track() { return [...this.cats, ...this.cats, ...this.cats]; },
                 get busy() { return this.paused || this.open; },
                 init() {
                     if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                     if (this.pages > 1 || this.revs > 1) {
                         this.timer = setInterval(() => {
                             if (this.busy) return;
                             if (this.pages > 1) this.page = (this.page + 1) % this.pages;
                             if (this.revs > 1) this.rev = (this.rev + 1) % this.revs;
                         }, 5000);
                     }
                     if (this.cats.length > 1) {
                         this.wheelTimer = setInterval(() => { if (!this.busy) this.nextCat(); }, 2000);
                     }
                 },
                 nextCat() {
                     const n = this.cats.length;
                     this.ci++;
                     if (this.ci === 2 * n) {
                         // the third copy has reached the same name as the middle copy: jump back without animation
                         setTimeout(() => { this.snap = true; this.ci = n; this.$nextTick(() => requestAnimationFrame(() => { this.snap = false; })); }, 800);
                     }
                 },
                 openReview(i) { this.rev = i; this.open = true; },
                 destroy() { clearInterval(this.timer); clearInterval(this.wheelTimer); },
             }"
             @mouseenter="paused = true" @mouseleave="paused = false"
             @focusin="paused = true" @focusout="paused = false"
             @keydown.escape.window="open = false"
             x-effect="document.documentElement.classList.toggle('overflow-hidden', open)"
             class="grid gap-3 {{ $reviewCount ? 'lg:grid-cols-[1.4fr_1fr]' : 'lg:grid-cols-2' }}">

            {{-- Quote card (featured reviews, taking turns when there are several) --}}
            @if ($reviewCount)
                <div data-reveal="left" class="rounded-2xl bg-navy p-6 text-white sm:p-7 lg:row-span-2">
                    <div class="grid h-full">
                        @foreach ($featuredReviews as $i => $review)
                            <figure :class="rev === {{ $i }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                                    @if ($i > 0) :aria-hidden="rev !== {{ $i }}" @endif
                                    class="flex flex-col justify-between gap-6 transition-opacity duration-[400ms] [grid-area:1/1]">
                                <div>
                                    <svg class="h-8 w-8 text-cyan" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10H0z"/>
                                    </svg>
                                    @php $isLong = mb_strlen($review->content) > $longReview; @endphp
                                    <blockquote class="mt-4 text-base font-medium leading-relaxed sm:text-[17px] {{ $isLong ? 'line-clamp-4' : '' }}">{{ $review->content }}</blockquote>
                                    @if ($isLong)
                                        <button type="button" @click="openReview({{ $i }})"
                                                class="mt-3 inline-flex items-center gap-1.5 font-mono text-[11px] uppercase tracking-widest text-cyan transition-colors hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan">
                                            Read full review
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                        </button>
                                    @endif
                                </div>
                                <figcaption class="flex items-center gap-3 border-t border-white/15 pt-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white/10 text-sm font-bold text-white">
                                        @if ($review->avatar)
                                            <img src="{{ asset('storage/' . $review->avatar) }}" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                        @else
                                            {{ strtoupper(mb_substr($review->name, 0, 1)) }}
                                        @endif
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-bold">{{ $review->name }}</span>
                                        @if ($review->designation || $review->organization)
                                            <span class="block truncate font-mono text-[10px] uppercase tracking-widest text-cyan">{{ collect([$review->designation, $review->organization])->filter()->implode(' · ') }}</span>
                                        @endif
                                    </span>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                    @if ($reviewCount > 1)
                        <div class="mt-4 flex gap-1.5" aria-hidden="true">
                            @for ($i = 0; $i < $reviewCount; $i++)
                                <span :class="rev === {{ $i }} ? 'w-5 bg-cyan' : 'w-1.5 bg-white/30'" class="h-1.5 rounded-full transition-all duration-300"></span>
                            @endfor
                        </div>
                    @endif
                </div>
            @endif

            {{-- Number tile + rotating category list (like a picker wheel: the one in the middle is the current one) --}}
            <div data-reveal class="rounded-2xl bg-ice p-5 sm:p-6">
                <p class="text-4xl font-bold leading-none text-navy sm:text-5xl">{{ $statNumber }}</p>
                <p class="mt-2 font-mono text-[11px] uppercase tracking-widest text-cyan-ink">{{ $statLabel }}</p>

                @if ($wheelCount)
                    <div class="mt-4 border-t border-navy/10 pt-3">
                        <p class="mb-1 font-mono text-[10px] uppercase tracking-widest text-slate">Across sectors</p>
                        @if ($wheelCount === 1)
                            <p class="text-[15px] font-semibold text-navy">{{ $categoryWheel[0]['label'] }} <span class="font-mono text-[11px] font-normal text-cyan-ink">· {{ $categoryWheel[0]['count'] }}</span></p>
                        @else
                            <div class="relative h-[5.25rem] overflow-hidden [mask-image:linear-gradient(to_bottom,transparent,#000_28%,#000_72%,transparent)]" role="status" aria-live="off">
                                <span class="pointer-events-none absolute inset-x-0 top-[1.75rem] z-10 h-[1.75rem] border-y border-cyan/40" aria-hidden="true"></span>
                                <ul class="m-0 list-none p-0" :class="snap ? '' : 'transition-transform duration-700 ease-in-out'"
                                    :style="`transform: translateY(-${(ci - 1) * 1.75}rem)`">
                                    <template x-for="(c, i) in track" :key="i">
                                        <li class="flex h-[1.75rem] origin-left items-center gap-2 truncate text-[15px] transition-all duration-700 ease-in-out"
                                            :class="i === ci ? 'scale-100 font-semibold text-navy' : (Math.abs(i - ci) === 1 ? 'scale-[0.8] text-slate/70' : 'scale-[0.8] opacity-0')"
                                            :aria-hidden="i !== ci">
                                            <span class="truncate" x-text="c.label"></span>
                                            <span x-show="i === ci" class="shrink-0 font-mono text-[11px] font-normal text-cyan-ink" x-text="'· ' + c.count"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Rotating logo cluster --}}
            <div data-reveal class="rounded-2xl border border-gray-100 bg-white p-3 shadow-sm sm:p-4">
                <div class="grid">
                    @foreach ($pages as $p => $items)
                        <div :class="page === {{ $p }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                             @if ($p > 0) :aria-hidden="page !== {{ $p }}" @endif
                             class="grid grid-cols-2 content-start gap-2 transition-opacity duration-[400ms] [grid-area:1/1]">
                            @foreach ($items as $client)
                                @php $categoryLabel = optional($client->category)?->is_active ? $client->category->name : null; @endphp
                                <div role="img" aria-label="{{ $client->name }}{{ $categoryLabel ? ', ' . $categoryLabel : '' }}" title="{{ $client->name }}"
                                     class="flex h-16 items-center justify-center rounded-xl border border-gray-200 bg-white p-2.5 transition-all duration-300 hover:-translate-y-1 hover:border-cyan/50 hover:shadow-lg sm:h-[4.5rem]">
                                    @if ($client->logo)
                                        <img src="{{ asset('storage/' . $client->logo) }}" alt="" class="img-load max-h-full max-w-full object-contain"
                                             loading="lazy" decoding="async" {!! \App\Support\Img::attrs($client->logo) !!} onload="this.classList.add('is-loaded')">
                                    @else
                                        <span class="text-lg font-black text-navy/70">{{ strtoupper(mb_substr($client->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                @if ($moreCount)
                    <div class="mt-2 flex h-10 items-center justify-center rounded-xl bg-ice font-mono text-[11px] uppercase tracking-widest text-cyan-ink">
                        +{{ $moreCount }} more {{ \Illuminate\Support\Str::plural('client', $moreCount) }}
                    </div>
                @endif
            </div>

            {{-- Full review popup: the page behind it is blurred; closes with the X, a click outside or Escape --}}
            @if ($reviewCount)
                <div x-cloak x-show="open" x-transition.opacity.duration.250ms
                     class="fixed inset-0 z-[70] flex items-center justify-center p-4"
                     role="dialog" aria-modal="true" aria-label="Full review"
                     x-effect="if (open) $nextTick(() => $refs.closeReview && $refs.closeReview.focus())">
                    <div class="absolute inset-0 bg-navy/40 backdrop-blur-md" @click="open = false" aria-hidden="true"></div>
                    <div x-show="open" x-transition class="relative max-h-[85vh] w-full max-w-xl overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl sm:p-8">
                        <span class="absolute inset-x-0 top-0 h-[3px] bg-cyan" aria-hidden="true"></span>
                        <button type="button" x-ref="closeReview" @click="open = false" aria-label="Close review"
                                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full text-slate transition hover:bg-ice hover:text-navy focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                        <svg class="h-8 w-8 text-cyan" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10H0z"/>
                        </svg>
                        <p class="mt-4 text-base leading-relaxed text-navy sm:text-lg" x-text="reviews[rev] && reviews[rev].content"></p>
                        <div class="mt-6 flex items-center gap-3 border-t border-gray-100 pt-4" x-show="reviews[rev]">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-navy text-sm font-bold text-white">
                                <img x-show="reviews[rev] && reviews[rev].avatar" :src="reviews[rev] && reviews[rev].avatar" alt="" class="h-full w-full object-cover">
                                <span x-show="reviews[rev] && !reviews[rev].avatar" x-text="reviews[rev] && reviews[rev].initial"></span>
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate font-bold text-navy" x-text="reviews[rev] && reviews[rev].name"></span>
                                <span class="block truncate font-mono text-[11px] uppercase tracking-widest text-cyan-ink" x-text="reviews[rev] && reviews[rev].meta"></span>
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endif
