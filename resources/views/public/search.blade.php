<x-layouts.app title="Search Results">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Search</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-navy">
            Search results for "<span class="text-link">{{ $query }}</span>"
        </h1>
        <p class="mt-2 text-slate">{{ $products->count() }} product{{ $products->count() !== 1 ? 's' : '' }} found</p>

        @if ($products->count())
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <a href="{{ route('product.show', $product->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white
                               ring-1 ring-gray-100 shadow-sm
                               hover:shadow-lg hover:ring-cyan/30 hover:-translate-y-1
                               transition-all duration-300">

                        <div class="h-44 flex items-center justify-center overflow-hidden bg-ice p-4">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                    class="max-h-full max-w-full object-contain
                                           transition-transform duration-500 group-hover:scale-105">
                            @else
                                <span class="text-sm text-slate">No image</span>
                            @endif
                        </div>

                        <div class="p-5">
                            @if ($product->category?->brand)
                                <p class="text-xs font-bold uppercase tracking-wide text-cyan">
                                    {{ $product->category->brand->name }}
                                </p>
                            @endif
                            <h3 class="mt-1 text-base font-bold text-navy group-hover:text-link transition-colors">
                                {{ $product->name }}
                            </h3>
                            @if ($product->short_description)
                                <p class="mt-2 text-sm text-slate line-clamp-2">{{ $product->short_description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="mt-12 text-center py-16">
                <div class="mx-auto h-16 w-16 rounded-full bg-ice flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m2.1-5.4a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                    </svg>
                </div>
                <p class="text-lg font-semibold text-navy">No products found</p>
                <p class="mt-2 text-sm text-slate">Try searching with different keywords.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
