<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Agarwal Brothers' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-ice text-navy flex min-h-screen">

    <aside class="w-64 bg-navy text-white flex-shrink-0 sticky top-0 h-screen overflow-y-auto">
        <div class="p-4 font-bold text-lg border-b border-white/10">Agarwal Brothers</div>
        <nav class="p-4 space-y-2">
            <a href="/" class="block py-2 hover:text-cyan">Home</a>
            <a href="/our-story" class="block py-2 hover:text-cyan">Our Story</a>
            <a href="/verticals" class="block py-2 hover:text-cyan">Verticals</a>
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex justify-between items-center py-2 hover:text-cyan">
                    Insights &amp; Updates
                    <span x-text="open ? '▲' : '▼'" class="text-xs"></span>
                </button>
                <div x-show="open" x-collapse class="pl-4 space-y-1">
                    <a href="{{ route('insights.blogs') }}" class="block py-1 text-sm hover:text-cyan">Blogs</a>
                    <a href="{{ route('insights.news') }}" class="block py-1 text-sm hover:text-cyan">News & Events</a>
                    <a href="{{ route('insights.webinars') }}" class="block py-1 text-sm hover:text-cyan">Webinars</a>
                </div>
            </div>
            <a href="/application-resources" class="block py-2 hover:text-cyan">Application Resources</a>
            <a href="/careers" class="block py-2 hover:text-cyan">Careers</a>
            <a href="/contact-us" class="block py-2 hover:text-cyan">Contact Us</a>
        </nav>
        <div class="p-4 border-t border-white/10">
            <form action="{{ route('search') }}" method="GET" class="p-4 border-t border-white/10">
                <input type="text" name="q" placeholder="Search for Products"
                    class="w-full rounded px-3 py-2 bg-white text-navy text-sm">
            </form>
        </div>
        <div class="p-4">
            @php $catalogue = \App\Models\SiteSetting::latest()->first(); @endphp
            @if ($catalogue?->catalogue_file)
            <a href="{{ asset('storage/' . $catalogue->catalogue_file) }}" download
                class="block w-full text-center bg-cyan text-navy font-medium rounded py-2">
                Download Catalogue
            </a>
            @endif
     
        </div>
    </aside>

    <main class="flex-1 p-8">
        {{ $slot }}
    </main>

</body>

</html>