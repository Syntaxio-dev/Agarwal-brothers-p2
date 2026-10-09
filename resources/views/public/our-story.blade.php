<x-layouts.app title="Our Story">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Our Story</span>
        </div>

        {{-- Hero --}}
        <div class="mb-14">
            <span class="px-4 py-1 text-xs font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                Who We Are
            </span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-bold text-navy leading-tight">
                43 Years of Scientific <span class="text-cyan">Excellence</span>
            </h1>
            <p class="mt-4 text-slate text-base sm:text-lg max-w-3xl leading-relaxed">
                Agarwal Brothers has been a trusted name in laboratory equipment, scientific instruments and chemicals since 1981.
                From a single office in Jaipur, we have grown into one of Rajasthan's most respected scientific solutions providers.
            </p>
        </div>

        {{-- Timeline --}}
        <div class="relative mb-16">

            {{-- Vertical line --}}
            <div class="absolute left-4 sm:left-1/2 top-0 bottom-0 w-0.5 bg-cyan/30 sm:-translate-x-px"></div>

            <div class="space-y-10">

                {{-- 1981 --}}
                <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="sm:w-1/2 sm:pr-10 sm:text-right pl-12 sm:pl-0">
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-link mb-1">1981</span>
                        <h3 class="text-lg font-bold text-navy">The Beginning</h3>
                        <p class="mt-1 text-sm text-slate leading-relaxed">
                            Founded in Jaipur, Rajasthan, with a vision to bring world-class scientific instruments to Indian laboratories.
                            Started as a small trading firm supplying lab chemicals and basic equipment.
                        </p>
                    </div>
                    <div class="absolute left-4 sm:static sm:flex sm:items-center sm:justify-center sm:w-0">
                        <div class="w-4 h-4 rounded-full bg-cyan border-4 border-white shadow-md sm:absolute sm:left-1/2 sm:-translate-x-1/2"></div>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-10"></div>
                </div>

                {{-- 1990s --}}
                <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-10"></div>
                    <div class="absolute left-4 sm:static sm:flex sm:items-center sm:justify-center sm:w-0">
                        <div class="w-4 h-4 rounded-full bg-navy border-4 border-white shadow-md sm:absolute sm:left-1/2 sm:-translate-x-1/2"></div>
                    </div>
                    <div class="sm:w-1/2 sm:pl-10 pl-12 sm:pl-10">
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-link mb-1">1990s</span>
                        <h3 class="text-lg font-bold text-navy">Expanding Horizons</h3>
                        <p class="mt-1 text-sm text-slate leading-relaxed">
                            Established partnerships with leading global instrument manufacturers.
                            Expanded product portfolio to include analytical instruments, centrifuges, balances and chromatography systems.
                        </p>
                    </div>
                </div>

                {{-- 2000s --}}
                <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="sm:w-1/2 sm:pr-10 sm:text-right pl-12 sm:pl-0">
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-link mb-1">2000s</span>
                        <h3 class="text-lg font-bold text-navy">Serving India's Pharma Boom</h3>
                        <p class="mt-1 text-sm text-slate leading-relaxed">
                            As India's pharmaceutical industry grew, so did our reach. We became a preferred supplier to
                            major pharma companies, CROs, and academic institutions across Rajasthan and beyond.
                        </p>
                    </div>
                    <div class="absolute left-4 sm:static sm:flex sm:items-center sm:justify-center sm:w-0">
                        <div class="w-4 h-4 rounded-full bg-cyan border-4 border-white shadow-md sm:absolute sm:left-1/2 sm:-translate-x-1/2"></div>
                    </div>
                    <div class="hidden sm:block sm:w-1/2 sm:pl-10"></div>
                </div>

                {{-- Today --}}
                <div class="relative flex flex-col sm:flex-row sm:items-center gap-6">
                    <div class="hidden sm:block sm:w-1/2 sm:pr-10"></div>
                    <div class="absolute left-4 sm:static sm:flex sm:items-center sm:justify-center sm:w-0">
                        <div class="w-5 h-5 rounded-full bg-link border-4 border-white shadow-lg ring-4 ring-cyan/20 sm:absolute sm:left-1/2 sm:-translate-x-1/2"></div>
                    </div>
                    <div class="sm:w-1/2 sm:pl-10 pl-12 sm:pl-10">
                        <span class="inline-block text-xs font-bold uppercase tracking-widest text-link mb-1">Today</span>
                        <h3 class="text-lg font-bold text-navy">43+ Years Strong</h3>
                        <p class="mt-1 text-sm text-slate leading-relaxed">
                            Today Agarwal Brothers represents 50+ global brands, serves 36,000+ customers, and operates
                            with a dedicated team across multiple branches — continuing our commitment to equipping India's
                            labs with the finest scientific solutions.
                        </p>
                    </div>
                </div>

            </div>
        </div>

        {{-- Values grid --}}
        <div class="mb-16">
            <div class="text-center mb-10">
                <span class="px-4 py-1 text-xs font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                    Our Values
                </span>
                <h2 class="mt-4 text-2xl font-bold text-navy">What Drives Us</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="rounded-2xl bg-ice border border-gray-100 p-6 hover:shadow-md hover:border-cyan/30 transition-all duration-300">
                    <div class="h-12 w-12 rounded-full bg-cyan/10 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-navy mb-2">Quality First</h3>
                    <p class="text-sm text-slate leading-relaxed">
                        We represent only globally certified brands with proven track records — because your research deserves nothing less.
                    </p>
                </div>

                <div class="rounded-2xl bg-ice border border-gray-100 p-6 hover:shadow-md hover:border-cyan/30 transition-all duration-300">
                    <div class="h-12 w-12 rounded-full bg-cyan/10 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-navy mb-2">Customer Partnership</h3>
                    <p class="text-sm text-slate leading-relaxed">
                        We don't just sell instruments — we partner with your lab, understand your application, and recommend the right solution.
                    </p>
                </div>

                <div class="rounded-2xl bg-ice border border-gray-100 p-6 hover:shadow-md hover:border-cyan/30 transition-all duration-300">
                    <div class="h-12 w-12 rounded-full bg-cyan/10 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.65-5.65 3.029 2.497c.379.312.62.74.757 1.209m-5.786-3.706L5.18 6.748a2.25 2.25 0 0 1 2.12-3.763l6.33 1.53"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-navy mb-2">After-Sales Support</h3>
                    <p class="text-sm text-slate leading-relaxed">
                        Installation, calibration, AMCs, and rapid spares support — we stand behind every instrument we sell.
                    </p>
                </div>

                <div class="rounded-2xl bg-ice border border-gray-100 p-6 hover:shadow-md hover:border-cyan/30 transition-all duration-300">
                    <div class="h-12 w-12 rounded-full bg-cyan/10 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-navy mb-2">Technical Expertise</h3>
                    <p class="text-sm text-slate leading-relaxed">
                        Our team includes trained application specialists who understand your science — not just the instruments.
                    </p>
                </div>

                <div class="rounded-2xl bg-ice border border-gray-100 p-6 hover:shadow-md hover:border-cyan/30 transition-all duration-300">
                    <div class="h-12 w-12 rounded-full bg-cyan/10 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-navy mb-2">Pan-India Reach</h3>
                    <p class="text-sm text-slate leading-relaxed">
                        With 12+ branches across India, we bring the same level of expertise and service to labs wherever they are.
                    </p>
                </div>

                <div class="rounded-2xl bg-ice border border-gray-100 p-6 hover:shadow-md hover:border-cyan/30 transition-all duration-300">
                    <div class="h-12 w-12 rounded-full bg-cyan/10 flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-navy mb-2">Training & Demos</h3>
                    <p class="text-sm text-slate leading-relaxed">
                        We conduct instrument demonstrations, user training and application workshops so your team gets the most from their investment.
                    </p>
                </div>

            </div>
        </div>

        {{-- CTA banner --}}
        <div class="rounded-2xl bg-navy px-8 py-10 sm:py-12 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div>
                <h3 class="text-xl sm:text-2xl font-bold text-white">Ready to work with us?</h3>
                <p class="mt-2 text-white/70 text-sm max-w-md">
                    Talk to our team about your lab requirements — we'll help you find the right solution.
                </p>
            </div>
            <a href="/contact-us"
                class="shrink-0 inline-flex items-center gap-2 rounded-full bg-cyan px-7 py-3
                       text-sm font-bold text-navy shadow-md
                       hover:bg-white hover:scale-105 transition-all duration-300">
                Get in Touch
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                </svg>
            </a>
        </div>

    </div>
</x-layouts.app>
