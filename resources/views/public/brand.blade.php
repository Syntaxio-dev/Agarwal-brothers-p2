<x-layouts.app :title="$brand->seoTitle()" :description="$brand->seoDescription()" :image="$brand->seoImage()">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $brand->name }}</span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-5">
            @if ($brand->logo)
                <div class="h-20 w-40 shrink-0 rounded-xl border border-gray-100 bg-white p-3 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="max-h-full max-w-full object-contain">
                </div>
            @endif
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">{{ $brand->name }}</h1>
                @if ($brand->country)
                    <p class="mt-1 text-sm font-semibold text-cyan">{{ $brand->country->name }}</p>
                @endif
            </div>
        </div>

        @if ($brand->description)
            <p class="mt-4 text-slate max-w-3xl leading-relaxed">{{ $brand->description }}</p>
        @endif

        @if ($brand->categories->count())
            <h2 class="mt-10 text-lg font-bold text-navy">Product Categories</h2>
            <div class="mt-4 flex flex-wrap gap-3">
                @foreach ($brand->categories as $category)
                    <a href="{{ route('category.show', [$brand->slug, $category->slug]) }}"
                        class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-navy
                               hover:border-cyan hover:text-link transition">
                        {{ $category->name }}
                        <span class="ml-1 text-xs text-slate">({{ $category->products_count }})</span>
                    </a>
                @endforeach
            </div>
        @endif

        <h2 class="mt-10 text-lg font-bold text-navy">Products</h2>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($products as $product)
                <a href="{{ route('product.show', $product->slug) }}"
                    class="group flex flex-col overflow-hidden rounded-xl bg-white border border-gray-100 shadow-sm
                           hover:shadow-lg hover:border-cyan/40 hover:-translate-y-1 transition-all duration-300">
                    <div class="h-40 bg-ice flex items-center justify-center p-4">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105">
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate">{{ $product->category?->name }}</p>
                        <h3 class="mt-1 text-sm font-bold text-navy group-hover:text-link transition-colors line-clamp-2">{{ $product->name }}</h3>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($products->isEmpty())
            <div class="mt-6 text-center py-16 text-slate">
                <p class="text-lg">No products available for this brand yet.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
