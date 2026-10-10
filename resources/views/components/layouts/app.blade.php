<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seoTitle = \App\Support\Seo::title($title ?? null);
        $seoDescription = \App\Support\Seo::description($description ?? $metaDescription ?? null);
        $seoImage = \App\Support\Seo::image($image ?? null);
        $seoUrl = \App\Support\Seo::canonical();
        $seoNoindex = ($noindex ?? false) || ! \App\Support\Seo::indexable() || \App\Support\Preview::active(request());
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $seoNoindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' }}">
    <link rel="canonical" href="{{ $seoUrl }}">

    <meta property="og:site_name" content="Agarwal Brothers">
    <meta property="og:locale" content="en_IN">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    @isset($schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endisset
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    @if (filled(config('services.analytics_id')))
        <meta name="analytics-id" content="{{ config('services.analytics_id') }}">
    @endif

    {{-- Lets CSS show image placeholders only when JS is available to remove them again --}}
    <script>document.documentElement.classList.add('js'); setTimeout(function () { if (!window.__revealOk) document.documentElement.classList.add('reveal-failsafe'); }, 3500);</script>

    {{-- Load the main fonts early so text does not shift when they swap in --}}
    @foreach (['ibm-plex-sans/files/ibm-plex-sans-latin-400-normal.woff2', 'ibm-plex-sans/files/ibm-plex-sans-latin-600-normal.woff2', 'ibm-plex-mono/files/ibm-plex-mono-latin-400-normal.woff2'] as $font)
        <link rel="preload" href="{{ \Illuminate\Support\Facades\Vite::asset('node_modules/@fontsource/' . $font) }}" as="font" type="font/woff2" crossorigin>
    @endforeach

    {{-- In the head on purpose: a style that arrives late (end of body) makes browsers drop the page-transition opt-in --}}
    <style>[x-cloak] { display: none !important; }</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-navy antialiased" x-data="{ sidebarOpen: false }">

    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-navy focus:px-5 focus:py-3 focus:text-sm focus:font-semibold focus:text-white focus:shadow-xl"
       style="outline-offset: 3px;">Skip to main content</a>

    {{-- Mobile top bar --}}
    <div class="fixed top-0 left-0 right-0 z-40 flex items-center gap-3 bg-white border-b border-gray-200 px-4 h-14 lg:hidden">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md bg-ice shadow-sm">
            <svg class="w-6 h-6 text-slate" fill="currentColor" viewBox="0 0 448 512">
                <path d="M16 132h416c8.8 0 16-7.2 16-16V76c0-8.8-7.2-16-16-16H16C7.2 60 0 67.2 0 76v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16zm0 160h416c8.8 0 16-7.2 16-16v-40c0-8.8-7.2-16-16-16H16c-8.8 0-16 7.2-16 16v40c0 8.8 7.2 16 16 16z"/>
            </svg>
        </button>
        <a href="/" aria-label="Home">
            <img src="{{ asset('sidebar-logo.png') }}" alt="Agarwal Brothers" class="img-load h-9 w-auto object-contain" decoding="async" onload="this.classList.add('is-loaded')" {!! \App\Support\Img::publicAttrs('sidebar-logo.png') !!}>
        </a>
        <a href="{{ route('enquiry-list') }}" x-cloak x-show="$store.enquiryList.count" aria-label="Enquiry list"
           class="relative ml-auto flex h-10 w-10 items-center justify-center rounded-md bg-ice text-navy">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm0 5.25h.007v.008H3.75V12Zm0 5.25h.007v.008H3.75v-.008Z"/></svg>
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-cyan px-1 font-mono text-[11px] font-semibold text-navy" x-text="$store.enquiryList.count"></span>
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
                <div class="hidden lg:flex items-center justify-center px-1 mb-3 pt-3">
                    <a href="/" aria-label="Home">
                        <img src="{{ asset('sidebar-logo.png') }}" alt="Agarwal Brothers"
                            class="img-load w-full max-w-[210px] h-auto object-contain" decoding="async" onload="this.classList.add('is-loaded')" {!! \App\Support\Img::publicAttrs('sidebar-logo.png') !!}>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 space-y-1 font-medium mt-14 lg:mt-0">

                    <a href="/"
                        class="nav-link block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('/') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Home
                    </a>

                    <a href="/our-story"
                        class="nav-link block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('our-story') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Our Story
                    </a>

                    <a href="/verticals"
                        class="nav-link block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('verticals*') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Verticals
                    </a>

                    {{-- Insights: compact dropdown (hover on desktop, tap on mobile) --}}
                    <div x-data="{
                            open: false, t: null,
                            desktop() { return window.innerWidth >= 1024 },
                            show() { if (this.desktop()) { clearTimeout(this.t); this.open = true } },
                            hide() { if (this.desktop()) { clearTimeout(this.t); this.t = setTimeout(() => this.open = false, 200) } }
                         }"
                         @mouseenter="show()" @mouseleave="hide()" @keydown.escape="open = false"
                         class="relative">
                        <button @click="open = !open"
                            class="nav-link flex items-center justify-between w-full px-3 py-2.5 rounded-md transition-all whitespace-nowrap
                                   {{ request()->is('insights*') ? 'bg-navy text-white shadow' : 'text-navy' }}"
                            :class="open && !{{ request()->is('insights*') ? 'true' : 'false' }} && 'bg-gray-100'">
                            <span class="text-left">Insights & Updates</span>
                            <svg class="h-4 w-4 text-cyan-ink transition-transform duration-200"
                                :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                            </svg>
                        </button>

                        <div x-cloak x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="mt-1 space-y-0.5 rounded-lg bg-white p-1.5 border border-gray-100 shadow-lg
                                    lg:absolute lg:left-0 lg:right-0 lg:top-full lg:z-40">
                            @foreach ([
                                ['insights.blogs', 'Blogs', 'insights/blogs'],
                                ['insights.news', 'News & Events', 'insights/news-events'],
                                ['insights.webinars', 'Webinars', 'insights/webinars'],
                            ] as [$r, $label, $match])
                                <a href="{{ route($r) }}"
                                   class="block rounded-md px-3 py-2 text-sm transition-colors
                                          {{ request()->is($match) ? 'bg-cyan/10 text-link font-semibold' : 'text-navy hover:bg-gray-100' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <a href="/application-resources"
                        class="nav-link block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('application-resources') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Application Resources
                    </a>

                    <a href="/careers"
                        class="nav-link block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('careers') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Careers
                    </a>

                    <a href="/contact-us"
                        class="nav-link block px-3 py-2.5 rounded-md transition-all duration-200
                        {{ request()->is('contact-us') ? 'bg-navy text-white shadow' : 'text-navy hover:bg-gray-100' }}">
                        Contact Us
                    </a>

                </nav>

                {{-- Bottom actions --}}
                <div class="space-y-2 pt-4 border-t border-gray-200 mt-auto">

                    {{-- Enquiry list: only appears once the visitor has added a product --}}
                    <a href="{{ route('enquiry-list') }}" x-cloak x-show="$store.enquiryList.count" x-transition.opacity
                        class="flex items-center justify-between gap-2 px-4 py-2.5 bg-ice text-navy border border-cyan/50 rounded-md
                               hover:border-cyan transition w-full"
                        style="box-shadow: inset 0 -2px 0 rgb(0 180 216 / 0.6);">
                        <span class="flex items-center gap-2 text-sm font-semibold">
                            <svg class="w-4 h-4 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm0 5.25h.007v.008H3.75V12Zm0 5.25h.007v.008H3.75v-.008Z"/></svg>
                            Enquiry List
                        </span>
                        <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-navy px-1.5 font-mono text-[11px] font-semibold text-white" x-text="$store.enquiryList.count"></span>
                    </a>

                    <a href="{{ route('search') }}"
                        class="flex items-center gap-2 px-4 py-2.5 bg-white text-navy border border-gray-300 rounded-md
                               hover:border-cyan hover:bg-ice transition w-full">
                        <svg class="w-4 h-4 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.3-4.3"/>
                        </svg>
                        Search
                    </a>

                    @php $catalogue = \App\Models\SiteSetting::latest()->first(); @endphp
                    @if ($catalogue?->catalogue_file)
                        <a href="{{ asset('storage/' . $catalogue->catalogue_file) }}" download
                            class="btn-primary flex items-center justify-between gap-2 px-4 py-2.5 h-11
                                   text-white font-medium rounded-lg w-full">
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

            <div class="page-fade relative flex flex-col min-h-screen">

                <main id="main" tabindex="-1" class="flex-grow mt-14 lg:mt-0">
                    @if (\App\Support\Preview::active(request()))
                        {{-- Staff preview of a page that may not be live yet --}}
                        <div class="sticky top-14 z-[55] flex flex-wrap items-center justify-between gap-x-4 gap-y-2 bg-navy px-5 py-2.5 text-white lg:top-0"
                             style="box-shadow: inset 0 -2px 0 #00B4D8;">
                            <p class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="font-mono text-[11px] font-medium uppercase tracking-[0.22em] text-cyan">Preview mode</span>
                                <span class="text-xs text-white/80">Only signed-in staff can see this view. Visitors see it only when it is Active.</span>
                            </p>
                            <a href="{{ url('/admin') }}" class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-white underline decoration-cyan underline-offset-4 hover:text-cyan">Back to admin</a>
                        </div>
                    @endif

                    {{ $slot }}
                </main>

                {{-- FOOTER: a rounded card that floats inside the page (not a full-width block) --}}
                @php
                    $fc = config('contact');
                    $fSales = $fc['departments'][0] ?? null;
                    $fHeading = 'relative mb-6 pb-3 text-sm font-bold uppercase tracking-wider text-white after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-9 after:rounded-full after:bg-cyan';
                    $fLink = 'inline-block text-sm text-white/70 transition hover:translate-x-1 hover:text-cyan';
                    $fSocial = 'flex h-10 w-10 items-center justify-center rounded-full text-white ring-1 ring-white/25 transition hover:-translate-y-0.5 hover:bg-cyan hover:text-navy hover:ring-cyan';
                @endphp
                <footer class="px-3 pb-3 pt-6 sm:px-5 sm:pb-5">
                    <div class="relative overflow-hidden rounded-[2rem] bg-navy text-white shadow-xl">
                        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(700px_320px_at_100%_0%,rgba(0,180,216,0.16),transparent_70%),radial-gradient(520px_260px_at_0%_100%,rgba(0,119,182,0.28),transparent_70%)]"></div>
                        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full border-[26px] border-white/5"></div>
                        <div class="pointer-events-none absolute -bottom-24 -left-16 h-60 w-60 rounded-full border-[24px] border-white/[0.04]"></div>

                        <div class="relative px-6 pb-8 pt-12 sm:px-10 lg:px-14">
                            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-12">

                                <div data-reveal class="lg:col-span-3">
                                    <a href="/" class="mb-5 inline-block">
                                        <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers"
                                            class="img-load h-14 w-auto brightness-0 invert object-contain" decoding="async" onload="this.classList.add('is-loaded')" {!! \App\Support\Img::publicAttrs('images/new-logo.png') !!}>
                                    </a>
                                    <p class="text-sm leading-7 text-white/70">
                                        43+ years of excellence in laboratory equipment, scientific instruments and chemicals.
                                        Serving pharma, biotech, diagnostics &amp; academia across India.
                                    </p>
                                    <p class="mt-6 font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-cyan">Follow us</p>
                                    <div class="mt-3 flex items-center gap-3">
                                        <a href="#" class="{{ $fSocial }}" aria-label="LinkedIn">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z"/></svg>
                                        </a>
                                        <a href="#" class="{{ $fSocial }}" aria-label="YouTube">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.5 6.19a3.02 3.02 0 00-2.12-2.14C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.38.55A3.02 3.02 0 00.5 6.19 31.8 31.8 0 000 12a31.8 31.8 0 00.5 5.81 3.02 3.02 0 002.12 2.14c1.87.55 9.38.55 9.38.55s7.5 0 9.38-.55a3.02 3.02 0 002.12-2.14A31.8 31.8 0 0024 12a31.8 31.8 0 00-.5-5.81zM9.75 15.02V8.98L15.5 12l-5.75 3.02z"/></svg>
                                        </a>
                                        <a href="#" class="{{ $fSocial }}" aria-label="WhatsApp">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07a8.2 8.2 0 01-2.41-1.49 9.06 9.06 0 01-1.67-2.08c-.17-.3-.02-.46.13-.61.14-.14.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51h-.57a1.1 1.1 0 00-.8.37A3.33 3.33 0 006 9.39a5.78 5.78 0 001.21 3.07 13.26 13.26 0 005.08 4.49 17.4 17.4 0 001.7.62 4.07 4.07 0 001.88.12 3.08 3.08 0 002.02-1.42 2.5 2.5 0 00.17-1.42c-.07-.12-.27-.2-.57-.34zM12.05 21.5A9.44 9.44 0 016.9 19.9l-.34-.2-3.54.93.95-3.47-.22-.36a9.45 9.45 0 01-1.45-5.04 9.5 9.5 0 0116.2-6.72A9.44 9.44 0 0121.5 12a9.5 9.5 0 01-9.45 9.5zm0-21A11.5 11.5 0 003.73 3.73a11.5 11.5 0 00-2.2 13.48L.05 24l6.96-1.45a11.5 11.5 0 005.04 1.17h.01A11.5 11.5 0 0012.05.5z"/></svg>
                                        </a>
                                    </div>
                                </div>

                                <div data-reveal class="lg:col-span-3">
                                    <h4 class="{{ $fHeading }}">Quick Links</h4>
                                    <ul class="space-y-3">
                                        <li><a href="/" class="{{ $fLink }}">Home</a></li>
                                        <li><a href="/our-story" class="{{ $fLink }}">Our Story</a></li>
                                        <li><a href="/verticals" class="{{ $fLink }}">Verticals</a></li>
                                        <li><a href="{{ route('insights.blogs') }}" class="{{ $fLink }}">Blogs</a></li>
                                        <li><a href="{{ route('insights.news') }}" class="{{ $fLink }}">News & Events</a></li>
                                        <li><a href="/application-resources" class="{{ $fLink }}">Application Resources</a></li>
                                        <li><a href="/careers" class="{{ $fLink }}">Careers</a></li>
                                        <li><a href="/contact-us" class="{{ $fLink }}">Contact Us</a></li>
                                    </ul>
                                </div>

                                <div data-reveal class="lg:col-span-3">
                                    <h4 class="{{ $fHeading }}">Our Verticals</h4>
                                    <ul class="space-y-3">
                                        @php $footerVerticals = \App\Models\Vertical::where('is_active', true)->orderBy('sort_order')->limit(6)->get(); @endphp
                                        @foreach ($footerVerticals as $v)
                                            <li><a href="{{ route('vertical.show', $v->slug) }}" class="{{ $fLink }}">{{ $v->name }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div data-reveal class="lg:col-span-3">
                                    <h4 class="{{ $fHeading }}">Contact Us</h4>
                                    <ul class="space-y-4">
                                        <li class="flex items-start gap-3">
                                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z"/>
                                            </svg>
                                            <span class="text-sm leading-relaxed text-white/70">{{ $fc['head_office']['address'] }}</span>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                            <a href="tel:{{ preg_replace('/\s+/', '', $fc['call']) }}" class="text-sm text-white/70 transition hover:text-cyan">{{ $fc['call'] }}</a>
                                        </li>
                                        <li class="flex items-start gap-3">
                                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75"/>
                                            </svg>
                                            <a href="mailto:{{ $fc['mail_24x7'] }}" class="break-all text-sm text-white/70 transition hover:text-cyan">{{ $fc['mail_24x7'] }}</a>
                                        </li>
                                    </ul>
                                </div>

                            </div>

                            {{-- Sales enquiries strip --}}
                            @if ($fSales)
                                <div data-reveal="fade" class="mt-12 flex flex-col gap-3 rounded-2xl bg-white/5 px-5 py-4 ring-1 ring-white/10 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-semibold text-white">{{ $fSales['title'] }}</p>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                        <a href="tel:{{ preg_replace('/\s+/', '', $fSales['phone']) }}" class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan hover:text-navy">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                            {{ $fSales['phone'] }}
                                        </a>
                                        <a href="mailto:{{ $fSales['email'] }}" class="inline-flex items-center gap-2 break-all rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan hover:text-navy">
                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75"/></svg>
                                            {{ $fSales['email'] }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            {{-- Bottom bar --}}
                            <div class="mt-6 flex flex-col items-center justify-between gap-3 rounded-3xl bg-white/5 px-6 py-3.5 ring-1 ring-white/10 sm:flex-row sm:rounded-full">
                                <p class="text-center text-xs text-white/60">&copy; {{ date('Y') }} Agarwal Brothers. All rights reserved.</p>
                                <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
                                    <a href="{{ route('privacy') }}" class="text-xs text-white/60 transition hover:text-white">Privacy Policy</a>
                                    <button type="button" x-data @click="$store.consent.reopen()" class="text-xs text-white/60 transition hover:text-white">Cookie settings</button>
                                    <a href="#" class="text-xs text-white/60 transition hover:text-white">Terms & Conditions</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </footer>

            </div>
        </div>
    </div>

    <x-cookie-consent />

    @unless (request()->routeIs('compare', 'enquiry-list'))
        <x-recently-viewed :current="request()->route('product')?->slug" />
    @endunless

    {{-- "Added to enquiry list" message --}}
    <div x-data x-cloak x-show="$store.enquiryList.toast" x-transition.opacity
         class="fixed inset-x-0 bottom-6 z-50 flex justify-center px-4 pr-20 pointer-events-none">
        <div class="pointer-events-auto flex items-center gap-4 rounded-xl bg-navy px-5 py-3 text-sm text-white shadow-xl" style="box-shadow: inset 0 -2px 0 #00B4D8, 0 18px 36px -16px rgba(11,37,69,0.6);">
            <span x-text="$store.enquiryList.toast"></span>
            <a href="{{ route('enquiry-list') }}" x-show="$store.enquiryList.count" class="whitespace-nowrap font-semibold text-cyan hover:text-white">View list &rarr;</a>
        </div>
    </div>

    @unless (request()->routeIs('compare'))
        <x-compare-tray />
    @endunless

    {{-- Floating action buttons --}}
    @php
        $wa = preg_replace('/\D/', '', (string) optional(\App\Models\SiteSetting::latest()->first())->whatsapp_number);
    @endphp
    <div x-data="{ top: false }"
         @scroll.window.passive="top = window.scrollY > 400"
         class="fixed bottom-5 right-4 z-40 flex flex-col items-center gap-3">

        <a href="/contact-us" aria-label="Contact us" title="Contact us"
           class="fab-navy h-12 w-12 rounded-full flex items-center justify-center hover:scale-110">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
            </svg>
        </a>

        <a href="{{ $wa ? 'https://wa.me/' . $wa : '/contact-us' }}" @if ($wa) target="_blank" rel="noopener" @endif
           aria-label="Chat on WhatsApp" title="Chat on WhatsApp"
           class="fab-green h-12 w-12 rounded-full flex items-center justify-center hover:scale-110">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/>
            </svg>
        </a>

        <button type="button" x-cloak x-show="top" x-transition.opacity
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                aria-label="Back to top" title="Back to top"
                class="fab-light h-12 w-12 rounded-full flex items-center justify-center hover:scale-110">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/>
            </svg>
        </button>
    </div>

</body>

</html>
