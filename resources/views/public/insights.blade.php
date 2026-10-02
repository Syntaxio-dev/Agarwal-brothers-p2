<x-layouts.app :title="$title">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $title }}</span>
        </div>

        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">{{ $title }}</h1>

        @if ($insights->count())
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($insights as $insight)
                    <a href="{{ route('insights.show', $insight->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white
                               ring-1 ring-gray-100 shadow-sm
                               hover:shadow-xl hover:ring-cyan/30 hover:-translate-y-1
                               transition-all duration-300">

                        <div class="h-48 overflow-hidden bg-gray-50">
                            @if ($insight->image)
                                <img src="{{ asset('storage/' . $insight->image) }}" alt="{{ $insight->title }}"
                                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-ice">
                                    <svg class="h-10 w-10 text-slate/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 p-5">
                            @if ($insight->event_date)
                                <p class="text-xs font-semibold text-link mb-2">{{ $insight->event_date->format('d M Y') }}</p>
                            @endif
                            <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors leading-snug">
                                {{ $insight->title }}
                            </h3>
                            @if ($insight->excerpt)
                                <p class="mt-2 text-sm text-slate line-clamp-3 leading-relaxed">{{ $insight->excerpt }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="mt-12 text-center py-16">
                <p class="text-lg font-semibold text-navy">Nothing here yet</p>
                <p class="mt-2 text-sm text-slate">Check back later for updates.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
