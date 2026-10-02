<x-layouts.app title="Agarwal Brothers">

    {{-- =========================================================
        HERO CAROUSEL
    ========================================================== --}}

    <section x-data="{
            active: 0,
            total: {{ $slides->count() }},
            timer: null,

            next() {
                if (this.total > 1) {
                    this.active = (this.active + 1) % this.total
                }
            },

            prev() {
                if (this.total > 1) {
                    this.active = (this.active - 1 + this.total) % this.total
                }
            },

            start() {
                if (this.total > 1) {
                    this.timer = setInterval(() => this.next(), 15000)
                }
            }
        }" x-init="start()"
        class="relative mx-[5px] mt-[5px] h-[calc(100vh-10px)] min-h-[520px] overflow-hidden rounded-xl bg-ice">

        {{-- Slides --}}
        @forelse ($slides as $i => $slide)

        <div x-show="active === {{ $i }}" x-transition:enter="transition-opacity ease-in-out duration-1000"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in-out duration-1000" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="absolute inset-0">

            @if ($slide->video)

            <video autoplay muted loop playsinline preload="auto" class="absolute inset-0 h-full w-full object-cover">
                <source src="{{ asset('storage/' . $slide->video) }}" type="video/mp4">
            </video>

            @elseif ($slide->image)

            <img src="{{ asset('storage/' . $slide->image) }}" alt="{{ $slide->title }}"
                class="absolute inset-0 h-full w-full object-cover">

            @endif

            {{-- Very light overlay --}}
            <div class="absolute inset-0 bg-white/5"></div>

        </div>

        @empty

        <div class="absolute inset-0 flex items-center justify-center bg-ice">
            <span class="text-slate">
                No slides added yet
            </span>
        </div>

        @endforelse


        {{-- =====================================================
            TOP FLOATING CONTROLS
        ====================================================== --}}

        <div
            class="absolute left-1/2 top-4 z-30 flex w-[calc(100%-24px)] -translate-x-1/2 items-center justify-center gap-4">

            {{-- Follow Us --}}
            <div class="flex shrink-0 items-center gap-3 rounded-full bg-link px-5 py-2 text-white shadow-lg">

                <span class="font-semibold">
                    Follow Us
                </span>

                <span
                    class="flex h-7 w-7 items-center justify-center rounded-full bg-white text-sm font-bold text-link">
                    in
                </span>

            </div>


            {{-- Search --}}
            <form action="{{ route('search') }}" method="GET"
                class="flex w-full max-w-[360px] items-center rounded-xl border-2 border-cyan bg-white/95 px-4 py-2 shadow-lg backdrop-blur-sm">

                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5 shrink-0 text-cyan" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                </svg>

                <input type="text" name="q" placeholder="Search for Products"
                    class="min-w-0 flex-1 border-0 bg-transparent text-sm text-navy outline-none focus:border-0 focus:outline-none focus:ring-0">

                <span class="ml-2 rounded-full bg-cyan px-2 py-0.5 text-[10px] font-bold text-navy">
                    NEW
                </span>

            </form>

        </div>


        {{-- =====================================================
            SLIDER ARROWS
        ====================================================== --}}

        @if ($slides->count() > 1)

        <button @click="prev()" type="button"
            class="absolute right-4 top-1/2 z-30 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/80 text-navy shadow-md backdrop-blur-sm transition hover:scale-105 hover:bg-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7" />
            </svg>
        </button>


        <button @click="next()" type="button"
            class="absolute right-4 top-[calc(50%+50px)] z-30 flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-navy shadow-md backdrop-blur-sm transition hover:scale-105 hover:bg-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" />
            </svg>
        </button>

        @endif


        {{-- =====================================================
            SLIDE DOTS
        ====================================================== --}}

        @if ($slides->count() > 1)

        <div
            class="absolute bottom-4 left-1/2 z-30 flex -translate-x-1/2 gap-2 rounded-full bg-navy/20 px-3 py-2 backdrop-blur-sm">

            @foreach ($slides as $i => $slide)

            <button @click="active = {{ $i }}" type="button" class="h-2 w-2 rounded-full transition-all duration-500"
                :class="
                            active === {{ $i }}
                                ? 'scale-125 bg-cyan'
                                : 'bg-white/80'
                        "></button>

            @endforeach

        </div>

        @endif

    </section>


    {{-- =========================================================
    ABOUT / COMPANY EXPERIENCE
========================================================== --}}

    <section x-data="{
        started: false,

        experience: 43,
        customers: 36500,
        brands: 50,
        awards: 10,
        branches: 12,

        displayExperience: 0,
        displayCustomers: 0,
        displayBrands: 0,
        displayAwards: 0,
        displayBranches: 0,

        startCounting() {

            if (this.started) {
                return
            }

            this.started = true

            const duration = 1800
            const startTime = performance.now()

            const animate = (currentTime) => {

                const elapsed = currentTime - startTime
                const progress = Math.min(elapsed / duration, 1)

                const eased = 1 - Math.pow(1 - progress, 3)

                this.displayExperience =
                    Math.floor(this.experience * eased)

                this.displayCustomers =
                    Math.floor(this.customers * eased)

                this.displayBrands =
                    Math.floor(this.brands * eased)

                this.displayAwards =
                    Math.floor(this.awards * eased)

                this.displayBranches =
                    Math.floor(this.branches * eased)

                if (progress < 1) {
                    requestAnimationFrame(animate)
                } else {

                    this.displayExperience = this.experience
                    this.displayCustomers = this.customers
                    this.displayBrands = this.brands
                    this.displayAwards = this.awards
                    this.displayBranches = this.branches

                }
            }

            requestAnimationFrame(animate)
        }
    }" x-init="
        const observer = new IntersectionObserver(
            (entries) => {

                if (entries[0].isIntersecting) {

                    startCounting()
                    observer.disconnect()

                }

            },
            {
                threshold: 0.25
            }
        )

        observer.observe($el)
    " class="mx-[5px] mt-5 overflow-hidden rounded-xl bg-[#fffafd]">

        {{-- =====================================================
        SECTION HEADING
    ====================================================== --}}

        <div class="px-5 pt-14 text-center sm:pt-16">

            <span class="inline-flex border border-cyan px-5 py-1.5 text-sm font-medium text-navy">
                WHO WE ARE
            </span>

            <h2 class="mt-5 text-2xl font-normal text-link sm:text-3xl lg:text-[28px]">
                We Will Ensure You Always Get the Best Results
            </h2>

        </div>


        {{-- =====================================================
        MAIN ABOUT AREA
    ====================================================== --}}

        <div
            class="relative mx-auto grid max-w-6xl grid-cols-1 px-5 pb-14 pt-8 sm:px-10 lg:grid-cols-2 lg:px-12 lg:pb-16 lg:pt-5">


            {{-- =================================================
            CENTER VERTICAL LINE
        ================================================== --}}

            <div class="pointer-events-none absolute bottom-20 left-1/2 top-16 hidden -translate-x-1/2 lg:block">

                <div class="relative h-full w-[3px] bg-cyan/80">

                    {{-- TOP DOT --}}
                    <span
                        class="absolute left-1/2 top-0 h-4 w-4 -translate-x-1/2 rounded-full border-4 border-white bg-cyan shadow"></span>

                    {{-- MIDDLE DOT 1 --}}
                    <span
                        class="absolute left-1/2 top-[25%] h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-white bg-cyan shadow"></span>

                    {{-- MIDDLE DOT 2 --}}
                    <span
                        class="absolute left-1/2 top-[50%] h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-white bg-cyan shadow"></span>

                    {{-- MIDDLE DOT 3 --}}
                    <span
                        class="absolute left-1/2 top-[75%] h-4 w-4 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-white bg-cyan shadow"></span>

                    {{-- BOTTOM DOT --}}
                    <span
                        class="absolute bottom-0 left-1/2 h-4 w-4 -translate-x-1/2 rounded-full border-4 border-white bg-cyan shadow"></span>

                </div>

            </div>


            {{-- =================================================
            LEFT SIDE
        ================================================== --}}

            <div class="flex flex-col items-center justify-center px-2 lg:pr-16">

                {{-- 43 YEARS IMAGE --}}

                <div class="flex w-full justify-center">

                    <img src="{{ asset('images/43-years.png') }}" alt="Agarwal Brothers - 43 Years of Excellence"
                        class="h-auto w-full max-w-[520px] object-contain">

                </div>


                {{-- KNOW MORE BUTTON --}}

                <a href="/our-story"
                    class="mt-4 inline-flex items-center gap-3 rounded-full bg-link px-7 py-3 text-sm font-semibold text-white shadow-md transition duration-300 hover:scale-105 hover:bg-navy">

                    Know More

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6" />
                    </svg>

                </a>

            </div>


            {{-- =================================================
            RIGHT SIDE STATS
        ================================================== --}}

            <div class="mt-10 flex flex-col justify-center gap-5 lg:mt-0 lg:pl-16">


                {{-- CUSTOMERS --}}
                <div
                    class="flex min-h-[105px] items-center gap-5 rounded-full bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200">

                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-cyan text-white">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19a6 6 0 0 0-12 0m6-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm9 8a5 5 0 0 0-4-4.9m0-3.1a3.5 3.5 0 1 0 0-7" />
                        </svg>

                    </div>

                    <div class="min-w-0">

                        <div class="flex items-baseline">

                            <span x-text="displayCustomers" class="text-2xl font-bold text-navy"></span>

                            <span class="ml-1 text-lg text-cyan">
                                +
                            </span>

                        </div>

                        <div class="text-base font-medium uppercase text-link">
                            Customers
                        </div>

                        <p class="mt-1 text-xs text-slate">
                            Serving pharma, biotech, diagnostics, academia,
                            and more.
                        </p>

                    </div>

                </div>


                {{-- BRANDS --}}
                <div
                    class="flex min-h-[105px] items-center gap-5 rounded-full bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200">

                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-cyan text-white">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 21h18M5 21V8l7-4 7 4v13M9 21v-6h6v6M8 10h.01M12 10h.01M16 10h.01" />
                        </svg>

                    </div>

                    <div class="min-w-0">

                        <div class="flex items-baseline">

                            <span x-text="displayBrands" class="text-2xl font-bold text-navy"></span>

                            <span class="ml-1 text-lg text-cyan">
                                +
                            </span>

                        </div>

                        <div class="text-base font-medium uppercase text-link">
                            Brands
                        </div>

                        <p class="mt-1 text-xs text-slate">
                            Global leaders across instruments, automation,
                            and workflows.
                        </p>

                    </div>

                </div>


                {{-- AWARDS --}}
                <div
                    class="flex min-h-[105px] items-center gap-5 rounded-full bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200">

                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-cyan text-white">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m12 3 2.2 4.5 5 .7-3.6 3.5.9 5-4.5-2.4-4.5 2.4.9-5-3.6-3.5 5-.7L12 3Z" />

                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16v5l4-2 4 2v-5" />
                        </svg>

                    </div>

                    <div class="min-w-0">

                        <div class="flex items-baseline">

                            <span x-text="displayAwards" class="text-2xl font-bold text-navy"></span>

                            <span class="ml-1 text-lg text-cyan">
                                +
                            </span>

                        </div>

                        <div class="text-base font-medium uppercase text-link">
                            Awards
                        </div>

                        <p class="mt-1 text-xs text-slate">
                            Recognized for excellence in performance and
                            customer satisfaction.
                        </p>

                    </div>

                </div>


                {{-- BRANCHES --}}
                <div
                    class="flex min-h-[105px] items-center gap-5 rounded-full bg-white px-5 py-4 shadow-sm ring-1 ring-slate-200">

                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-cyan text-white">

                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 21s7-5.1 7-11a7 7 0 1 0-14 0c0 5.9 7 11 7 11Z" />

                            <circle cx="12" cy="10" r="2.5" />
                        </svg>

                    </div>

                    <div class="min-w-0">

                        <div class="flex items-baseline">

                            <span x-text="displayBranches" class="text-2xl font-bold text-navy"></span>

                            <span class="ml-1 text-lg text-cyan">
                                +
                            </span>

                        </div>

                        <div class="text-base font-medium uppercase text-link">
                            Branches
                        </div>

                        <p class="mt-1 text-xs text-slate">
                            Pan-India reach ensuring fast, localized support.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- Strategic Alliances / Brands Section --}}
    <section class="relative mt-16 mb-16 bg-white overflow-hidden pt-16 sm:pt-20 lg:pt-24">

        {{-- Heading --}}
        <div class="text-center mb-20 px-6">
            <span class="inline-block border border-cyan text-navy text-sm font-medium px-5 py-2">
                OUR PRINCIPLES
            </span>

            <h2 class="mt-5 text-2xl sm:text-3xl lg:text-4xl font-semibold text-navy">
                Strategic Alliances with Global Scientific Leaders
            </h2>
        </div>


        {{-- =========================================================
         BRAND ORBIT AREA
         No fixed section height — content gets its full space.
         ========================================================= --}}
        <div class="relative mx-auto w-full max-w-[1100px] h-[900px] sm:h-[1000px] lg:h-[1100px]">


            {{-- =====================================================
             4 PERFECT CONCENTRIC CIRCLES
             ====================================================== --}}

            {{-- Outer Circle --}}
            <div class="absolute left-1/2 top-1/2
           -translate-x-1/2 -translate-y-1/2
           w-[850px] h-[850px]
           rounded-full
           border border-cyan/40">
            </div>

            {{-- Circle 2 --}}
            <div class="absolute left-1/2 top-1/2
           -translate-x-1/2 -translate-y-1/2
           w-[650px] h-[650px]
           rounded-full
           border border-cyan/40">
            </div>

            {{-- Circle 3 --}}
            <div class="absolute left-1/2 top-1/2
           -translate-x-1/2 -translate-y-1/2
           w-[450px] h-[450px]
           rounded-full
           border border-cyan/40">
            </div>

            {{-- Circle 4 --}}
            <div class="absolute left-1/2 top-1/2
           -translate-x-1/2 -translate-y-1/2
           w-[250px] h-[250px]
           rounded-full
           border border-cyan/40">
            </div>



            {{-- =====================================================
             CENTER AGARWAL BROTHERS LOGO
             ====================================================== --}}

            {{-- CENTER AGARWAL BROTHERS LOGO --}}
            <div class="absolute left-1/2 top-1/2
           -translate-x-1/2 -translate-y-1/2
           z-30
           w-24 h-24
           sm:w-28 sm:h-28
           lg:w-32 lg:h-32
           rounded-full
           bg-white
           shadow-[0_0_35px_rgba(0,180,216,0.25)]
           flex items-center justify-center">

                <img src="{{ asset('images/43-years.png') }}" alt="Agarwal Brothers"
                    class="w-[75%] h-[75%] object-contain">
            </div>



            {{-- =====================================================
             BRAND 1 — TOP
             ====================================================== --}}

            @if(isset($brands[0]))

            <a href="{{ $brands[0]->categories->first()
                    ? route('category.show', [
                        'brand' => $brands[0]->slug,
                        'category' => $brands[0]->categories->first()->slug
                    ])
                    : '#' }}" class="absolute
                       left-1/2 top-[30px]
                       -translate-x-1/2
                       z-20
                       w-36 h-20
                       sm:w-44 sm:h-24
                       bg-white rounded-lg
                       shadow-md
                       flex items-center justify-center
                       p-4
                       transition duration-300
                       hover:scale-105 hover:shadow-xl">

                @if($brands[0]->logo)

                <img src="{{ asset('storage/' . $brands[0]->logo) }}" alt="{{ $brands[0]->name }}"
                    class="max-w-full max-h-full object-contain">

                @else

                <span class="font-semibold text-navy text-center">
                    {{ $brands[0]->name }}
                </span>

                @endif

            </a>

            @endif



            {{-- =====================================================
             BRAND 2 — RIGHT
             ====================================================== --}}

            @if(isset($brands[1]))

            <a href="{{ $brands[1]->categories->first()
                    ? route('category.show', [
                        'brand' => $brands[1]->slug,
                        'category' => $brands[1]->categories->first()->slug
                    ])
                    : '#' }}" class="absolute
                       right-[20px] top-1/2
                       -translate-y-1/2
                       z-20
                       w-36 h-20
                       sm:w-44 sm:h-24
                       bg-white rounded-lg
                       shadow-md
                       flex items-center justify-center
                       p-4
                       transition duration-300
                       hover:scale-105 hover:shadow-xl">

                @if($brands[1]->logo)

                <img src="{{ asset('storage/' . $brands[1]->logo) }}" alt="{{ $brands[1]->name }}"
                    class="max-w-full max-h-full object-contain">

                @else

                <span class="font-semibold text-navy text-center">
                    {{ $brands[1]->name }}
                </span>

                @endif

            </a>

            @endif



            {{-- =====================================================
             BRAND 3 — BOTTOM
             ====================================================== --}}

            @if(isset($brands[2]))

            <a href="{{ $brands[2]->categories->first()
                    ? route('category.show', [
                        'brand' => $brands[2]->slug,
                        'category' => $brands[2]->categories->first()->slug
                    ])
                    : '#' }}" class="absolute
                       left-1/2 bottom-[30px]
                       -translate-x-1/2
                       z-20
                       w-36 h-20
                       sm:w-44 sm:h-24
                       bg-white rounded-lg
                       shadow-md
                       flex items-center justify-center
                       p-4
                       transition duration-300
                       hover:scale-105 hover:shadow-xl">

                @if($brands[2]->logo)

                <img src="{{ asset('storage/' . $brands[2]->logo) }}" alt="{{ $brands[2]->name }}"
                    class="max-w-full max-h-full object-contain">

                @else

                <span class="font-semibold text-navy text-center">
                    {{ $brands[2]->name }}
                </span>

                @endif

            </a>

            @endif



            {{-- =====================================================
             BRAND 4 — LEFT
             ====================================================== --}}

            @if(isset($brands[3]))

            <a href="{{ $brands[3]->categories->first()
                    ? route('category.show', [
                        'brand' => $brands[3]->slug,
                        'category' => $brands[3]->categories->first()->slug
                    ])
                    : '#' }}" class="absolute
                       left-[20px] top-1/2
                       -translate-y-1/2
                       z-20
                       w-36 h-20
                       sm:w-44 sm:h-24
                       bg-white rounded-lg
                       shadow-md
                       flex items-center justify-center
                       p-4
                       transition duration-300
                       hover:scale-105 hover:shadow-xl">

                @if($brands[3]->logo)

                <img src="{{ asset('storage/' . $brands[3]->logo) }}" alt="{{ $brands[3]->name }}"
                    class="max-w-full max-h-full object-contain">

                @else

                <span class="font-semibold text-navy text-center">
                    {{ $brands[3]->name }}
                </span>

                @endif

            </a>

            @endif

        </div>

    </section>


    {{-- Scientific Verticals --}}
    <section class="relative w-full bg-ice py-16 px-4 sm:px-6 lg:px-8">

        {{-- Section Heading --}}
        <div class="text-center mb-10">

            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-semibold text-navy">
                Explore Our
                <span class="text-cyan">Scientific Verticals</span>
            </h2>

            <p class="mt-3 text-sm sm:text-base text-slate max-w-3xl mx-auto">
                From research to production, discover how our solutions support every lab need.
            </p>

        </div>


        {{-- Dynamic Verticals --}}
        @php
        $verticals = \App\Models\Vertical::query()
        ->orderBy('name')
        ->get();
        @endphp

        @if ($verticals->count())

        <div class="max-w-[1400px] mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-7">

            @foreach ($verticals as $vertical)

            <a href="{{ url('/verticals/' . $vertical->slug) }}" class="group block h-full">

                <article class="h-full min-h-[285px]
                               rounded-2xl overflow-hidden
                               bg-white
                               border border-[#E7EDF2]
                               shadow-sm
                               transition-all duration-300 ease-out
                               hover:-translate-y-1
                               hover:shadow-lg
                               hover:border-cyan
                               flex flex-col">

                    {{-- Card Content --}}
                    <div class="flex-1 bg-[#F5F7F8] p-5 sm:p-6">

                        {{-- Icon + Title --}}
                        <div class="flex items-start gap-3 mb-5">

                            {{-- Icon --}}
                            <div class="w-12 h-12 sm:w-14 sm:h-14
                                           shrink-0 rounded-full
                                           bg-white
                                           flex items-center justify-center
                                           shadow-sm
                                           overflow-hidden">
                                @if ($vertical->icon)
                                <img src="{{ asset('storage/' . $vertical->icon) }}" alt="{{ $vertical->name }}" class="w-9 h-9 sm:w-10 sm:h-10 object-contain
                                                   transition-transform duration-700
                                                   group-hover:rotate-[360deg]">
                                @else
                                <div class="text-cyan text-xl font-bold">
                                    +
                                </div>
                                @endif
                            </div>


                            {{-- Title --}}
                            <h3 class="pt-1
                                           text-base sm:text-[17px]
                                           leading-6
                                           font-medium
                                           text-link
                                           group-hover:text-navy
                                           transition-colors duration-200">
                                {{ $vertical->name }}
                            </h3>

                        </div>


                        {{-- Divider --}}
                        <div class="w-full h-px
                                       bg-gradient-to-r
                                       from-cyan/70
                                       to-transparent
                                       mb-4"></div>


                        {{-- Description --}}
                        <p class="text-sm
                                       leading-6
                                       text-navy
                                       line-clamp-3">
                            {{ $vertical->description ?: 'Explore our products, solutions and scientific applications in this vertical.' }}
                        </p>

                    </div>


                    {{-- Bottom Button --}}
                    <div class="w-full
                                   bg-navy
                                   text-white
                                   py-3
                                   px-5
                                   text-center
                                   text-sm
                                   font-semibold
                                   transition-colors duration-200
                                   group-hover:bg-cyan
                                   group-hover:text-navy">
                        Know More
                    </div>

                </article>

            </a>

            @endforeach

        </div>

        @else

        {{-- Empty State --}}
        <div class="text-center py-12 text-slate">
            No scientific verticals available yet.
        </div>

        @endif

    </section>


    {{-- =========================================================
        TRUSTED CLIENTS
    ========================================================== --}}

    <section class="mx-[5px] mt-14 pb-10">

        <h2 class="mb-5 text-2xl font-bold text-navy">
            Our Trusted Clients
        </h2>

        <div class="grid grid-cols-2 items-center gap-5 sm:grid-cols-4 lg:grid-cols-6">

            @foreach ($clients as $client)

            <div class="flex h-24 items-center justify-center rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">

                @if ($client->logo)

                <img src="{{ asset('storage/' . $client->logo) }}" alt="{{ $client->name }}"
                    class="max-h-14 max-w-full object-contain">

                @else

                <span class="text-center text-sm text-slate">
                    {{ $client->name }}
                </span>

                @endif

            </div>

            @endforeach

        </div>

    </section>

</x-layouts.app>