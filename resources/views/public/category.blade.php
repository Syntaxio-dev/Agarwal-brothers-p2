<x-layouts.app :title="$category->name">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy">{{ $brand->name }}</span>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $category->name }}</span>
        </div>

        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">{{ $category->name }}</h1>
        <p class="mt-1 text-cyan font-semibold">{{ $brand->name }}</p>
        @if ($category->description)
            <p class="mt-3 text-slate max-w-3xl">{{ $category->description }}</p>
        @endif

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($category->products as $product)
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
                        <h3 class="text-base font-bold text-navy group-hover:text-link transition-colors">
                            {{ $product->name }}
                        </h3>
                        @if ($product->short_description)
                            <p class="mt-2 text-sm text-slate line-clamp-2">{{ $product->short_description }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        @if ($category->products->isEmpty())
            <div class="mt-10 text-center py-16 text-slate">
                <p class="text-lg">No products available in this category yet.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
