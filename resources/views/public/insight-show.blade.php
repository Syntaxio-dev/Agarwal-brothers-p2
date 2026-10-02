<x-layouts.app :title="$insight->title">
    <article class="w-[98%] mx-auto max-w-4xl px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('insights.blogs') }}" class="hover:text-link transition">Insights</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium line-clamp-1">{{ $insight->title }}</span>
        </div>

        @if ($insight->image)
            <img src="{{ asset('storage/' . $insight->image) }}" alt="{{ $insight->title }}"
                class="w-full max-h-[400px] object-cover rounded-2xl shadow-sm mb-8">
        @endif

        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy leading-tight">
            {{ $insight->title }}
        </h1>

        @if ($insight->event_date)
            <div class="mt-3 inline-flex items-center gap-2 rounded-full bg-cyan/10 px-4 py-1.5 text-sm font-semibold text-link">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                {{ $insight->event_date->format('d M Y') }}
            </div>
        @endif

        <div class="mt-8 prose prose-lg max-w-none
                    prose-headings:text-navy prose-headings:font-bold
                    prose-p:text-slate prose-p:leading-relaxed
                    prose-a:text-link prose-a:no-underline hover:prose-a:underline
                    prose-img:rounded-xl">
            {!! $insight->content !!}
        </div>
    </article>
</x-layouts.app>
