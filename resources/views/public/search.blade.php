<x-layouts.app title="Search Results">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Search</span>
        </div>

        <form action="{{ route('search') }}" method="GET" class="relative max-w-xl mb-8"
            x-data="ghostSearch()" x-init="startGhost()">
            <input type="text" name="q" value="{{ $query }}" x-ref="input"
                :placeholder="ghost"
                @focus="stopGhost()" @blur="startGhost()"
                class="h-12 w-full rounded-full border border-gray-200 bg-ice pl-12 pr-5
                       text-base text-navy placeholder:text-slate/60
                       outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20
                       transition-all duration-200">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
            </svg>
        </form>

        @if ($query)
            <h1 class="text-2xl sm:text-3xl font-bold text-navy">
                Results for "<span class="text-link">{{ $query }}</span>"
            </h1>
            <p class="mt-2 text-slate">{{ $products->count() }} product{{ $products->count() !== 1 ? 's' : '' }} found</p>
        @else
            <h1 class="text-2xl sm:text-3xl font-bold text-navy">Search Products</h1>
            <p class="mt-2 text-slate">Search by product name, brand, or category.</p>
        @endif

        @if ($products->count())
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($products as $product)
                    <a href="{{ route('product.show', $product->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white
                               border border-gray-100 shadow-sm
                               hover:shadow-lg hover:border-cyan/30 hover:-translate-y-1
                               transition-all duration-300">

                        <div class="h-44 flex items-center justify-center overflow-hidden bg-ice p-4">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex flex-col items-center justify-center text-slate/40">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 p-5">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                @if ($product->category?->brand)
                                    <span class="inline-block rounded bg-cyan/10 px-2.5 py-0.5 font-mono text-[11px] font-medium uppercase tracking-wide text-link">
                                        {{ $product->category->brand->name }}
                                    </span>
                                @endif
                                @if ($product->category)
                                    <span class="inline-block rounded-full bg-navy/5 px-2.5 py-0.5 text-[11px] font-semibold text-slate">
                                        {{ $product->category->name }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors leading-snug">
                                {{ $product->name }}
                            </h3>

                            @if ($product->short_description)
                                <p class="mt-2 text-sm text-slate line-clamp-2 leading-relaxed">{{ $product->short_description }}</p>
                            @endif

                            <div class="mt-auto pt-3">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-link group-hover:text-navy transition-colors">
                                    View Details
                                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @elseif ($query)
            <div class="mt-12 text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-ice flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-slate/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <circle cx="11" cy="11" r="8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <p class="text-lg font-semibold text-navy">No products found for "{{ $query }}"</p>
                <p class="mt-2 text-sm text-slate max-w-md mx-auto">
                    Try searching by product name, brand (e.g. "BUCHI"), or category (e.g. "Rotary Evaporators").
                </p>
            </div>
        @endif
    </div>
</x-layouts.app>
