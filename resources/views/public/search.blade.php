@php
    $total = $products->count();
    $hasAny = $total || $brands->count() || $lines->count() || $verticals->count();
@endphp

<x-layouts.app title="Search Results" :noindex="true" description="Search laboratory equipment, instruments and chemicals from Agarwal Brothers.">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[520px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.16),transparent_70%),radial-gradient(700px_380px_at_6%_16%,rgba(0,119,182,0.08),transparent_70%),radial-gradient(700px_380px_at_96%_22%,rgba(0,180,216,0.10),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-10 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [['Home', '/'], ['Search', null]]])

        {{-- Heading --}}
        <div class="max-w-3xl">
            <span class="section-badge">Search</span>
            @if ($query !== '')
                <h1 class="mt-4 text-3xl sm:text-4xl font-bold leading-tight text-navy">
                    Results for <span class="text-cyan">&ldquo;{{ $query }}&rdquo;</span>
                </h1>
                <p class="mt-3 flex flex-wrap items-center gap-2 text-sm text-slate">
                    <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 font-mono text-xs text-navy">
                        {{ $total }} {{ \Illuminate\Support\Str::plural('product', $total) }}
                    </span>
                    @if ($brands->count())
                        <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 font-mono text-xs text-navy">{{ $brands->count() }} {{ \Illuminate\Support\Str::plural('brand', $brands->count()) }}</span>
                    @endif
                    @if ($lines->count())
                        <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5 font-mono text-xs text-navy">{{ $lines->count() }} product {{ \Illuminate\Support\Str::plural('line', $lines->count()) }}</span>
                    @endif
                </p>
            @else
                <h1 class="mt-4 text-3xl sm:text-4xl font-bold leading-tight text-navy">
                    Find <span class="text-cyan">instruments, brands</span> and product lines
                </h1>
                <p class="mt-3 text-base text-slate leading-relaxed">Search by product name, brand or category, or browse by vertical below.</p>
            @endif
        </div>

        {{-- Search bar --}}
        <form action="{{ route('search') }}" method="GET" role="search"
              x-data="ghostSearch()" x-init="startGhost()"
              class="mt-7 flex max-w-2xl items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm transition focus-within:border-cyan focus-within:ring-2 focus-within:ring-cyan/20">
            <svg class="ml-3 h-5 w-5 shrink-0 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
            </svg>
            <input type="text" name="q" value="{{ $query }}" x-ref="input" maxlength="80" autocomplete="off" aria-label="Search"
                   :placeholder="ghost" @focus="stopGhost()" @blur="startGhost()"
                   class="min-w-0 flex-1 bg-transparent px-1 py-2.5 text-base text-navy placeholder:text-slate/60 outline-none">
            @if ($query !== '')
                <a href="{{ route('search') }}" aria-label="Clear search" title="Clear"
                   class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate transition-colors hover:bg-ice hover:text-navy">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </a>
            @endif
            <button type="submit" class="btn-primary shrink-0 rounded-lg px-6 py-2.5 text-sm font-semibold text-white">Search</button>
        </form>

        @if ($query !== '' && $hasAny)

            {{-- Matching brands --}}
            @if ($brands->count())
                <section class="mt-12">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-bold text-navy sm:text-xl">Brands</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-3">
                        @foreach ($brands as $brand)
                            <a href="{{ route('brand.show', $brand->slug) }}"
                               class="group flex items-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-cyan/60 hover:shadow-md">
                                <span class="flex h-10 w-14 items-center justify-center overflow-hidden rounded-lg bg-ice p-1">
                                    @if ($brand->logo)
                                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }} logo" class="max-h-full max-w-full object-contain">
                                    @else
                                        <span class="text-xs font-bold text-navy">{{ \Illuminate\Support\Str::substr($brand->name, 0, 2) }}</span>
                                    @endif
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-navy transition-colors group-hover:text-link">{{ $brand->name }}</span>
                                    <span class="block font-mono text-[11px] text-slate">{{ $brand->categories_count }} {{ \Illuminate\Support\Str::plural('line', $brand->categories_count) }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Matching product lines --}}
            @if ($lines->count())
                <section class="mt-10">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-bold text-navy sm:text-xl">Product lines</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($lines as $line)
                            <a href="{{ route('category.show', [$line->brand->slug, $line->slug]) }}"
                               class="chip !pl-3.5 hover:text-link">
                                {{ $line->name }}
                                <span class="text-slate">&middot; {{ $line->brand->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Matching verticals --}}
            @if ($verticals->count())
                <section class="mt-10">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-bold text-navy sm:text-xl">Verticals</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($verticals as $vertical)
                            <a href="{{ route('vertical.show', $vertical->slug) }}" class="chip !pl-3.5 hover:text-link">{{ $vertical->name }}</a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Products --}}
            @if ($total)
                <section class="mt-12">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-bold text-navy sm:text-xl">Products</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($products as $product)
                            <a href="{{ route('product.show', $product->slug) }}"
                               class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan/50 hover:shadow-xl">
                                <div class="flex h-48 items-center justify-center bg-ice p-5">
                                    @if ($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                             class="max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105">
                                    @else
                                        <svg class="h-12 w-12 text-slate/25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/>
                                        </svg>
                                    @endif
                                </div>

                                <div class="flex flex-1 flex-col p-5">
                                    @if ($product->category?->brand)
                                        <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-link">{{ $product->category->brand->name }}</p>
                                    @endif
                                    <h3 class="mt-1.5 text-base font-bold leading-snug text-navy transition-colors group-hover:text-link">{{ $product->name }}</h3>
                                    @if ($product->category)
                                        <p class="mt-1 text-xs text-slate">{{ $product->category->name }}</p>
                                    @endif
                                    @if ($product->short_description)
                                        <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate">{{ $product->short_description }}</p>
                                    @endif
                                    <span class="mt-auto pt-4 text-xs font-semibold text-link">View details &rarr;</span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if ($total >= 60)
                        <p class="mt-6 text-center text-sm text-slate">Showing the first 60 matches. Refine your search to narrow it down.</p>
                    @endif
                </section>
            @endif

        @else
            {{-- Nothing searched yet, or nothing matched --}}
            @if ($query !== '')
                <div class="mt-12 rounded-2xl border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-xl bg-ice text-link">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                    </div>
                    <p class="text-lg font-semibold text-navy">No matches for &ldquo;{{ $query }}&rdquo;</p>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate">
                        Check the spelling, or try a brand (for example &ldquo;Shimadzu&rdquo;) or a category (for example &ldquo;Centrifuges&rdquo;).
                        If you cannot find what you need, we can source it for you.
                    </p>
                    <a href="{{ route('contact') }}" class="btn-primary mt-6 inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">
                        Ask our team
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
                    </a>
                </div>
            @endif

            @if ($suggestVerticals->count())
                <section class="mt-12">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-bold text-navy sm:text-xl">Browse by vertical</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($suggestVerticals as $v)
                            <a href="{{ route('vertical.show', $v->slug) }}" class="chip !pl-3.5 hover:text-link">{{ $v->name }}</a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($suggestBrands->count())
                <section class="mt-10">
                    <div class="flex items-center gap-4">
                        <h2 class="text-lg font-bold text-navy sm:text-xl">Popular brands</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2.5">
                        @foreach ($suggestBrands as $b)
                            <a href="{{ route('search', ['q' => $b->name]) }}" class="chip !pl-3.5 hover:text-link">{{ $b->name }}</a>
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
    </div>
  </div>
</x-layouts.app>
