<x-layouts.app title="Agarwal Brothers">

    {{-- =========================================================
        HERO CAROUSEL  — full-width, with text overlay
    ========================================================== --}}
    <section x-data="{
        active: 0,
        total: {{ $slides->count() }},
        timer: null,
        next()  { if (this.total > 1) this.active = (this.active + 1) % this.total },
        prev()  { if (this.total > 1) this.active = (this.active - 1 + this.total) % this.total },
        reset() { clearInterval(this.timer); this.start() },
        start() { if (this.total > 1) this.timer = setInterval(() => this.next(), 6000) }
    }" x-init="start()"
        class="relative w-full h-[50vh] sm:h-[60vh] lg:h-[85vh] min-h-[360px] overflow-hidden bg-navy">

        {{-- Slides --}}
        @forelse ($slides as $i => $slide)
            <div x-show="active === {{ $i }}"
                x-transition:enter="transition-opacity ease-in-out duration-700"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in-out duration-700"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0">

                @if ($slide->video)
                    <video autoplay muted loop playsinline preload="auto"
                        class="absolute inset-0 h-full w-full object-cover">
                        <source src="{{ asset('storage/' . $slide->video) }}" type="video/mp4">
                    </video>
                @elseif ($slide->image)
                    <img src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->title ?? 'Slide' }}"
                        class="absolute inset-0 h-full w-full object-cover">
                @endif

                {{-- Gradient overlay --}}
                <div class="absolute inset-0 bg-gradient-to-r from-navy/80 via-navy/40 to-transparent"></div>

                {{-- Slide text --}}
                @if ($slide->title || $slide->subtitle)
                    <div class="absolute inset-0 flex items-center">
                        <div class="mx-auto w-full max-w-7xl px-6 sm:px-8 lg:px-10">
                            <div class="max-w-xl">
                                @if ($slide->title)
                                    <h2 class="text-2xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight drop-shadow-lg">
                                        {{ $slide->title }}
                                    </h2>
                                @endif
                                @if ($slide->subtitle)
                                    <p class="mt-4 text-base sm:text-lg text-white/90 leading-relaxed drop-shadow">
                                        {{ $slide->subtitle }}
                                    </p>
                                @endif
                                @if ($slide->link_url)
                                    <a href="{{ $slide->link_url }}"
                                        class="mt-6 inline-flex items-center gap-2 rounded-full bg-cyan px-7 py-3
                                               text-sm font-bold text-navy shadow-lg
                                               hover:bg-white transition-all duration-300">
                                        Explore
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="absolute inset-0 flex items-center justify-center bg-navy">
                <span class="text-white/50 text-lg">No slides added yet</span>
            </div>
        @endforelse

        {{-- Arrow Controls --}}
        @if ($slides->count() > 1)
            <button @click="prev(); reset()" type="button"
                class="absolute left-4 sm:left-6 top-1/2 z-30 -translate-y-1/2
                       flex h-11 w-11 items-center justify-center rounded-full
                       bg-white/20 text-white backdrop-blur-sm
                       hover:bg-white hover:text-navy transition-all duration-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
                </svg>
            </button>
            <button @click="next(); reset()" type="button"
                class="absolute right-4 sm:right-6 top-1/2 z-30 -translate-y-1/2
                       flex h-11 w-11 items-center justify-center rounded-full
                       bg-white/20 text-white backdrop-blur-sm
                       hover:bg-white hover:text-navy transition-all duration-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
                </svg>
            </button>
        @endif

        {{-- Dots --}}
        @if ($slides->count() > 1)
            <div class="absolute bottom-6 left-1/2 z-30 flex -translate-x-1/2 gap-2">
                @foreach ($slides as $i => $slide)
                    <button @click="active = {{ $i }}; reset()" type="button"
                        class="h-2.5 rounded-full transition-all duration-500"
                        :class="active === {{ $i }} ? 'w-8 bg-cyan' : 'w-2.5 bg-white/60 hover:bg-white'">
                    </button>
                @endforeach
            </div>
        @endif

    </section>


    {{-- =========================================================
        WHO WE ARE — stats counter section
    ========================================================== --}}
    <section x-data="{
        started: false,
        stats: [
            { target: 43,    display: 0, label: 'Years', desc: 'Of trusted excellence in scientific solutions' },
            { target: 36500, display: 0, label: 'Customers', desc: 'Serving pharma, biotech, diagnostics & academia' },
            { target: 50,    display: 0, label: 'Brands', desc: 'Global leaders in instruments & automation' },
            { target: 10,    display: 0, label: 'Awards', desc: 'Recognized for performance & customer satisfaction' },
            { target: 12,    display: 0, label: 'Branches', desc: 'Pan-India reach with fast, localized support' }
        ],
        startCounting() {
            if (this.started) return;
            this.started = true;
            const duration = 2000;
            const start = performance.now();
            const animate = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                this.stats.forEach(s => s.display = Math.floor(s.target * eased));
                if (progress < 1) requestAnimationFrame(animate);
                else this.stats.forEach(s => s.display = s.target);
            };
            requestAnimationFrame(animate);
        }
    }"
    x-init="new IntersectionObserver(([e]) => { if (e.isIntersecting) { startCounting(); } }, { threshold: 0.2 }).observe($el)"
    class="relative bg-ice py-16 sm:py-20 lg:py-24">

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="text-center mb-12 lg:mb-16">
                <span class="inline-block rounded-full bg-cyan/10 px-5 py-1.5 text-xs font-bold uppercase tracking-widest text-link">
                    Who We Are
                </span>
                <h2 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">
                    We Will Ensure You Always Get the
                    <span class="text-cyan">Best Results</span>
                </h2>
            </div>

            {{-- Stats Grid --}}
            <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-5 lg:gap-8">
                <template x-for="(stat, i) in stats" :key="i">
                    <div class="group rounded-2xl bg-white p-5 sm:p-6 shadow-sm ring-1 ring-gray-100
                                hover:shadow-lg hover:ring-cyan/30 transition-all duration-300 text-center">
                        <div class="text-3xl sm:text-4xl font-extrabold text-navy">
                            <span x-text="stat.display.toLocaleString()"></span><span class="text-cyan">+</span>
                        </div>
                        <div class="mt-2 text-sm font-bold uppercase tracking-wide text-link" x-text="stat.label"></div>
                        <p class="mt-2 text-xs leading-5 text-slate hidden sm:block" x-text="stat.desc"></p>
                    </div>
                </template>
            </div>

            {{-- CTA --}}
            <div class="mt-10 text-center">
                <a href="/verticals"
                    class="inline-flex items-center gap-2 rounded-full bg-link px-7 py-3
                           text-sm font-semibold text-white shadow-md
                           hover:bg-navy transition-all duration-300">
                    Know More About Us
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                    </svg>
                </a>
            </div>

        </div>
    </section>


    {{-- =========================================================
        STRATEGIC ALLIANCES — Brands logo grid
    ========================================================== --}}
    @if ($brands->count())
    <section class="bg-white py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="text-center mb-12 lg:mb-16">
                <span class="inline-block rounded-full bg-cyan/10 px-5 py-1.5 text-xs font-bold uppercase tracking-widest text-link">
                    Our Principals
                </span>
                <h2 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">
                    Strategic Alliances with
                    <span class="text-cyan">Global Scientific Leaders</span>
                </h2>
            </div>

            {{-- Brand Logos Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-6">
                @foreach ($brands as $brand)
                    <a href="{{ $brand->categories->first()
                            ? route('category.show', ['brand' => $brand->slug, 'category' => $brand->categories->first()->slug])
                            : '#' }}"
                        class="group flex h-24 sm:h-28 items-center justify-center rounded-xl
                               bg-ice p-4 ring-1 ring-gray-100
                               hover:shadow-lg hover:ring-cyan/40 hover:-translate-y-1
                               transition-all duration-300">
                        @if ($brand->logo)
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}"
                                class="max-h-14 max-w-full object-contain
                                       transition-transform duration-300 group-hover:scale-110">
                        @else
                            <span class="text-sm font-bold text-navy text-center">{{ $brand->name }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

        </div>
    </section>
    @endif


    {{-- =========================================================
        SCIENTIFIC VERTICALS
    ========================================================== --}}
    @if ($verticals->count())
    <section class="bg-ice py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="text-center mb-12 lg:mb-16">
                <span class="inline-block rounded-full bg-cyan/10 px-5 py-1.5 text-xs font-bold uppercase tracking-widest text-link">
                    Verticals
                </span>
                <h2 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">
                    Explore Our
                    <span class="text-cyan">Scientific Verticals</span>
                </h2>
                <p class="mt-3 mx-auto max-w-2xl text-sm sm:text-base text-slate">
                    From research to production, discover how our solutions support every lab need.
                </p>
            </div>

            {{-- Verticals Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($verticals as $vertical)
                    <a href="{{ route('vertical.show', $vertical->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white
                               ring-1 ring-gray-100 shadow-sm
                               hover:shadow-xl hover:ring-cyan/30 hover:-translate-y-1
                               transition-all duration-300">

                        {{-- Image --}}
                        <div class="relative h-40 sm:h-44 overflow-hidden bg-gray-50">
                            @if ($vertical->image)
                                <img src="{{ asset('storage/' . $vertical->image) }}" alt="{{ $vertical->name }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <div class="h-14 w-14 rounded-full bg-cyan/10 flex items-center justify-center">
                                        <svg class="h-7 w-7 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 p-5">
                            <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors duration-200">
                                {{ $vertical->name }}
                            </h3>
                            @if ($vertical->description)
                                <p class="mt-2 text-sm text-slate line-clamp-2 leading-relaxed">
                                    {{ $vertical->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Bottom bar --}}
                        <div class="px-5 py-3 bg-navy text-center text-sm font-semibold text-white
                                    group-hover:bg-cyan group-hover:text-navy transition-colors duration-300">
                            Know More →
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif


    {{-- =========================================================
        TOP PICKS / FEATURED PRODUCTS
    ========================================================== --}}
    @if ($topPicks->count())
    <section class="bg-white py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="text-center mb-12 lg:mb-16">
                <span class="inline-block rounded-full bg-cyan/10 px-5 py-1.5 text-xs font-bold uppercase tracking-widest text-link">
                    Featured Products
                </span>
                <h2 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">
                    Our <span class="text-cyan">Top Picks</span>
                </h2>
                <p class="mt-3 mx-auto max-w-2xl text-sm sm:text-base text-slate">
                    Explore some of our featured scientific instruments and laboratory solutions.
                </p>
            </div>

            {{-- Product Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($topPicks as $product)
                    <a href="{{ route('product.show', $product->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white
                               ring-1 ring-gray-100 shadow-sm
                               hover:shadow-xl hover:ring-cyan/30 hover:-translate-y-1
                               transition-all duration-300">

                        {{-- Product Image --}}
                        <div class="relative h-52 flex items-center justify-center overflow-hidden bg-ice p-6">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="max-h-full max-w-full object-contain
                                           transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="text-sm text-slate">No image</div>
                            @endif

                            <span class="absolute left-3 top-3 rounded-full bg-cyan px-3 py-1
                                         text-[11px] font-bold text-navy shadow-sm">
                                Top Pick
                            </span>
                        </div>

                        {{-- Product Info --}}
                        <div class="flex flex-1 flex-col p-5">

                            @if ($product->category?->brand)
                                <p class="text-xs font-bold uppercase tracking-wide text-cyan">
                                    {{ $product->category->brand->name }}
                                </p>
                            @endif

                            <h3 class="mt-2 text-base font-bold text-navy leading-snug line-clamp-2
                                       group-hover:text-link transition-colors duration-200">
                                {{ $product->name }}
                            </h3>

                            @if ($product->short_description)
                                <p class="mt-2 text-sm text-slate line-clamp-2 leading-relaxed">
                                    {{ $product->short_description }}
                                </p>
                            @endif

                            <div class="mt-auto pt-4">
                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-link
                                             group-hover:text-navy transition-colors duration-200">
                                    View Product
                                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- View All CTA --}}
            <div class="mt-10 text-center">
                <a href="{{ route('search') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-navy px-8 py-3
                           text-sm font-semibold text-white shadow-md
                           hover:bg-link transition-all duration-300">
                    Explore All Products
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" />
                    </svg>
                </a>
            </div>
        </div>
    </section>
    @endif


    {{-- =========================================================
        TRUSTED CLIENTS
    ========================================================== --}}
    @if ($clients->count())
    <section class="bg-ice py-16 sm:py-20 lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="text-center mb-12 lg:mb-16">
                <span class="inline-block rounded-full bg-cyan/10 px-5 py-1.5 text-xs font-bold uppercase tracking-widest text-link">
                    Our Clients
                </span>
                <h2 class="mt-4 text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">
                    Trusted by <span class="text-cyan">Industry Leaders</span>
                </h2>
            </div>

            {{-- Client Logos --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-6">
                @foreach ($clients as $client)
                    <div class="flex h-24 items-center justify-center rounded-xl bg-white p-4
                                ring-1 ring-gray-100 shadow-sm
                                hover:shadow-md hover:ring-cyan/30 transition-all duration-300">
                        @if ($client->logo)
                            <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}"
                                class="max-h-14 max-w-full object-contain">
                        @else
                            <span class="text-sm font-medium text-slate text-center">{{ $client->name }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

</x-layouts.app>
