<x-layouts.app title="Application Resources">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Application Resources</span>
        </div>

        {{-- Header --}}
        <div class="mb-12">
            <span class="px-4 py-1 text-xs font-medium uppercase rounded-full bg-white border border-cyan/40 text-link">
                Resources
            </span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-bold text-navy leading-tight">
                Application <span class="text-cyan">Resources</span>
            </h1>
            <p class="mt-4 text-slate text-base max-w-3xl leading-relaxed">
                Technical guides, application notes, and reference materials to help you get the most from your laboratory instruments.
            </p>
        </div>

        {{-- Filter tabs --}}
        <div x-data="{ active: 'all' }" class="mb-10">

            <div class="flex flex-wrap gap-2 mb-10">
                <button @click="active = 'all'"
                    :class="active === 'all' ? 'bg-navy text-white' : 'bg-ice text-navy hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200">
                    All Resources
                </button>
                <button @click="active = 'appnote'"
                    :class="active === 'appnote' ? 'bg-navy text-white' : 'bg-ice text-navy hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200">
                    Application Notes
                </button>
                <button @click="active = 'guide'"
                    :class="active === 'guide' ? 'bg-navy text-white' : 'bg-ice text-navy hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200">
                    Technical Guides
                </button>
                <button @click="active = 'video'"
                    :class="active === 'video' ? 'bg-navy text-white' : 'bg-ice text-navy hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200">
                    Videos & Webinars
                </button>
                <button @click="active = 'brochure'"
                    :class="active === 'brochure' ? 'bg-navy text-white' : 'bg-ice text-navy hover:bg-gray-100 border border-gray-200'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200">
                    Brochures
                </button>
            </div>

            {{-- Resource cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- Application Notes --}}
                <div x-show="active === 'all' || active === 'appnote'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-navy to-link"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-cyan/10 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-link bg-cyan/10 px-2.5 py-1 rounded-full">App Note</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            Rotary Evaporation: Solvent Recovery Best Practices
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Step-by-step guide for optimising evaporation efficiency, reducing bumping and achieving maximum solvent recovery using BUCHI rotary evaporators.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">BUCHI · Rotary Evaporators</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-link hover:text-navy transition">
                                Request
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div x-show="active === 'all' || active === 'appnote'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-navy to-link"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-cyan/10 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-link bg-cyan/10 px-2.5 py-1 rounded-full">App Note</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            HPLC Column Selection for Pharmaceutical Analysis
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Comprehensive guide on selecting the right reversed-phase column for API and impurity analysis in pharmaceutical QC labs.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">Shimadzu · HPLC Systems</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-link hover:text-navy transition">
                                Request
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div x-show="active === 'all' || active === 'appnote'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-navy to-link"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-cyan/10 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-link bg-cyan/10 px-2.5 py-1 rounded-full">App Note</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            Good Weighing Practice (GWP) for Regulated Labs
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Understand GLP/GMP compliant weighing routines, calibration intervals and uncertainty calculations for Mettler Toledo and Sartorius balances.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">Mettler Toledo · Analytical Balances</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-link hover:text-navy transition">
                                Request
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Technical Guides --}}
                <div x-show="active === 'all' || active === 'guide'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-cyan to-link"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-navy/60 bg-navy/5 px-2.5 py-1 rounded-full">Tech Guide</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            Centrifuge Maintenance & Rotor Care Guide
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Complete maintenance schedule, rotor inspection checklists and troubleshooting tips for Hettich centrifuges to maximise uptime and safety.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">Hettich · Centrifuges</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-link hover:text-navy transition">
                                Request
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div x-show="active === 'all' || active === 'guide'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-cyan to-link"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-navy/5 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-navy/60 bg-navy/5 px-2.5 py-1 rounded-full">Tech Guide</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            Water Purity Standards: Type I, II & III Explained
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Which water grade does your application need? A practical guide to ASTM/ISO water purity levels for HPLC, cell culture, IVF and general lab use.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">Thermo Fisher · Water Purification</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-link hover:text-navy transition">
                                Request
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Videos --}}
                <div x-show="active === 'all' || active === 'video'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-alert to-orange-400"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-alert/10 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-alert" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-alert bg-alert/10 px-2.5 py-1 rounded-full">Webinar</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            Introduction to UV-Vis Spectrophotometry
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            60-min recorded webinar covering Beer-Lambert law, instrument qualification, wavelength calibration and common troubleshooting with Shimadzu UV-1900i.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">Shimadzu · UV-Vis</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-link hover:text-navy transition">
                                Request Link
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div x-show="active === 'all' || active === 'video'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-alert to-orange-400"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-alert/10 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-alert" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-alert bg-alert/10 px-2.5 py-1 rounded-full">Webinar</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            Best Practices in Laboratory Weighing (GLP/GMP)
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Live session recording: selecting the right balance, daily routine tests, calibration schedules and meeting GLP/GMP documentation requirements.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">Mettler Toledo · Balances</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-link hover:text-navy transition">
                                Request Link
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Brochures --}}
                <div x-show="active === 'all' || active === 'brochure'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-success to-teal-400"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-success bg-success/10 px-2.5 py-1 rounded-full">Brochure</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            BUCHI Product Catalogue 2025–26
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Complete catalogue of BUCHI lab instruments — rotary evaporators, spray dryers, melting point systems, freeze dryers and accessories.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">BUCHI · Full Range</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-success hover:text-navy transition">
                                Download
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <div x-show="active === 'all' || active === 'brochure'"
                    class="group flex flex-col rounded-2xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-success to-teal-400"></div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="h-10 w-10 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                            </div>
                            <span class="text-[11px] font-bold uppercase tracking-wide text-success bg-success/10 px-2.5 py-1 rounded-full">Brochure</span>
                        </div>
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors mb-2">
                            Shimadzu HPLC & UHPLC Systems Overview
                        </h3>
                        <p class="text-sm text-slate leading-relaxed flex-1">
                            Shimadzu's full HPLC portfolio — from entry-level LC-2050 to high-throughput Nexera X3 UHPLC — with specifications and application tables.
                        </p>
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs text-slate">Shimadzu · HPLC</span>
                            <a href="/contact-us" class="inline-flex items-center gap-1 text-xs font-semibold text-success hover:text-navy transition">
                                Download
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- CTA: Can't find what you need --}}
        <div class="mt-4 rounded-2xl bg-ice border border-cyan/20 px-8 py-8 flex flex-col sm:flex-row items-center gap-5">
            <div class="h-14 w-14 rounded-full bg-cyan/10 flex items-center justify-center shrink-0">
                <svg class="h-7 w-7 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
                </svg>
            </div>
            <div class="flex-1 text-center sm:text-left">
                <h3 class="font-bold text-navy">Can't find the resource you need?</h3>
                <p class="mt-1 text-sm text-slate">Our technical team can provide application-specific guides, SOPs and instrument selection advice for your lab.</p>
            </div>
            <a href="/contact-us"
                class="shrink-0 inline-flex items-center gap-2 rounded-full bg-navy px-6 py-2.5
                       text-sm font-semibold text-white hover:bg-link transition-all duration-300">
                Ask Our Team
            </a>
        </div>

    </div>
</x-layouts.app>
