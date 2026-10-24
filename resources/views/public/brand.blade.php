<x-layouts.app :title="$brand->seoTitle()" :description="$brand->seoDescription()" :image="$brand->seoImage()">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-8 sm:py-14 lg:py-16">

        @include('public.partials.breadcrumb', ['items' => [
            ['Home', '/'],
            ['Verticals', route('verticals.index')],
            [$brand->name, null],
        ]])

        <div data-reveal class="flex flex-col sm:flex-row sm:items-center gap-5 max-sm:flex-row max-sm:items-center max-sm:gap-4">
            @if ($brand->logo)
                <div class="h-20 w-40 shrink-0 rounded-xl border border-gray-100 bg-white p-3 flex items-center justify-center max-sm:h-14 max-sm:w-28 max-sm:p-2">
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-load max-h-full max-w-full object-contain" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
                </div>
            @endif
            <div>
                <h1 class="text-[1.65rem] sm:text-3xl lg:text-4xl font-bold text-navy">{{ $brand->name }}</h1>
                @if ($brand->country)
                    <p class="mt-1 text-sm font-semibold text-cyan-ink">{{ $brand->country->name }}</p>
                @endif
            </div>
        </div>

        @if ($brand->description)
            <p data-reveal class="mt-4 text-[15px] text-slate max-w-3xl leading-relaxed sm:text-base">{{ $brand->description }}</p>
        @endif

        @if ($brand->categories->count())
            <h2 data-reveal class="mt-8 sm:mt-10 text-lg font-bold text-navy">Product categories</h2>
            <div class="mt-4 flex flex-wrap gap-3 max-sm:-mx-4 max-sm:flex-nowrap max-sm:gap-2 max-sm:overflow-x-auto max-sm:px-4 max-sm:pb-1 max-sm:[scrollbar-width:none] max-sm:[&::-webkit-scrollbar]:hidden">
                @foreach ($brand->categories as $category)
                    <a data-reveal="zoom" href="{{ route('category.show', [$brand->slug, $category->slug]) }}"
                        class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-navy max-sm:shrink-0 max-sm:whitespace-nowrap
                               hover:border-cyan hover:text-link transition">
                        {{ $category->name }}
                        <span class="ml-1 text-xs text-slate">({{ $category->products_count }})</span>
                    </a>
                @endforeach
            </div>
        @endif

        <h2 data-reveal class="mt-8 sm:mt-10 text-lg font-bold text-navy">Products <span class="ml-1 font-mono text-[11px] font-medium uppercase tracking-widest text-slate sm:hidden">{{ $products->count() }} items</span></h2>
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 max-sm:grid-cols-2 max-sm:gap-3">
            @foreach ($products as $product)
                <div data-reveal class="group transition-all duration-300 hover:-translate-y-1 relative flex">
                <a href="{{ route('product.show', $product->slug) }}"
                    class="w-full group flex flex-col overflow-hidden rounded-xl bg-white border border-gray-100 shadow-sm
                           group-hover:shadow-lg group-hover:border-cyan/40 transition-all duration-300">
                    <div class="h-40 bg-ice flex items-center justify-center p-2 max-sm:h-32">
                        @if ($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                class="img-load max-h-full max-w-full mix-blend-multiply object-contain transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($product->image) !!} onload="this.classList.add('is-loaded')">
                        @endif
                    </div>
                    <div class="p-4 max-sm:p-3 max-sm:pb-14">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate max-sm:line-clamp-1 max-sm:text-[10px]">{{ $product->category?->name }}</p>
                        <h3 class="mt-1 text-sm font-bold text-navy group-hover:text-link transition-colors line-clamp-2">{{ $product->name }}</h3>
                    </div>
                </a>
                    @include('public.partials.compare-toggle', ['product' => $product])
                    @include('public.partials.enquiry-list-toggle', ['product' => $product])
                </div>
            @endforeach
        </div>

        @if ($products->isEmpty())
            <div data-reveal="fade" class="mt-6 text-center py-16 text-slate">
                <p class="text-lg">No products available for this brand yet.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
