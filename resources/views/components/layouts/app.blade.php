<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Agarwal Brothers' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-ice text-navy flex min-h-screen">

    <aside class="w-[16%] min-w-[210px] max-w-[280px] bg-[#F4F9FB] text-navy
           flex-shrink-0 sticky top-0 h-screen overflow-y-auto
           border-r border-[#D9E2EA] shadow-sm">

        {{-- Logo --}}
        <div class="h-[82px] flex items-center justify-center px-4 border-b border-[#D9E2EA]">
            <a href="/" class="block">
                <img src="{{ asset('images/new-logo.png') }}" alt="Agarwal Brothers"
                    class="h-[70px] w-auto max-w-full object-contain">
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="px-3 py-4">

            {{-- Home --}}
            <a href="/" class="flex items-center w-full min-h-[42px] px-3 rounded-md text-[14px] font-semibold
           transition-all duration-200
           {{ request()->is('/') 
                ? 'bg-navy text-white' 
                : 'text-navy hover:bg-[#E5E9ED]' }}">
                Home
            </a>

            {{-- Our Story --}}
            <a href="/our-story" class="flex items-center w-full min-h-[42px] px-3 rounded-md text-[14px] font-semibold
           transition-all duration-200
           {{ request()->is('our-story') 
                ? 'bg-navy text-white' 
                : 'text-navy hover:bg-[#E5E9ED]' }}">
                Our Story
            </a>

            {{-- Verticals --}}
            <a href="/verticals" class="flex items-center w-full min-h-[42px] px-3 rounded-md text-[14px] font-semibold
           transition-all duration-200
           {{ request()->is('verticals*') 
                ? 'bg-navy text-white' 
                : 'text-navy hover:bg-[#E5E9ED]' }}">
                Verticals
            </a>

            {{-- Insights --}}
            <div x-data="{ open: false }">

                <button @click="open = !open" class="flex items-center justify-between w-full min-h-[42px] px-3 rounded-md
                       text-[14px] font-semibold text-navy
                       hover:bg-[#E5E9ED] transition-all duration-200">

                    <span>Insights &amp; Updates</span>

                    <span x-text="open ? '▲' : '▼'" class="text-[9px]">
                    </span>

                </button>

                <div x-show="open" x-collapse class="ml-3 pl-3 border-l border-[#D9E2EA]">

                    <a href="{{ route('insights.blogs') }}" class="block py-2 text-[13px] text-navy hover:text-link">
                        Blogs
                    </a>

                    <a href="{{ route('insights.news') }}" class="block py-2 text-[13px] text-navy hover:text-link">
                        News & Events
                    </a>

                    <a href="{{ route('insights.webinars') }}" class="block py-2 text-[13px] text-navy hover:text-link">
                        Webinars
                    </a>

                </div>
            </div>

            {{-- Application Resources --}}
            <a href="/application-resources" class="flex items-center w-full min-h-[42px] px-3 rounded-md text-[14px] font-semibold
           transition-all duration-200
           {{ request()->is('application-resources') 
                ? 'bg-navy text-white' 
                : 'text-navy hover:bg-[#E5E9ED]' }}">
                Application Resources
            </a>

            {{-- Careers --}}
            <a href="/careers" class="flex items-center w-full min-h-[42px] px-3 rounded-md text-[14px] font-semibold
           transition-all duration-200
           {{ request()->is('careers') 
                ? 'bg-navy text-white' 
                : 'text-navy hover:bg-[#E5E9ED]' }}">
                Careers
            </a>

            {{-- Contact Us --}}
            <a href="/contact-us" class="flex items-center w-full min-h-[42px] px-3 rounded-md text-[14px] font-semibold
           transition-all duration-200
           {{ request()->is('contact-us') 
                ? 'bg-navy text-white' 
                : 'text-navy hover:bg-[#E5E9ED]' }}">
                Contact Us
            </a>

        </nav>

        {{-- Bottom utilities --}}
        <div class="mt-auto px-3 pb-4">

            <div class="border-t border-[#D9E2EA] pt-4">

                {{-- Search --}}
                <form action="{{ route('search') }}" method="GET">
                    <div class="relative">

                        <input type="text" name="q" placeholder="Search for Products" class="w-full h-[38px] rounded-md
                               border border-[#C9D5DF]
                               bg-white px-3
                               text-[12px] text-navy
                               placeholder:text-slate
                               outline-none
                               focus:border-cyan
                               focus:ring-1 focus:ring-cyan/30">

                    </div>
                </form>

                {{-- Catalogue --}}
                @php
                $catalogue = \App\Models\SiteSetting::latest()->first();
                @endphp

                @if ($catalogue?->catalogue_file)

                <a href="{{ asset('storage/' . $catalogue->catalogue_file) }}" download class="flex items-center justify-center
                           w-full h-[38px] mt-3
                           rounded-md
                           bg-navy text-white
                           text-[12px] font-semibold
                           hover:bg-[#12385F]
                           transition-all duration-200">

                    Download Catalogue

                </a>

                @endif

            </div>

        </div>

    </aside>

    <main class="flex-1">
        <div class="{{ str_starts_with(request()->path(), 'verticals') || request()->path() === '/' ? '' : 'p-8' }}">
            {{ $slot }}
        </div>
    </main>

</body>

</html>