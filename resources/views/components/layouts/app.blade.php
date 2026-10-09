<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Agarwal Brothers' }} — Lab Equipment & Scientific Solutions</title>
    <meta name="description" content="{{ $metaDescription ?? 'Agarwal Brothers — 43+ years of excellence in laboratory equipment, scientific instruments, and chemicals across India.' }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-navy antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile top bar --}}
    <div class="fixed top-0 left-0 right-0 z-40 flex items-center gap-3 bg-white border-b border-gray-200 px-4 h-14 lg:hidden">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md bg-ice shadow-sm">
            <svg class="w-6 h-6 text-slate" fill="currentColor" viewBox="0 0 448 512">
                <path d="M16 132h416c8.8 0 16-7.2 16-16V76c0-8.8-7.2-16-16-16H16C7.2 60 0 67.2 0 76v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16z"/>
            </svg>
        </button>
        <a href="/" aria-label="Home">
            <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers" class="h-10 w-auto object-contain">
        </a>
    </div>

    <div class="flex min-h-screen w-full">

        {{-- =========================================================
            LEFT SIDEBAR — Inkarp-style fixed navigation
        ========================================================== --}}
        <aside
            class="fixed top-0 left-0 bottom-0 z-30 bg-white border-r border-gray-200 shadow-lg
                   transition-transform duration-300 ease-in-out w-[260px] lg:w-[16%]"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            @click.away="sidebarOpen = false">

            <div class="flex flex-col h-screen max-h-screen w-full py-4 px-3 space-y-3 overflow-y-auto">

                {{-- Logo --}}
                <div class="hidden lg:flex items-center justify-center mb-2 pt-2">
                    <a href="/" aria-label="Home">
                        <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers"
                            class="h-[70px] lg:h-[90px] w-auto object-contain">
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 space-y-1 font-medium mt-14 lg:mt-0">

                    <a href="/"
                        class="block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('/') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Home
                    </a>

                    <a href="/our-story"
                        class="block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('our-story') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Our Story
                    </a>

                    <a href="/verticals"
                        class="block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('verticals*') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Verticals
                    </a>

                    {{-- Insights dropdown --}}
                    <div x-data="{ open: {{ request()->is('insights*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full px-3 py-2.5 rounded-md text-navy hover:bg-gray-100 transition-all whitespace-nowrap">
                            <span class="text-left">Insights & Updates</span>
                            <svg class="h-4 w-4 text-cyan transition-transform duration-200"
                                :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak class="ml-3 mt-1 space-y-0.5 border-l-2 border-cyan/30 pl-3">
                            <a href="{{ route('insights.blogs') }}"
                                class="block px-3 py-2 rounded-md text-sm {{ request()->is('insights/blog') ? 'text-link font-semibold' : 'text-navy hover:bg-gray-100' }}">
                                Blogs
                            </a>
                            <a href="{{ route('insights.news') }}"
                                class="block px-3 py-2 rounded-md text-sm {{ request()->is('insights/news') ? 'text-link font-semibold' : 'text-navy hover:bg-gray-100' }}">
                                News & Events
                            </a>
                            <a href="{{ route('insights.webinars') }}"
                                class="block px-3 py-2 rounded-md text-sm {{ request()->is('insights/webinar') ? 'text-link font-semibold' : 'text-navy hover:bg-gray-100' }}">
                                Webinars
                            </a>
                        </div>
                    </div>

                    <a href="/careers"
                        class="block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('careers') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Careers
                    </a>

                    <a href="/contact-us"
                        class="block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('contact-us') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Contact Us
                    </a>

                </nav>

                {{-- Bottom actions --}}
                <div class="space-y-2 pt-4 border-t border-gray-200 mt-auto">

                    <a href="{{ route('search') }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white text-navy border border-gray-300 rounded-md
                               hover:border-cyan hover:bg-ice transition w-full">
                        <svg class="w-4 h-4 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                        Search
                    </a>

                    @php $catalogue = \App\Models\SiteSetting::latest()->first(); @endphp
                    @if ($catalogue?->catalogue_file)
                        <a href="{{ asset('storage/' . $catalogue->catalogue_file) }}" download
                            class="flex items-center justify-between gap-2 px-4 py-2.5 h-11
                                   bg-navy text-white font-medium rounded-md
                                   hover:bg-link transition w-full">
                            <span class="whitespace-nowrap overflow-hidden text-ellipsis text-sm">Product Catalogue</span>
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 17V3m0 14-6-6m6 6 6-6M5 21h14"/>
                            </svg>
                        </a>
                    @endif

                </div>
            </div>
        </aside>

        {{-- Mobile sidebar backdrop --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-20 lg:hidden" x-transition.opacity></div>

        {{-- =========================================================
            MAIN CONTENT AREA
        ========================================================== --}}
        <div class="flex-1 transition-all duration-300 ease-in-out lg:ml-[16%] w-full lg:w-[84%]">

            <div class="relative flex flex-col min-h-screen">

                <main class="flex-grow mt-14 lg:mt-0">
                    {{ $slot }}
                </main>

                {{-- FOOTER --}}
                <footer class="bg-navy text-white">
                    <div class="px-6 sm:px-10 lg:px-16 py-14">
                        <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">

                            <div>
                                <a href="/" class="inline-block mb-5">
                                    <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers"
                                        class="h-14 w-auto brightness-0 invert object-contain">
                                </a>
                                <p class="text-sm leading-7 text-white/70">
                                    43+ years of excellence in laboratory equipment, scientific instruments and chemicals.
                                    Serving pharma, biotech, diagnostics &amp; academia across India.
                                </p>
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

                            <div>
                                <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-5">Quick Links</h4>
                                <ul class="space-y-3">
                                    <li><a href="/" class="text-sm text-white/70 hover:text-cyan transition">Home</a></li>
                                    <li><a href="/our-story" class="text-sm text-white/70 hover:text-cyan transition">Our Story</a></li>
                                    <li><a href="/verticals" class="text-sm text-white/70 hover:text-cyan transition">Verticals</a></li>
                                    <li><a href="{{ route('insights.blogs') }}" class="text-sm text-white/70 hover:text-cyan transition">Blogs</a></li>
                                    <li><a href="{{ route('insights.news') }}" class="text-sm text-white/70 hover:text-cyan transition">News & Events</a></li>
                                    <li><a href="/careers" class="text-sm text-white/70 hover:text-cyan transition">Careers</a></li>
                                    <li><a href="/contact-us" class="text-sm text-white/70 hover:text-cyan transition">Contact Us</a></li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-5">Our Verticals</h4>
                                <ul class="space-y-3">
                                    @php $footerVerticals = \App\Models\Vertical::where('is_active', true)->orderBy('sort_order')->limit(6)->get(); @endphp
                                    @foreach ($footerVerticals as $v)
                                        <li><a href="{{ route('vertical.show', $v->slug) }}" class="text-sm text-white/70 hover:text-cyan transition">{{ $v->name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-bold uppercase tracking-wider text-white mb-5">Contact Us</h4>
                                <ul class="space-y-4">
                                    <li class="flex items-start gap-3">
                                        <svg class="h-5 w-5 mt-0.5 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z"/>
                                        </svg>
                                        <span class="text-sm text-white/70">Agarwal Brothers,<br>VKI Area, Jaipur,<br>Rajasthan, India</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <svg class="h-5 w-5 mt-0.5 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75"/>
                                        </svg>
                                        <span class="text-sm text-white/70">info@agarwalbrothers.com</span>
                                    </li>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <div class="border-t border-white/10">
                        <div class="px-6 sm:px-10 lg:px-16 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <p class="text-xs text-white/50">&copy; {{ date('Y') }} Agarwal Brothers. All rights reserved.</p>
                            <div class="flex items-center gap-5">
                                <a href="#" class="text-xs text-white/50 hover:text-white transition">Privacy Policy</a>
                                <a href="#" class="text-xs text-white/50 hover:text-white transition">Terms & Conditions</a>
                            </div>
                        </div>
                    </div>
                </footer>

            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

</body>

</html>
