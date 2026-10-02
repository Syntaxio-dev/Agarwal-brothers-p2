<x-layouts.app :title="$title">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $title }}</span>
        </div>

        <div class="text-center py-20">
            <div class="mx-auto h-20 w-20 rounded-full bg-cyan/10 flex items-center justify-center mb-6">
                <svg class="h-10 w-10 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-navy">{{ $title }}</h1>
            <p class="mt-3 text-slate">This page is coming soon. Stay tuned!</p>
        </div>
    </div>
</x-layouts.app>
