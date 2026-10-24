@php
    $siteSeo = \App\Models\SiteSetting::current();
    $orgSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'Agarwal Brothers',
        'url' => \App\Support\Seo::absolute('/'),
        'logo' => \App\Support\Seo::absolute('sidebar-logo.png'),
        'description' => $siteSeo?->seo_description ?: \App\Support\Seo::defaultDescription(),
        'foundingDate' => (string) \App\Support\Company::foundedYear(),
        'telephone' => config('contact.call'),
        'email' => config('contact.departments.0.email'),
        'taxID' => config('contact.gstin'),
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => config('contact.head_office.street'),
            'addressLocality' => config('contact.head_office.city'),
            'addressRegion' => 'Rajasthan',
            'postalCode' => config('contact.head_office.postal_code'),
            'addressCountry' => 'IN',
        ],
    ]);
@endphp
<x-layouts.app :title="$siteSeo?->seo_title ?: \App\Support\Seo::DEFAULT_TITLE"
               :description="$siteSeo?->seo_description ?: \App\Support\Seo::defaultDescription()"
               :image="$siteSeo?->default_og_image ? \App\Support\Seo::storage($siteSeo->default_og_image) : null"
               :schema="$orgSchema">

    <h1 class="sr-only">Laboratory equipment, scientific instruments and chemicals supplier in India</h1>



    {{-- ===== 1. HERO CAROUSEL ===== --}}
    <section x-data="{
        active: 0,
        total: {{ $slides->count() }},
        timer: null,
        next()  { if (this.total > 1) this.active = (this.active + 1) % this.total },
        prev()  { if (this.total > 1) this.active = (this.active - 1 + this.total) % this.total },
        reset() { clearInterval(this.timer); this.start() },
        start() { if (this.total > 1) this.timer = setInterval(() => this.next(), 6000) }
    }" x-init="start()"
        class="relative w-[98%] max-w-[98vw] mx-auto mt-2 rounded-xl overflow-hidden">

        <div class="relative w-full max-sm:aspect-[4/3] sm:aspect-video 2xl:h-[600px] rounded-xl overflow-hidden bg-navy">

            @forelse ($slides as $i => $slide)
                @php $slideLink = $slide->linkTarget(); $slideProd = $slide->resolvedProduct(); @endphp

                @if ($slideLink)
                <a href="{{ $slideLink }}" @if ($slideProd) aria-label="View {{ $slideProd->name }}" @endif
                @else
                <div
                @endif
                    x-show="active === {{ $i }}"
                    x-transition:enter="transition-opacity ease-in-out duration-700"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-in-out duration-700"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 block">

                    @if ($slide->video)
                        <video autoplay muted loop playsinline preload="auto" @if ($slide->image) poster="{{ asset('storage/' . $slide->image) }}" @endif class="absolute inset-0 h-full w-full object-cover">
                            <source src="{{ asset('storage/' . $slide->video) }}" type="video/mp4">
                        </video>
                    @elseif ($slide->image)
                        <img src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->alt_text ?: ($slide->title ?: 'Agarwal Brothers laboratory equipment') }}"
                            class="img-load absolute inset-0 h-full w-full object-cover" decoding="async" {!! \App\Support\Img::attrs($slide->image) !!} onload="this.classList.add('is-loaded')">
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/10"></div>

                    @if ($slide->title || $slide->subtitle || $slideProd)
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full px-14 sm:px-12 lg:px-20">
                                <div class="max-w-xl">
                                    @if (! $slide->title && $slideProd)
                                        <span class="section-badge">Featured product</span>
                                        <h2 class="mt-2 text-[1.4rem] sm:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-lg">
                                            {{ $slideProd->name }}
                                        </h2>
                                    @elseif ($slide->title)
                                        <h2 class="text-[1.4rem] sm:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-lg">
                                            {{ $slide->title }}
                                        </h2>
                                    @endif
                                    @if ($slide->subtitle)
                                        <p class="mt-2 line-clamp-2 text-sm sm:mt-4 sm:line-clamp-none sm:text-lg text-white/90 leading-relaxed drop-shadow">
                                            {{ $slide->subtitle }}
                                        </p>
                                    @endif
                                    @if ($slideLink)
                                        <span class="btn-glass mt-4 px-5 py-2.5 text-sm sm:mt-6 sm:px-7 sm:py-3 sm:text-base">
                                            {{ $slide->linkLabel() }}
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9h6V5l7 7-7 7v-4H6V9z"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @if ($slideLink) </a> @else </div> @endif
            @empty
                <div class="absolute inset-0 flex items-center justify-center bg-navy">
                    <span class="text-white/50 text-lg">No slides added yet</span>
                </div>
            @endforelse
        </div>

        {{-- Carousel arrows --}}
        @if ($slides->count() > 1)
            {{-- Phone: two round buttons on the picture edges and dots at the bottom (the side panel is for bigger screens) --}}
            <button type="button" @click="prev(); reset()" aria-label="Previous slide"
                    class="absolute left-2.5 top-1/2 z-20 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition active:bg-white/35 sm:hidden">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" @click="next(); reset()" aria-label="Next slide"
                    class="absolute right-2.5 top-1/2 z-20 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/20 text-white backdrop-blur-sm transition active:bg-white/35 sm:hidden">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            </button>
            <div class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 items-center gap-1.5 sm:hidden" role="group" aria-label="Choose a slide">
                @foreach ($slides as $dot => $dotSlide)
                    <button type="button" @click="active = {{ $dot }}; reset()" aria-label="Slide {{ $dot + 1 }}"
                            :class="active === {{ $dot }} ? 'w-5 bg-cyan' : 'w-1.5 bg-white/60'"
                            class="h-1.5 rounded-full transition-all duration-300"></button>
                @endforeach
            </div>

            <div class="absolute right-0 top-1/2 z-20 hidden -translate-y-1/2 flex-col items-center gap-2 rounded-l-xl bg-ice p-2 shadow-md sm:flex sm:gap-3 sm:p-3">
                <button @click="prev(); reset()" class="glass-icon rounded-full p-1.5 sm:p-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>
                </button>
                <button @click="next(); reset()" class="glass-icon rounded-full p-1.5 sm:p-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
            </div>
        @endif

    </section>


    {{-- ===== 1a. PHONE ONLY: quick facts row right under the hero (numbers come from App\Support\Company) ===== --}}
    @php
        $companyYears = \App\Support\Company::yearsShown();   // 33 years shows as 30+
        $foundedYear = \App\Support\Company::foundedYear();
        // three equal capsules: key, number, label, line of text, icon (24x24 outline path)
        $capsules = [
            ['customers', config('company.customers'), 'Customers', 'All over India, from small labs to large plants.',
             'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z'],
            ['industries', config('company.industries'), 'Industry categories', 'Everyone from pharma and dairy to mining and power.',
             'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21'],
            ['brands', config('company.brands'), 'Brands', 'Global leaders across the world, under one roof.',
             'M12.75 3.03v.568c0 .334.148.65.405.864l1.068.89c.442.369.535 1.01.216 1.49l-.51.766a2.25 2.25 0 0 1-1.161.886l-.143.048a1.107 1.107 0 0 0-.57 1.664c.369.555.169 1.307-.427 1.605L9 13.125l.423 1.059a.956.956 0 0 1-1.652.928l-.679-.906a1.125 1.125 0 0 0-1.906.172L4.5 15.75l-.612.153M12.75 3.031a9 9 0 0 1 6.69 14.036m0 0-.177-.529A2.25 2.25 0 0 0 17.128 15H16.5l-.324-.324a1.453 1.453 0 0 0-2.328.377l-.036.073a1.586 1.586 0 0 1-.982.816l-.99.282c-.55.157-.894.702-.8 1.267l.073.438c.08.474.49.821.97.821.846 0 1.598.542 1.865 1.345l.215.643m-3.414 1.768A9.004 9.004 0 0 1 3.75 12c0-1.26.26-2.46.727-3.55'],
        ];
    @endphp
    <x-phone-facts data-reveal class="mx-auto mt-3 w-[98%]" />


    {{-- ===== 1b. WHAT WE DO: three capabilities (the brand count comes from App\Support\Company, so it stays correct) ===== --}}
    @php
        $capabilities = [
            ['01', 'Supply', 'Authorised supply of lab equipment and chemicals from ' . \App\Support\Company::plus('brands') . ' global brands.',
             'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
            ['02', 'Installation', 'On-site installation and commissioning by trained engineers.',
             'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z'],
            ['03', 'Calibration & Service', 'Calibration, repairs and genuine spares to keep labs accurate.',
             'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
        ];
    @endphp
    <section class="relative mx-auto mt-4 w-[98%] rounded-3xl bg-ice px-4 py-10 sm:px-8 sm:py-16 md:px-10 lg:px-16" aria-labelledby="capabilities-heading">
        <div data-reveal class="mb-7 flex sm:mb-10 flex-col items-center justify-center gap-3 text-center">
            <span class="section-badge">What we do</span>
            <h2 id="capabilities-heading" class="text-xl font-bold leading-tight text-navy sm:text-2xl">
                One partner, <span class="text-cyan-ink">start to finish</span>
            </h2>
        </div>

        {{-- the dashed line draws left to right behind the cards (only the bits in the gaps show), cards rise one after another --}}
        <div data-reveal="draw" class="relative mx-auto grid max-w-6xl grid-cols-1 gap-5 md:grid-cols-3 md:gap-x-8 lg:gap-x-12 xl:gap-x-20">
            <div aria-hidden="true" class="cap-line pointer-events-none absolute left-[16.667%] right-[16.667%] top-[92px] hidden h-[3px] rounded-full bg-[repeating-linear-gradient(to_right,#00B4D8_0_12px,transparent_12px_20px)] md:block"></div>

            @foreach ($capabilities as [$num, $capTitle, $capText, $capIcon])
                <div class="cap-card group relative grid grid-cols-[3rem_1fr] gap-x-4 overflow-hidden rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:block sm:p-7 transition-all duration-[350ms] hover:-translate-y-1.5 hover:border-cyan/50 hover:shadow-[0_16px_30px_-14px_rgba(11,37,69,0.35)]"
                     style="--cap-delay: {{ 150 + $loop->index * 120 }}ms">
                    <span aria-hidden="true" class="pointer-events-none absolute right-5 top-3 translate-y-2 select-none text-7xl font-extrabold leading-none text-navy opacity-0 transition-all duration-[350ms] group-hover:translate-y-0 group-hover:opacity-[.14]">{{ $num }}</span>
                    <p class="relative col-start-2 font-mono text-sm font-medium text-link">{{ $num }}</p>
                    <div class="relative col-start-1 row-span-3 row-start-1 flex h-12 w-12 sm:mt-4 sm:h-14 sm:w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-navy to-link text-white shadow-md transition-all duration-[350ms] ease-[cubic-bezier(.34,1.56,.64,1)] group-hover:-rotate-6 group-hover:scale-[1.08] group-hover:from-link group-hover:to-cyan" aria-hidden="true">
                        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $capIcon }}"/>
                        </svg>
                    </div>
                    <h3 class="relative col-start-2 mt-0.5 text-base font-bold text-navy sm:mt-5 sm:text-lg">{{ $capTitle }}</h3>
                    <p class="relative col-start-2 mt-1 text-sm leading-relaxed text-slate sm:mt-2">{{ $capText }}</p>
                </div>
            @endforeach
        </div>
    </section>


    {{-- ===== 2. WHO WE ARE ===== --}}
    <section x-data="{
        started: false,
        c: { customers: 0, industries: 0, brands: 0 },
        startCounting() {
            if (this.started) return;
            this.started = true;
            const targets = @js(collect($capsules)->mapWithKeys(fn ($cap) => [$cap[0] => $cap[1]])->all());
            const duration = 2000;
            const start = performance.now();
            const animate = (now) => {
                const p = Math.min((now - start) / duration, 1);
                const e = 1 - Math.pow(1 - p, 3);
                Object.keys(targets).forEach(k => this.c[k] = Math.floor(targets[k] * e));
                if (p < 1) requestAnimationFrame(animate);
                else Object.keys(targets).forEach(k => this.c[k] = targets[k]);
            };
            requestAnimationFrame(animate);
        }
    }"
    x-init="new IntersectionObserver(([e]) => { if (e.isIntersecting) { startCounting(); } }, { threshold: 0.2 }).observe($el)"
    class="relative w-[98%] mx-auto py-10 sm:py-16 md:px-10 lg:px-20">

        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(1200px_600px_at_20%_-10%,rgba(0,180,216,0.06),transparent),radial-gradient(1200px_600px_at_80%_110%,rgba(0,180,216,0.06),transparent)]"></div>

        <div data-reveal class="text-center flex flex-col justify-center items-center gap-3 mb-7 sm:mb-10">
            <span class="section-badge">
                Who We Are
            </span>
            <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight text-center">
                We will ensure you always get the <span class="text-cyan-ink">best results</span>
            </h2>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-center gap-6 sm:gap-8 lg:gap-12">

            {{-- Left: years of excellence (worked out from the founding year) --}}
            <div data-reveal="left" class="flex flex-col items-center justify-center w-full md:w-[42%] text-center">
                <p class="text-xs sm:text-sm uppercase tracking-[0.25em] font-semibold text-slate mb-1">Celebrating</p>

                <div class="relative my-2">
                    <span class="text-[72px] sm:text-[140px] lg:text-[160px] font-black leading-none
                                 text-transparent bg-clip-text
                                 bg-gradient-to-br from-cyan via-link to-navy
                                 drop-shadow-sm select-none">
                        {{ $companyYears }}
                    </span>
                    <span class="absolute -right-3 top-2 sm:-right-5 sm:top-5 text-cyan-ink text-2xl sm:text-4xl font-black">+</span>
                </div>

                <p class="text-base sm:text-lg font-bold text-navy uppercase tracking-[0.15em] -mt-2">Years of</p>
                <p class="text-2xl sm:text-3xl font-black text-navy uppercase tracking-[0.1em]">Excellence</p>

                <div class="mt-4 inline-flex items-center gap-2 px-5 py-1.5 rounded-full border-2 border-cyan/60">
                    <div class="h-1.5 w-1.5 rounded-full bg-cyan"></div>
                    <span class="text-xs sm:text-sm font-bold text-navy tracking-[0.2em]">SINCE {{ $foundedYear }}</span>
                    <div class="h-1.5 w-1.5 rounded-full bg-cyan"></div>
                </div>

                <p class="mt-5 text-sm text-navy/60 max-w-xs leading-relaxed">
                    Trusted partner in laboratory equipment, scientific instruments & chemicals across India.
                </p>

                <a href="/our-story"
                    class="mt-5 px-7 py-2.5 rounded-full font-semibold shadow-md text-white text-sm
                           bg-navy hover:bg-link transition-all duration-300 inline-flex items-center gap-2
                           hover:scale-105 hover:shadow-lg btn-primary">
                    Know More
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                    </svg>
                </a>
            </div>

            {{-- Vertical divider --}}
            <div data-reveal="fade" class="hidden md:flex flex-col items-center justify-between h-[296px] mx-2 relative">
                <div class="absolute w-[3px] bg-gradient-to-b from-cyan via-link to-navy h-full left-1/2 -translate-x-1/2 rounded-full z-0"></div>
                <div class="w-4 h-4 rounded-full border-[3px] border-white z-10 shadow-md bg-cyan"></div>
                <div class="w-4 h-4 rounded-full border-[3px] border-white z-10 shadow-md bg-link"></div>
                <div class="w-4 h-4 rounded-full border-[3px] border-white z-10 shadow-md bg-navy"></div>
            </div>

            {{-- Right: three equal capsules (same height as the divider on desktop: 3 x 5.5rem + 2 gaps = 296px) --}}
            <div class="flex w-full flex-col gap-3 md:w-[42%] md:gap-4">
                @foreach ($capsules as [$key, $target, $label, $text, $icon])
                    <div data-reveal="right" class="group relative flex items-center rounded-full border border-gray-100 bg-white/90 p-1.5 pr-5 shadow-sm
                                transition-all duration-300 hover:scale-[1.02] hover:shadow-md min-h-[5.5rem] md:h-[5.5rem]">
                        <div class="mr-3 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-navy to-link text-white shadow-md
                                    transition-all group-hover:from-link group-hover:to-cyan md:mr-4 md:h-[4.5rem] md:w-[4.5rem]">
                            <svg class="h-5 w-5 md:h-7 md:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline gap-1">
                                <span class="text-2xl font-bold leading-none text-navy md:text-3xl" x-text="c.{{ $key }}">0</span>
                                <span class="text-lg font-bold text-cyan-ink md:text-xl">+</span>
                            </div>
                            <h3 class="mt-0.5 text-xs font-semibold uppercase tracking-wider text-link md:text-[13px]">{{ $label }}</h3>
                            <p class="mt-0.5 text-xs leading-snug text-navy/60">{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ===== 2b. OUR CLIENTS: bento (quote card, number tile, rotating logos), right under Who we are ===== --}}
    @include('public.partials.clients-bento')


    {{-- ===== 3. OUR PRINCIPALS — Moving Brand Chain ===== --}}
    @if ($brands->count())
    <section class="py-10 sm:py-16">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-6 sm:mb-10">
                <span class="section-badge">
                    Our Principals
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    The right instrument, <span class="text-cyan-ink">from the right brand</span>
                </h2>
                <p class="mt-1 max-w-2xl text-sm text-slate">
                    Trusted partnerships with world-renowned manufacturers, bringing cutting-edge laboratory technology to India.
                </p>
            </div>

            @php
                $half = (int) ceil($brands->count() / 2);
                $rows = [
                    ['items' => $brands->take($half)->values(),  'dir' => 'left'],
                    ['items' => $brands->slice($half)->values(), 'dir' => 'right'],
                ];
            @endphp

            <div data-reveal="fade" class="relative overflow-hidden py-6 flex flex-col gap-6">

                {{-- Edge fades --}}
                <div class="absolute left-0 inset-y-0 w-6 sm:w-12 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 inset-y-0 w-6 sm:w-12 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>

                @foreach ($rows as $row)
                    @php
                        $items = $row['items'];
                        if ($items->isEmpty()) continue;
                        $repeat = (int) ceil(10 / $items->count());
                        $half_set = collect(range(1, $repeat))->flatMap(fn () => $items);
                        $duration = max(30, $half_set->count() * 4);
                    @endphp
                    <div class="brand-marquee-track brand-marquee-{{ $row['dir'] }} flex w-max gap-5"
                         style="--dur: {{ $duration }}s">
                        @foreach ([0, 1] as $copy)
                            @foreach ($half_set as $brand)
                                <a href="{{ route('brand.show', $brand->slug) }}"
                                    @if ($copy) aria-hidden="true" tabindex="-1" @endif
                                    class="shrink-0 flex items-center justify-center h-24 w-36 sm:h-28 sm:w-48 rounded-xl
                                           bg-white border border-gray-100 p-2.5 shadow-sm
                                           hover:-translate-y-2 hover:scale-110 hover:shadow-2xl hover:border-cyan/50 hover:z-20
                                           transition-all duration-300 group relative">
                                    @if ($brand->logo)
                                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}"
                                            class="img-load max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
                                    @else
                                        <span class="text-sm font-bold text-navy text-center">{{ $brand->name }}</span>
                                    @endif
                                </a>
                            @endforeach
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>
    </section>
    @endif


    {{-- ===== 4. EXPLORE OUR SCIENTIFIC VERTICALS ===== --}}
    @if ($verticals->count())
    <section class="bg-ice py-10 sm:py-14">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-5 sm:mb-8">
                <span class="section-badge">
                    Verticals
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Explore our <span class="text-cyan-ink">scientific verticals</span>
                </h2>
                <p class="max-w-2xl text-sm text-slate">
                    From research to production, discover how our solutions support every lab need.
                </p>
            </div>

            <div class="mb-2 flex justify-end sm:hidden">
                <a href="{{ route('verticals.index') }}" class="text-xs font-semibold text-link">View all &rarr;</a>
            </div>

            {{-- phone: one swipe row; from 640px the usual grid --}}
            <div class="-mx-1 flex snap-x snap-mandatory gap-2.5 overflow-x-auto px-1 py-1 [scrollbar-width:none] sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-4 sm:overflow-visible sm:p-0 lg:grid-cols-4 [&::-webkit-scrollbar]:hidden">
                @foreach ($verticals->take(8) as $vertical)
                    <a data-reveal href="{{ route('vertical.show', $vertical->slug) }}"
                        class="group flex w-[9.5rem] shrink-0 snap-start flex-col overflow-hidden rounded-xl bg-white sm:w-auto
                               border border-gray-100 shadow-sm
                               hover:shadow-lg hover:border-cyan/40 hover:-translate-y-1
                               transition-all duration-300">

                        <div class="flex-1 p-3 sm:p-4">
                            <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:gap-3">
                                <div class="h-12 w-12 shrink-0 rounded-lg bg-ice border border-gray-100 overflow-hidden
                                            flex items-center justify-center">
                                    @if ($vertical->image)
                                        <img src="{{ asset('storage/' . $vertical->image) }}" alt="{{ $vertical->name }}"
                                            class="img-load h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($vertical->image) !!} onload="this.classList.add('is-loaded')">
                                    @else
                                        <svg class="h-6 w-6 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                                        </svg>
                                    @endif
                                </div>
                                <h3 class="text-[13px] font-bold text-navy leading-snug line-clamp-3 sm:text-sm sm:line-clamp-2
                                           group-hover:text-link transition-colors duration-200">
                                    {{ $vertical->name }}
                                </h3>
                            </div>

                            @if ($vertical->description)
                                <p class="mt-3 text-xs text-slate leading-relaxed line-clamp-2 max-sm:hidden">{{ $vertical->description }}</p>
                            @endif
                        </div>

                        <div class="px-4 py-2 bg-navy text-center text-xs font-semibold text-white
                                    group-hover:bg-link transition-colors duration-300">
                            Know More
                            <svg class="inline w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>

            @if ($verticals->count() > 8)
                <div data-reveal class="mt-8 text-center">
                    <a href="{{ route('verticals.index') }}"
                        class="inline-flex items-center gap-2 rounded-full bg-navy px-7 py-2.5
                               text-sm font-semibold text-white shadow-md
                               hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                        Explore All Verticals
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                        </svg>
                    </a>
                </div>
            @endif

        </div>
    </section>
    @endif


    {{-- ===== 5. PRECISION PICKS ===== --}}
    @if ($topPicks->count())
    <section class="py-10 sm:py-14"
        x-data="{
            active: 0,
            go(i) { this.active = i }
        }">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-5 sm:mb-8">
                <span class="section-badge">
                    Precision Picks
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Explore our <span class="text-cyan-ink">top lab solutions</span>
                </h2>
                <p class="max-w-2xl text-sm text-slate">
                    Expert-curated equipment engineered for accuracy, reliability, and ease.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr_240px] lg:h-[420px] gap-5 items-stretch">

                {{-- Product selector list --}}
                <div data-reveal="left" class="order-2 lg:order-1 rounded-2xl bg-white border border-gray-100 shadow-sm p-2.5 sm:p-3
                            flex lg:flex-col gap-2 overflow-x-auto lg:overflow-x-visible lg:overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach ($topPicks as $i => $product)
                        <button type="button"
                            @click="go({{ $i }})"
                            :class="active === {{ $i }}
                                ? 'bg-cyan/10 border-cyan text-link max-sm:!border-navy max-sm:!bg-navy max-sm:!text-white max-sm:shadow-[inset_0_-2px_0_#00B4D8]'
                                : 'bg-white border-gray-100 text-navy hover:border-cyan/40'"
                            class="shrink-0 lg:shrink w-56 lg:w-full flex items-center gap-3 text-left
                                   rounded-xl border px-4 py-3 transition-all duration-200
                                   max-sm:w-auto max-sm:max-w-[13rem] max-sm:rounded-full max-sm:px-3.5 max-sm:py-1.5">
                            <span :class="active === {{ $i }} ? 'bg-cyan' : 'bg-gray-300'"
                                  class="h-2 w-2 rounded-full shrink-0 transition-colors max-sm:hidden"></span>
                            <span class="text-sm font-medium leading-snug line-clamp-2 max-sm:line-clamp-1 max-sm:text-xs max-sm:font-semibold">{{ $product->name }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Image stage --}}
                <div data-reveal="zoom" class="order-1 lg:order-2 relative rounded-2xl border border-gray-100 shadow-sm overflow-hidden
                            bg-gradient-to-br from-white via-ice to-cyan/10 min-h-[280px] sm:min-h-[380px]">
                    @foreach ($topPicks as $i => $product)
                        <a href="{{ route('product.show', $product->slug) }}"
                           :class="active === {{ $i }} ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                           class="absolute inset-0 flex items-center justify-center p-4 sm:p-6 transition-opacity duration-500">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="img-load max-h-full max-w-full mix-blend-multiply object-contain drop-shadow-xl" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($product->image) !!} onload="this.classList.add('is-loaded')">
                            @else
                                <div class="h-24 w-24 rounded-full bg-white shadow flex items-center justify-center">
                                    <svg class="h-10 w-10 text-slate/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v14.25c0 .828.672 1.5 1.5 1.5Z"/>
                                    </svg>
                                </div>
                            @endif
                        </a>
                    @endforeach

                    <span class="absolute left-4 top-4 rounded-full bg-cyan px-3 py-1 text-[10px] font-bold text-navy shadow-sm flex items-center gap-1">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        Top Pick
                    </span>
                </div>

                {{-- Details + CTA --}}
                <div data-reveal="right" class="order-3 rounded-2xl bg-white border border-gray-100 shadow-sm p-5 sm:p-6 flex flex-col justify-center text-left lg:overflow-y-auto">
                    <div class="grid">
                    @foreach ($topPicks as $i => $product)
                        <div :class="active === {{ $i }} ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                             class="[grid-area:1/1] transition-opacity duration-300">
                            @if ($product->category?->brand)
                                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-link">{{ $product->category->brand->name }}</p>
                            @endif
                            <h3 class="mt-2 text-lg font-bold text-navy leading-snug">{{ $product->name }}</h3>
                            @if ($product->category)
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-slate">{{ $product->category->name }}</p>
                            @endif
                            @if ($product->short_description)
                                <p class="mt-3 text-sm text-slate leading-relaxed line-clamp-4">{{ $product->short_description }}</p>
                            @endif
                            <a href="{{ route('product.show', $product->slug) }}"
                               class="mt-5 inline-flex items-center gap-2 rounded-full bg-navy px-6 py-2.5
                                      text-sm font-semibold text-white shadow-md
                                      hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                                Explore
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    @endforeach
                    </div>

                    <a href="{{ route('search') }}" class="mt-6 text-xs font-semibold text-link hover:text-navy transition">
                        View all products &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>
    @endif


    {{-- ===== 6. GLOBAL BRANDS MAP: switched off for now (client does not need it yet). To bring it back, change `false` to `$mapCountries->count()` below. ===== --}}
    @if (false)
    <section class="bg-ice py-12 sm:py-14">
        <div class="w-[98%] mx-auto md:px-5">

            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-4">
                <span class="section-badge">
                    Global Presence
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Global brands represented by <span class="text-cyan-ink">Agarwal Brothers</span> in India
                </h2>
                <p class="max-w-2xl text-sm text-slate">
                    Hover over a pin to see the brands from each country.
                </p>
            </div>

            <div data-reveal="zoom" x-data="brandMap(@js($mapCountries))" x-ref="wrap"
                 @keydown.escape.window="close()"
                 class="relative w-full">

                <div x-ref="map" class="w-full aspect-[2/1]"></div>

                {{-- Hover / tap card --}}
                <div x-cloak x-show="active !== null"
                     x-transition.opacity.duration.150ms
                     @mouseenter="cancelClose()" @mouseleave="scheduleClose()"
                     class="absolute z-30 w-60"
                     :style="`left:${pos.x}px; top:${pos.y}px; transform: translate(-50%, ${pos.below ? '12px' : 'calc(-100% - 12px)'});`">
                    <div class="overflow-hidden rounded-xl bg-white border border-gray-200 shadow-2xl">
                        <div class="bg-navy px-4 py-2.5 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-cyan animate-pulse"></span>
                            <span class="text-sm font-bold text-white" x-text="active !== null ? countries[active].name : ''"></span>
                            <span class="ml-auto text-[10px] font-semibold text-white/60"
                                  x-text="active !== null ? countries[active].brands.length + ' brand' + (countries[active].brands.length > 1 ? 's' : '') : ''"></span>
                        </div>
                        <div class="max-h-60 overflow-y-auto p-2">
                            <template x-for="brand in (active !== null ? countries[active].brands : [])" :key="brand.url">
                                <a :href="brand.url"
                                   class="flex items-center gap-3 rounded-lg px-2 py-2 hover:bg-ice transition-colors">
                                    <span class="h-9 w-12 shrink-0 rounded-md bg-ice border border-gray-100 flex items-center justify-center overflow-hidden p-1">
                                        <template x-if="brand.logo">
                                            <img :src="brand.logo" :alt="brand.name" class="max-h-full max-w-full object-contain">
                                        </template>
                                    </span>
                                    <span class="text-sm font-semibold text-navy" x-text="brand.name"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- No-JS / small screen list --}}
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-w-5xl mx-auto lg:hidden">
                @foreach ($mapCountries as $country)
                    <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm">
                        <h4 class="text-xs font-bold text-cyan-ink mb-1.5">{{ $country['name'] }}</h4>
                        @foreach ($country['brands'] as $b)
                            <a href="{{ $b['url'] }}" class="block text-[12px] text-navy font-medium leading-relaxed hover:text-link">{{ $b['name'] }}</a>
                        @endforeach
                    </div>
                @endforeach
            </div>

        </div>
    </section>
    @endif


    {{-- ===== 7. BLOGS ===== --}}
    @if ($blogs->count())
    <section class="py-10 sm:py-14"
        x-data="{
            active: 0,
            urls: @js($blogs->map(fn ($b) => route('insights.show', $b->slug))->values())
        }">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">
                    Blogs
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Expert perspectives | <span class="text-cyan-ink">real-world lab applications</span>
                </h2>
            </div>

            <div data-reveal="fade" class="rounded-3xl border border-gray-100 bg-white/70 p-4 sm:p-6 shadow-sm
                        grid grid-cols-1 lg:grid-cols-2 gap-6 items-center">

                {{-- Blog list (click to select) --}}
                <div class="order-2 lg:order-1 flex flex-col gap-3">
                    @foreach ($blogs as $i => $blog)
                        <button data-reveal="left" type="button" @click="active = {{ $i }}"
                            :class="active === {{ $i }}
                                ? 'border-cyan bg-cyan/10'
                                : 'border-gray-100 bg-white hover:border-cyan/40'"
                            class="w-full rounded-xl border px-5 py-3 text-center transition-colors duration-200">
                            <span :class="active === {{ $i }} ? 'text-link' : 'text-navy'"
                                  class="block text-sm sm:text-base font-semibold leading-snug line-clamp-2 min-h-[2.75rem] flex items-center justify-center transition-colors">
                                {{ $blog->title }}
                            </span>
                            <span class="mt-1 inline-flex items-center gap-1.5 text-xs text-slate">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                                {{ $blog->created_at->format('F d, Y') }}
                            </span>
                        </button>
                    @endforeach
                </div>

                {{-- Cover image + Read More --}}
                <div data-reveal="right" class="order-1 lg:order-2 flex flex-col items-center gap-5">
                    <div class="relative w-full aspect-[16/8] rounded-2xl overflow-hidden bg-gradient-to-br from-navy/10 to-cyan/20 shadow-sm">
                        @foreach ($blogs as $i => $blog)
                            <a href="{{ route('insights.show', $blog->slug) }}"
                               :class="active === {{ $i }} ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                               class="absolute inset-0 transition-opacity duration-500">
                                @if ($blog->image)
                                    <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}"
                                        class="img-load h-full w-full object-cover" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($blog->image) !!} onload="this.classList.add('is-loaded')">
                                @else
                                    <div class="h-full w-full flex items-center justify-center px-8 text-center">
                                        <span class="text-base font-bold text-navy/60 line-clamp-3">{{ $blog->title }}</span>
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <a :href="urls[active]"
                       class="inline-flex items-center gap-2 rounded-full bg-navy px-7 py-2.5
                              text-sm font-semibold text-white shadow-md
                              hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                        Read More
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div data-reveal class="mt-6 text-center">
                <a href="{{ route('insights.blogs') }}" class="text-sm font-semibold text-link hover:text-navy transition">
                    View all blogs &rarr;
                </a>
            </div>

        </div>
    </section>
    @endif


    {{-- ===== 8. NEWS & EVENTS ===== --}}
    @if ($news->count())
    <section class="bg-ice py-10 sm:py-14">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-5 sm:mb-8">
                <span class="section-badge">
                    News & Events
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Events, initiatives & <span class="text-cyan-ink">community highlights</span>
                </h2>
            </div>

            {{-- phone: one swipe row; from 640px the usual grid --}}
            <div class="-mx-1 flex snap-x snap-mandatory gap-3 overflow-x-auto px-1 py-1 [scrollbar-width:none] sm:mx-auto sm:grid sm:max-w-5xl sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:p-0 lg:grid-cols-3 [&::-webkit-scrollbar]:hidden">
                @foreach ($news as $item)
                    <div data-reveal class="group flex w-[15.5rem] shrink-0 snap-start flex-col overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm sm:w-auto
                                hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        <div class="h-1.5 bg-gradient-to-r from-navy via-link to-cyan"></div>

                        <a href="{{ route('insights.show', $item->slug) }}" class="block p-4 pb-3">
                            <div class="aspect-[3/2] w-full sm:aspect-[4/5] rounded-xl overflow-hidden bg-ice border border-gray-100
                                        flex items-center justify-center">
                                @if ($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}"
                                        class="img-load h-full w-full object-contain transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($item->image) !!} onload="this.classList.add('is-loaded')">
                                @else
                                    <span class="px-6 text-center text-base font-bold text-navy/60 line-clamp-4">{{ $item->title }}</span>
                                @endif
                            </div>
                        </a>

                        <div class="px-4 pb-5 flex flex-col items-center text-center">
                            <h3 class="text-sm font-semibold text-navy leading-snug line-clamp-2 min-h-[2.5rem]">
                                {{ $item->title }}
                            </h3>
                            <p class="mt-1 inline-flex items-center gap-1.5 text-xs text-slate">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                                </svg>
                                {{ ($item->event_date ?? $item->created_at)->format('F d, Y') }}
                            </p>
                            <a href="{{ route('insights.show', $item->slug) }}"
                               class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-navy px-5 py-2
                                      text-xs font-semibold text-white shadow
                                      hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                                Know More
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div data-reveal class="mt-8 text-center">
                <a href="{{ route('insights.news') }}" class="text-sm font-semibold text-link hover:text-navy transition">
                    View all news & events &rarr;
                </a>
            </div>

        </div>
    </section>
    @endif


    @include('public.partials.reviews-carousel')

</x-layouts.app>
