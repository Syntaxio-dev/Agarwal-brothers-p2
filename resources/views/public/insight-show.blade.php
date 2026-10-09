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

        @if ($insight->pdf)
            @php $pdfUrl = asset('storage/' . $insight->pdf); @endphp
            <div class="mt-8 rounded-2xl bg-gradient-to-br from-ice to-cyan/10 border border-cyan/20 p-5 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm">
                        <svg class="h-6 w-6 text-alert" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-base font-bold text-navy">Attached document</h2>
                        <p class="text-sm text-slate">Read it below or download the PDF.</p>
                    </div>
                    <a href="{{ $pdfUrl }}" download target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-2 rounded-full bg-navy px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        Download PDF
                    </a>
                </div>

                <iframe src="{{ $pdfUrl }}" title="{{ $insight->title }} (PDF)"
                        class="mt-5 hidden sm:block w-full h-[75vh] rounded-xl border border-gray-200 bg-white"></iframe>
            </div>
        @endif

        @if (filled($insight->content))
        <div class="mt-8 prose prose-lg max-w-none
                    prose-headings:text-navy prose-headings:font-bold
                    prose-p:text-slate prose-p:leading-relaxed
                    prose-a:text-link prose-a:no-underline hover:prose-a:underline
                    prose-img:rounded-xl">
            {!! $insight->content !!}
        </div>
        @endif
    </article>
</x-layouts.app>
