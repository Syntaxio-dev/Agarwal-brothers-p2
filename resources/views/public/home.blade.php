<x-layouts.app title="Agarwal Brothers">

    {{-- =========================================================
        HERO CAROUSEL — rounded, inside content area (Inkarp-style)
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
        class="relative w-[98%] max-w-[98vw] mx-auto mt-2 rounded-xl overflow-hidden">

        <div class="relative w-full aspect-video 2xl:h-[600px] rounded-xl overflow-hidden bg-navy">

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
                        <video autoplay muted loop playsinline preload="auto" class="absolute inset-0 h-full w-full object-cover">
                            <source src="{{ asset('storage/' . $slide->video) }}" type="video/mp4">
                        </video>
                    @elseif ($slide->image)
                        <img src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->title ?? 'Slide' }}"
                            class="absolute inset-0 h-full w-full object-cover">
                    @endif

                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/10"></div>

                    @if ($slide->title || $slide->subtitle)
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full px-8 sm:px-12 lg:px-20">
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
                                                   hover:bg-white hover:scale-105 transition-all duration-300">
                                            Explore
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9h6V5l7 7-7 7v-4H6V9z"/>
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
        </div>

        {{-- Search button overlay --}}
        <div class="absolute top-3 right-16 sm:right-20 z-20">
            <a href="{{ route('search') }}"
                class="relative h-10 sm:h-12 px-4 sm:px-6 min-w-[160px] sm:min-w-[220px]
                       rounded-xl text-sm backdrop-blur-md border-2 border-cyan shadow-md
                       text-navy flex items-center gap-2 transition-all duration-300
                       hover:ring-2 hover:ring-cyan hover:bg-white/30 group bg-white/80">
                <span class="absolute -top-2 -right-2 bg-cyan text-navy text-[10px] font-bold px-1.5 py-0.5 rounded-full animate-pulse shadow-lg">NEW</span>
                <svg class="h-4 w-4 text-navy group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <span class="text-navy font-medium group-hover:text-link transition-all">
                    Search<span class="hidden sm:inline"> for Products</span>
                </span>
            </a>
        </div>

        {{-- Carousel arrows — stacked on right edge --}}
        @if ($slides->count() > 1)
            <div class="bg-ice p-2 sm:p-3 absolute rounded-l-xl right-0 top-1/2 -translate-y-1/2 flex flex-col items-center gap-2 sm:gap-3 z-20">
                <button @click="prev(); reset()" class="bg-gray-200/70 text-navy p-1.5 sm:p-2 rounded-full hover:bg-cyan/20 shadow-md transition">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                    </svg>
                </button>
                <button @click="next(); reset()" class="bg-gray-200/70 text-navy p-1.5 sm:p-2 rounded-full hover:bg-cyan/20 shadow-md transition">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
            </div>
        @endif

    </section>


    {{-- =========================================================
        WHO WE ARE — split layout with pill stat cards
    ========================================================== --}}
    <section x-data="{
        started: false,
        stats: [
            { target: 36500, display: 0, label: 'Customers', icon: 'users', desc: 'Serving pharma, biotech, diagnostics & academia.' },
            { target: 50,    display: 0, label: 'Brands',    icon: 'building', desc: 'Global leaders across instruments & automation.' },
            { target: 10,    display: 0, label: 'Awards',    icon: 'award', desc: 'Recognized for excellence in performance.' },
            { target: 12,    display: 0, label: 'Branches',  icon: 'pin', desc: 'Pan-India reach ensuring fast, localized support.' }
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
    class="relative w-[98%] mx-auto py-12 md:px-10 lg:px-20">

        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(1200px_600px_at_20%_-10%,rgba(0,180,216,0.06),transparent),radial-gradient(1200px_600px_at_80%_110%,rgba(0,180,216,0.06),transparent)]"></div>

        <div class="text-center flex flex-col justify-center items-center gap-3 mb-8">
            <span class="px-4 py-1 text-xs sm:text-sm font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                Who We Are
            </span>
            <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight text-center">
                We Will Ensure You Always Get the <span class="text-cyan">Best Results</span>
            </h2>
        </div>

        <div class="flex flex-col md:flex-row items-center justify-center gap-10">

            {{-- Left: logo + tagline --}}
            <div class="flex flex-col items-center justify-center w-full md:w-[45%] text-center">
                <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers" class="w-full max-w-sm h-auto object-contain">
                <p class="mt-4 text-sm sm:text-base text-navy/70">
                    43+ years of trusted excellence in laboratory equipment, scientific instruments & chemicals.
                </p>
                <a href="/contact-us"
                    class="mt-6 px-6 py-2.5 rounded-full font-semibold shadow text-white text-sm
                           bg-navy hover:bg-link transition inline-flex items-center gap-2">
                    Know More
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9h6V5l7 7-7 7v-4H6V9z"/>
                    </svg>
                </a>
            </div>

            {{-- Vertical divider (desktop) --}}
            <div class="hidden md:flex flex-col items-center justify-between h-[380px] mx-4 relative">
                <div class="absolute w-1 bg-cyan h-full left-1/2 -translate-x-1/2 rounded-full z-0"></div>
                <div class="w-4 h-4 rounded-full border-4 border-white z-10 shadow-md bg-cyan"></div>
                <div class="w-4 h-4 rounded-full border-4 border-white z-10 shadow-md bg-cyan"></div>
                <div class="w-4 h-4 rounded-full border-4 border-white z-10 shadow-md bg-cyan"></div>
                <div class="w-4 h-4 rounded-full border-4 border-white z-10 shadow-md bg-cyan"></div>
            </div>

            {{-- Right: stat pills --}}
            <div class="flex flex-col gap-5 w-full md:w-[40%]">
                <template x-for="(stat, i) in stats" :key="i">
                    <div class="relative flex items-center bg-white/90 backdrop-blur rounded-full p-1 pr-4
                                hover:scale-[1.015] transition-all duration-300 group border border-ice shadow">
                        <div class="w-14 h-14 flex items-center justify-center rounded-full text-white text-2xl ml-2 mr-4 shadow-md
                                    bg-navy group-hover:bg-link transition shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <template x-if="stat.icon === 'users'">
                                    <g>
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                        <circle cx="9" cy="7" r="4"/>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    </g>
                                </template>
                                <template x-if="stat.icon === 'building'">
                                    <g>
                                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                                        <path d="M10 6h4M10 10h4M10 14h4M10 18h4"/>
                                    </g>
                                </template>
                                <template x-if="stat.icon === 'award'">
                                    <g>
                                        <path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"/>
                                        <circle cx="12" cy="8" r="6"/>
                                    </g>
                                </template>
                                <template x-if="stat.icon === 'pin'">
                                    <g>
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </g>
                                </template>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1">
                                <span class="text-2xl font-bold text-navy" x-text="stat.display.toLocaleString()"></span>
                                <span class="text-cyan font-bold">+</span>
                            </div>
                            <h3 class="uppercase text-sm text-link tracking-wider font-medium" x-text="stat.label"></h3>
                            <p class="text-xs text-navy/60 leading-tight mt-0.5 hidden sm:block" x-text="stat.desc"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>


    {{-- =========================================================
        STRATEGIC ALLIANCES — Brands grid
    ========================================================== --}}
    @if ($brands->count())
    <section class="relative w-[98%] mx-auto py-12 md:px-5 lg:px-20">

        <div class="text-center flex flex-col items-center gap-3 mb-10">
            <span class="px-4 py-1 text-xs sm:text-sm font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                Our Principals
            </span>
            <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                Strategic Alliances with <span class="text-cyan">Global Leaders</span>
            </h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 sm:gap-5">
            @foreach ($brands as $brand)
                <a href="{{ $brand->categories->first()
                        ? route('category.show', ['brand' => $brand->slug, 'category' => $brand->categories->first()->slug])
                        : '#' }}"
                    class="group flex h-24 sm:h-28 items-center justify-center rounded-xl
                           bg-ice p-4 border border-gray-100
                           hover:shadow-lg hover:border-cyan/40 hover:-translate-y-1
                           transition-all duration-300">
                    @if ($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}"
                            class="max-h-14 max-w-full object-contain transition-transform duration-300 group-hover:scale-110">
                    @else
                        <span class="text-sm font-bold text-navy text-center">{{ $brand->name }}</span>
                    @endif
                </a>
            @endforeach
        </div>

    </section>
    @endif


    {{-- =========================================================
        SCIENTIFIC VERTICALS
    ========================================================== --}}
    @if ($verticals->count())
    <section class="bg-ice py-12 sm:py-16">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div class="text-center flex flex-col items-center gap-3 mb-10">
                <span class="px-4 py-1 text-xs sm:text-sm font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                    Verticals
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Explore Our <span class="text-cyan">Scientific Verticals</span>
                </h2>
                <p class="mt-1 max-w-2xl text-sm text-slate">
                    From research to production, discover how our solutions support every lab need.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($verticals as $vertical)
                    <a href="{{ route('vertical.show', $vertical->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white
                               border border-gray-100 shadow-sm
                               hover:shadow-xl hover:border-cyan/30 hover:-translate-y-1
                               transition-all duration-300">

                        <div class="relative h-40 sm:h-44 overflow-hidden bg-gray-50">
                            @if ($vertical->image)
                                <img src="{{ asset('storage/' . $vertical->image) }}" alt="{{ $vertical->name }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <div class="h-14 w-14 rounded-full bg-cyan/10 flex items-center justify-center">
                                        <svg class="h-7 w-7 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                                        </svg>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 p-5">
                            <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors duration-200">
                                {{ $vertical->name }}
                            </h3>
                            @if ($vertical->description)
                                <p class="mt-2 text-sm text-slate line-clamp-2 leading-relaxed">{{ $vertical->description }}</p>
                            @endif
                        </div>

                        <div class="px-5 py-3 bg-navy text-center text-sm font-semibold text-white
                                    group-hover:bg-link transition-colors duration-300">
                            Know More
                            <svg class="inline w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                            </svg>
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
    <section class="py-12 sm:py-16">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div class="text-center flex flex-col items-center gap-3 mb-10">
                <span class="px-4 py-1 text-xs sm:text-sm font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                    Featured Products
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Our <span class="text-cyan">Top Picks</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($topPicks as $product)
                    <a href="{{ route('product.show', $product->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white
                               border border-gray-100 shadow-sm
                               hover:shadow-xl hover:border-cyan/30 hover:-translate-y-1
                               transition-all duration-300">

                        <div class="relative h-48 flex items-center justify-center overflow-hidden bg-ice p-6">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="text-sm text-slate">No image</div>
                            @endif

                            <span class="absolute left-3 top-3 rounded-full bg-cyan px-3 py-1
                                         text-[11px] font-bold text-navy shadow-sm">
                                Top Pick
                            </span>
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            @if ($product->category?->brand)
                                <p class="text-xs font-bold uppercase tracking-wide text-link">
                                    {{ $product->category->brand->name }}
                                </p>
                            @endif

                            <h3 class="mt-2 text-base font-bold text-navy leading-snug line-clamp-2
                                       group-hover:text-link transition-colors duration-200">
                                {{ $product->name }}
                            </h3>

                            @if ($product->short_description)
                                <p class="mt-2 text-sm text-slate line-clamp-2 leading-relaxed">{{ $product->short_description }}</p>
                            @endif

                            <div class="mt-auto pt-4">
                                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-link
                                             group-hover:text-navy transition-colors duration-200">
                                    View Product
                                    <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('search') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-navy px-8 py-3
                           text-sm font-semibold text-white shadow-md
                           hover:bg-link hover:scale-105 transition-all duration-300">
                    Explore All Products
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>
    @endif


    {{-- =========================================================
        GLOBAL BRANDS MAP — world map with hover brand tooltips
    ========================================================== --}}
    <section class="bg-ice py-12 sm:py-16 overflow-hidden"
        x-data="{
            activeRegion: null,
            regions: {
                switzerland: { name: 'Switzerland', x: 50.5, y: 32, brands: ['BUCHI', 'Labomatic'] },
                germany: { name: 'Germany', x: 51.5, y: 28, brands: ['Hettich', 'Eppendorf', 'Sartorius', 'Merck'] },
                usa: { name: 'United States', x: 22, y: 30, brands: ['Mettler Toledo', 'Thermo Fisher'] },
                japan: { name: 'Japan', x: 83, y: 32, brands: ['Shimadzu'] },
                india: { name: 'India', x: 68, y: 42, brands: ['Borosil'] }
            }
        }">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="px-4 py-1 text-xs sm:text-sm font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                    Global Presence
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Global Brands Represented by <span class="text-cyan">Agarwal Brothers</span> in India
                </h2>
            </div>

            <div class="relative w-full max-w-5xl mx-auto">

                {{-- World map SVG --}}
                <div class="relative w-full aspect-[2/1] bg-slate/5 rounded-2xl overflow-hidden border border-gray-100">
                    <svg viewBox="0 0 100 50" class="w-full h-full" xmlns="http://www.w3.org/2000/svg">

                        {{-- Simplified continents as path outlines --}}
                        <g fill="#CBD5E1" stroke="#94A3B8" stroke-width="0.15">
                            {{-- North America --}}
                            <path d="M5,8 L8,5 L12,4 L18,5 L22,4 L28,6 L32,8 L30,12 L28,16 L26,20 L24,24 L22,26 L20,30 L18,32 L16,30 L14,28 L12,26 L10,22 L8,20 L6,16 L4,14 L3,12 L4,10 Z"/>
                            {{-- South America --}}
                            <path d="M22,36 L24,34 L28,34 L30,36 L32,40 L30,44 L28,48 L26,49 L24,48 L22,46 L20,42 L20,38 Z"/>
                            {{-- Europe --}}
                            <path d="M44,6 L46,4 L48,5 L50,4 L52,5 L54,6 L56,8 L55,10 L54,12 L52,14 L50,16 L48,18 L46,16 L44,14 L42,12 L42,10 L43,8 Z"/>
                            {{-- Africa --}}
                            <path d="M44,20 L46,18 L50,18 L54,20 L56,24 L58,28 L58,32 L56,36 L54,40 L52,42 L50,44 L48,42 L46,38 L44,34 L42,30 L42,26 L42,22 Z"/>
                            {{-- Asia --}}
                            <path d="M56,4 L60,3 L66,4 L72,6 L78,8 L82,10 L86,12 L88,16 L86,20 L84,24 L80,28 L76,30 L72,32 L68,34 L64,36 L62,34 L60,30 L58,26 L56,22 L54,18 L54,14 L54,10 L55,6 Z"/>
                            {{-- India subcontinent --}}
                            <path d="M64,28 L66,26 L70,28 L72,32 L72,36 L70,40 L68,44 L66,42 L64,38 L62,34 L62,30 Z"/>
                            {{-- Australia --}}
                            <path d="M78,38 L82,36 L86,38 L90,40 L90,44 L88,46 L84,46 L80,44 L78,42 Z"/>
                            {{-- Japan --}}
                            <path d="M82,14 L84,12 L86,14 L86,18 L84,20 L82,18 Z"/>
                        </g>

                        {{-- Grid lines (subtle) --}}
                        <g stroke="#E2E8F0" stroke-width="0.05" fill="none">
                            <line x1="0" y1="25" x2="100" y2="25"/>
                            <line x1="50" y1="0" x2="50" y2="50"/>
                        </g>

                        {{-- Pin markers for each region --}}
                        <template x-for="(region, key) in regions" :key="key">
                            <g class="cursor-pointer"
                               @mouseenter="activeRegion = key"
                               @mouseleave="activeRegion = null"
                               @touchstart.prevent="activeRegion = activeRegion === key ? null : key">

                                {{-- Pulse ring --}}
                                <circle :cx="region.x" :cy="region.y" r="1.8"
                                    fill="none" stroke="#00B4D8" stroke-width="0.3" opacity="0.4">
                                    <animate attributeName="r" values="1.2;2.5;1.2" dur="2s" repeatCount="indefinite"/>
                                    <animate attributeName="opacity" values="0.6;0;0.6" dur="2s" repeatCount="indefinite"/>
                                </circle>

                                {{-- Pin dot --}}
                                <circle :cx="region.x" :cy="region.y" r="1"
                                    class="transition-all duration-200"
                                    :fill="activeRegion === key ? '#0077B6' : '#0B2545'"
                                    stroke="white" stroke-width="0.4"/>

                                {{-- Inner dot --}}
                                <circle :cx="region.x" :cy="region.y" r="0.35" fill="white"/>
                            </g>
                        </template>

                    </svg>

                    {{-- Tooltip cards (positioned via percentage) --}}
                    <template x-for="(region, key) in regions" :key="'tip-'+key">
                        <div x-show="activeRegion === key"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute z-10 bg-white rounded-xl shadow-xl border border-gray-200 p-4 min-w-[180px] max-w-[240px]
                                   pointer-events-none"
                            :style="`left: ${region.x}%; top: ${region.y * 2}%; transform: translate(-50%, -110%);`">

                            <h4 class="text-sm font-bold text-cyan mb-2" x-text="region.name"></h4>

                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="brand in region.brands" :key="brand">
                                    <div class="flex items-center justify-center h-10 bg-ice rounded-lg px-2 border border-gray-100">
                                        <span class="text-[11px] font-bold text-navy text-center leading-tight" x-text="brand"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                </div>

                {{-- Mobile fallback: show all regions as cards below map --}}
                <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 lg:hidden">
                    <template x-for="(region, key) in regions" :key="'mob-'+key">
                        <div class="bg-white rounded-xl p-3 border border-gray-100 shadow-sm text-center">
                            <h4 class="text-xs font-bold text-cyan mb-1.5" x-text="region.name"></h4>
                            <template x-for="brand in region.brands" :key="brand">
                                <p class="text-[11px] text-navy font-medium leading-relaxed" x-text="brand"></p>
                            </template>
                        </div>
                    </template>
                </div>

            </div>
        </div>
    </section>


    {{-- =========================================================
        TRUSTED CLIENTS
    ========================================================== --}}
    @if ($clients->count())
    <section class="bg-ice py-12 sm:py-16">
        <div class="w-[98%] mx-auto md:px-5 lg:px-20">

            <div class="text-center flex flex-col items-center gap-3 mb-10">
                <span class="px-4 py-1 text-xs sm:text-sm font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                    Our Clients
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Trusted by <span class="text-cyan">Industry Leaders</span>
                </h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 sm:gap-5">
                @foreach ($clients as $client)
                    <div class="flex h-24 items-center justify-center rounded-xl bg-white p-4
                                border border-gray-100 shadow-sm
                                hover:shadow-md hover:border-cyan/30 transition-all duration-300">
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
