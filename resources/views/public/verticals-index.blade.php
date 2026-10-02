<x-layouts.app title="Explore Our Scientific Verticals">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Verticals</span>
        </div>

        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">
            Explore Our <span class="text-cyan">Scientific Verticals</span>
        </h1>
        <p class="mt-3 text-slate max-w-2xl">
            From research to production, discover how our solutions support every lab need.
        </p>

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($verticals as $vertical)
                <a href="{{ route('vertical.show', $vertical->slug) }}"
                    class="group flex flex-col overflow-hidden rounded-2xl bg-white
                           ring-1 ring-gray-100 shadow-sm
                           hover:shadow-xl hover:ring-cyan/30 hover:-translate-y-1
                           transition-all duration-300">

                    <div class="h-40 overflow-hidden bg-gray-50">
                        @if ($vertical->image)
                            <img src="{{ asset('storage/' . $vertical->image) }}" alt="{{ $vertical->name }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <div class="h-14 w-14 rounded-full bg-cyan/10 flex items-center justify-center">
                                    <svg class="h-7 w-7 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex-1 p-5">
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors">{{ $vertical->name }}</h3>
                        @if ($vertical->description)
                            <p class="mt-2 text-sm text-slate line-clamp-2">{{ $vertical->description }}</p>
                        @endif
                    </div>

                    <div class="px-5 py-3 bg-navy text-center text-sm font-semibold text-white
                                group-hover:bg-cyan group-hover:text-navy transition-colors duration-300">
                        Know More →
                    </div>
                </a>
            @endforeach
        </div>

        @if ($verticals->isEmpty())
            <div class="mt-10 text-center py-16 text-slate">
                <p class="text-lg">No scientific verticals available yet.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
