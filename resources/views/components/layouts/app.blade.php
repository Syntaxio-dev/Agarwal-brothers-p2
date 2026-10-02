<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Agarwal Brothers' }} — Lab Equipment & Scientific Solutions</title>
    <meta name="description" content="{{ $metaDescription ?? 'Agarwal Brothers — 43+ years of excellence in laboratory equipment, scientific instruments, and chemicals. Serving pharma, biotech, diagnostics & academia across India.' }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-navy font-sans antialiased">

    {{-- =========================================================
        TOP NAVBAR
    ========================================================== --}}
    <header x-data="{ mobileOpen: false, insightsOpen: false }" class="sticky top-0 z-50 w-full bg-white shadow-sm border-b border-gray-100">

        {{-- Primary Nav Bar --}}
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-[72px] items-center justify-between">

                {{-- Logo --}}
                <a href="/" class="shrink-0">
                    <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers"
                        class="h-[54px] w-auto object-contain">
                </a>

                {{-- Desktop Navigation --}}
                <nav class="hidden lg:flex items-center gap-1">

                    <a href="/"
                        class="px-4 py-2 rounded-lg text-[14px] font-semibold transition-all duration-200
                        {{ request()->is('/') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">
                        Home
                    </a>

                    <a href="/verticals"
                        class="px-4 py-2 rounded-lg text-[14px] font-semibold transition-all duration-200
                        {{ request()->is('verticals*') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">
                        Verticals
                    </a>

                    {{-- Insights Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button
                            class="flex items-center gap-1 px-4 py-2 rounded-lg text-[14px] font-semibold text-navy hover:bg-ice transition-all duration-200">
                            Insights
                            <svg class="h-3.5 w-3.5 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute left-0 top-full mt-1 w-48 rounded-xl bg-white py-2 shadow-xl ring-1 ring-black/5">
                            <a href="{{ route('insights.blogs') }}" class="block px-4 py-2.5 text-sm text-navy hover:bg-ice hover:text-link transition">Blogs</a>
                            <a href="{{ route('insights.news') }}" class="block px-4 py-2.5 text-sm text-navy hover:bg-ice hover:text-link transition">News & Events</a>
                            <a href="{{ route('insights.webinars') }}" class="block px-4 py-2.5 text-sm text-navy hover:bg-ice hover:text-link transition">Webinars</a>
                        </div>
                    </div>

                    <a href="/application-resources"
                        class="px-4 py-2 rounded-lg text-[14px] font-semibold transition-all duration-200
                        {{ request()->is('application-resources') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">
                        Resources
                    </a>

                    <a href="/careers"
                        class="px-4 py-2 rounded-lg text-[14px] font-semibold transition-all duration-200
                        {{ request()->is('careers') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">
                        Careers
                    </a>

                    <a href="/contact-us"
                        class="px-4 py-2 rounded-lg text-[14px] font-semibold transition-all duration-200
                        {{ request()->is('contact-us') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">
                        Contact
                    </a>

                </nav>

                {{-- Right Side: Search + Catalogue --}}
                <div class="hidden lg:flex items-center gap-3">

                    {{-- Search --}}
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <input type="text" name="q" placeholder="Search products..."
                            class="h-10 w-[220px] rounded-full border border-gray-200 bg-ice pl-10 pr-4
                                   text-sm text-navy placeholder:text-slate
                                   outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20
                                   transition-all duration-200">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                    </form>

                    {{-- Catalogue Download --}}
                    @php $catalogue = \App\Models\SiteSetting::latest()->first(); @endphp
                    @if ($catalogue?->catalogue_file)
                        <a href="{{ asset('storage/' . $catalogue->catalogue_file) }}" download
                            class="inline-flex items-center gap-2 rounded-full bg-navy px-5 py-2.5
                                   text-xs font-semibold text-white shadow-sm
                                   hover:bg-link transition-all duration-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0-4-4m4 4 4-4M4 18h16" />
                            </svg>
                            Catalogue
                        </a>
                    @endif

                </div>

                {{-- Mobile Hamburger --}}
                <button @click="mobileOpen = !mobileOpen" type="button"
                    class="lg:hidden flex items-center justify-center h-10 w-10 rounded-lg text-navy hover:bg-ice transition">
                    <svg x-show="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileOpen" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="lg:hidden border-t border-gray-100 bg-white shadow-lg">

            <div class="mx-auto max-w-7xl px-4 py-4 space-y-1">

                <a href="/" class="block px-4 py-3 rounded-lg text-sm font-semibold {{ request()->is('/') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">Home</a>
                <a href="/verticals" class="block px-4 py-3 rounded-lg text-sm font-semibold {{ request()->is('verticals*') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">Verticals</a>

                {{-- Insights accordion --}}
                <div>
                    <button @click="insightsOpen = !insightsOpen"
                        class="flex w-full items-center justify-between px-4 py-3 rounded-lg text-sm font-semibold text-navy hover:bg-ice">
                        Insights
                        <svg class="h-4 w-4 transition-transform" :class="insightsOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="insightsOpen" x-cloak class="ml-4 space-y-1 border-l-2 border-cyan/30 pl-4">
                        <a href="{{ route('insights.blogs') }}" class="block px-3 py-2 rounded-lg text-sm text-navy hover:text-link">Blogs</a>
                        <a href="{{ route('insights.news') }}" class="block px-3 py-2 rounded-lg text-sm text-navy hover:text-link">News & Events</a>
                        <a href="{{ route('insights.webinars') }}" class="block px-3 py-2 rounded-lg text-sm text-navy hover:text-link">Webinars</a>
                    </div>
                </div>

                <a href="/application-resources" class="block px-4 py-3 rounded-lg text-sm font-semibold {{ request()->is('application-resources') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">Resources</a>
                <a href="/careers" class="block px-4 py-3 rounded-lg text-sm font-semibold {{ request()->is('careers') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">Careers</a>
                <a href="/contact-us" class="block px-4 py-3 rounded-lg text-sm font-semibold {{ request()->is('contact-us') ? 'bg-navy text-white' : 'text-navy hover:bg-ice' }}">Contact</a>

                {{-- Mobile Search --}}
                <div class="pt-3 border-t border-gray-100">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <input type="text" name="q" placeholder="Search products..."
                            class="h-11 w-full rounded-xl border border-gray-200 bg-ice pl-10 pr-4
                                   text-sm text-navy placeholder:text-slate
                                   outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                        </svg>
                    </form>
                </div>

                {{-- Mobile Catalogue --}}
                @if ($catalogue?->catalogue_file)
                    <a href="{{ asset('storage/' . $catalogue->catalogue_file) }}" download
                        class="flex items-center justify-center gap-2 w-full h-11 mt-2
                               rounded-xl bg-navy text-white text-sm font-semibold
                               hover:bg-link transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v12m0 0-4-4m4 4 4-4M4 18h16" />
                        </svg>
                        Download Catalogue
                    </a>
                @endif

            </div>
        </div>

    </header>


    {{-- =========================================================
        MAIN CONTENT
    ========================================================== --}}
    <main class="min-h-[60vh]">
        {{ $slot }}
    </main>


    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer class="bg-navy text-white">

        {{-- Main Footer --}}
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14 lg:py-16">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Column 1: Brand --}}
                <div class="lg:col-span-1">
                    <a href="/" class="inline-block mb-5">
                        <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers"
                            class="h-14 w-auto brightness-0 invert object-contain">
                    </a>
                    <p class="text-sm leading-7 text-white/70">
                        43+ years of excellence in laboratory equipment, scientific instruments and chemicals.
                        Serving pharma, biotech, diagnostics &amp; academia across India.
                    </p>

                    {{-- Social Links --}}
                    <div class="mt-6 flex items-center gap-3">
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-cyan hover:text-navy transition" aria-label="LinkedIn">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z"/></svg>
                        </a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-cyan hover:text-navy transition" aria-label="YouTube">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.5 6.19a3.02 3.02 0 00-2.12-2.14C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.38.55A3.02 3.02 0 00.5 6.19 31.8 31.8 0 000 12a31.8 31.8 0 00.5 5.81 3.02 3.02 0 002.12 2.14c1.87.55 9.38.55 9.38.55s7.5 0 9.38-.55a3.02 3.02 0 002.12-2.14A31.8 31.8 0 0024 12a31.8 31.8 0 00-.5-5.81zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg>
                        </a>
                        <a href="#" class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-cyan hover:text-navy transition" aria-label="WhatsApp">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07a8.2 8.2 0 01-2.41-1.49 9.06 9.06 0 01-1.67-2.08c-.17-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57a1.1 1.1 0 00-.8.37A3.33 3.33 0 006 9.39a5.78 5.78 0 001.21 3.07 13.26 13.26 0 005.08 4.49 17.4 17.4 0 001.7.62 4.07 4.07 0 001.88.12 3.08 3.08 0 002.02-1.42 2.5 2.5 0 00.17-1.42c-.07-.12-.27-.2-.57-.34zM12.05 21.5A9.44 9.44 0 016.9 19.9l-.34-.2-3.54.93.95-3.47-.22-.36a9.45 9.45 0 01-1.45-5.04 9.5 9.5 0 0116.2-6.72A9.44 9.44 0 0121.5 12a9.5 9.5 0 01-9.45 9.5zm0-21A11.5 11.5 0 003.73 3.73a11.5 11.5 0 00-2.2 13.48L.05 24l6.96-1.45a11.5 11.5 0 005.04 1.17h.01A11.5 11.5 0 0012.05.5z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Column 2: Quick Links --}}
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-5">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="/" class="text-sm text-white/70 hover:text-cyan transition">Home</a></li>
                        <li><a href="/verticals" class="text-sm text-white/70 hover:text-cyan transition">Verticals</a></li>
                        <li><a href="{{ route('insights.blogs') }}" class="text-sm text-white/70 hover:text-cyan transition">Blogs</a></li>
                        <li><a href="{{ route('insights.news') }}" class="text-sm text-white/70 hover:text-cyan transition">News & Events</a></li>
                        <li><a href="/application-resources" class="text-sm text-white/70 hover:text-cyan transition">Resources</a></li>
                        <li><a href="/careers" class="text-sm text-white/70 hover:text-cyan transition">Careers</a></li>
                        <li><a href="/contact-us" class="text-sm text-white/70 hover:text-cyan transition">Contact Us</a></li>
                    </ul>
                </div>

                {{-- Column 3: Verticals --}}
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-5">Our Verticals</h4>
                    <ul class="space-y-3">
                        @php
                            $footerVerticals = \App\Models\Vertical::where('is_active', true)->orderBy('sort_order')->limit(6)->get();
                        @endphp
                        @foreach ($footerVerticals as $v)
                            <li>
                                <a href="{{ route('vertical.show', $v->slug) }}" class="text-sm text-white/70 hover:text-cyan transition">
                                    {{ $v->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Column 4: Contact --}}
                <div>
                    <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-5">Contact Us</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 mt-0.5 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z" />
                            </svg>
                            <span class="text-sm text-white/70">Agarwal Brothers,<br>VKI Area, Jaipur,<br>Rajasthan, India</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="h-5 w-5 mt-0.5 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75" />
                            </svg>
                            <span class="text-sm text-white/70">info@agarwalbrothers.com</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-white/10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-white/50">
                    &copy; {{ date('Y') }} Agarwal Brothers. All rights reserved.
                </p>
                <div class="flex items-center gap-5">
                    <a href="#" class="text-xs text-white/50 hover:text-white transition">Privacy Policy</a>
                    <a href="#" class="text-xs text-white/50 hover:text-white transition">Terms & Conditions</a>
                </div>
            </div>
        </div>

    </footer>

    <style>
        [x-cloak] { display: none !important; }
    </style>

</body>

</html>
