@php
    $crumbs = [['Home', '/']];
    if ($vertical) {
        $crumbs[] = ['Verticals', route('verticals.index')];
        $crumbs[] = [$vertical->name, route('vertical.show', $vertical->slug)];
    } else {
        $crumbs[] = [$brand->name, route('brand.show', $brand->slug)];
    }
    $crumbs[] = [$category->name, null];

    $q = $vertical ? ['v' => $vertical->slug] : [];

    // Filter bar only when the list is long enough to need it
    $filterable = $products->count() >= 8;
    $filterData = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'group' => $p->model_group ?: 'Models',
        'text' => mb_strtolower($p->name . ' ' . $p->short_description),
    ])->values();
@endphp

<x-layouts.app :title="$category->seoTitle()" :description="$category->seoDescription()" :image="$category->seoImage()">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[480px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.14),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-10 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => $crumbs])

        {{-- Title banner: category on the left, brand on the right --}}
        <div class="flex flex-col items-start justify-between gap-5 rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-ice px-6 py-6 shadow-sm sm:flex-row sm:items-center sm:px-10 sm:py-8">
            <div>
                <p class="font-mono text-xs font-medium uppercase tracking-[0.2em] text-link">{{ $brand->name }}</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-navy leading-tight">{{ $category->name }}</h1>
            </div>
            <a href="{{ route('brand.show', $brand->slug) }}" class="flex h-16 w-44 shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-white p-3" title="{{ $brand->name }}">
                @if ($brand->logo)
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-load max-h-full max-w-full object-contain" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
                @else
                    <span class="text-base font-bold text-navy">{{ $brand->name }}</span>
                @endif
            </a>
        </div>

        {{-- Heading + intro --}}
        @if ($category->heading || $category->description)
            <div class="mx-auto mt-12 max-w-4xl text-center">
                @if ($category->heading)
                    <h2 class="text-2xl sm:text-3xl font-bold text-link leading-tight">{{ $category->heading }}</h2>
                @endif
                @if ($category->description)
                    <p class="mt-4 text-base leading-relaxed text-slate">{{ $category->description }}</p>
                @endif
            </div>
        @endif

        {{-- Models, grouped --}}
        @if ($products->count())
            <div class="mt-14" @if ($filterable) x-data="catalogueFilter(@js($filterData))" @endif>
                <div class="flex items-center gap-4">
                    <h2 class="text-xl sm:text-2xl font-bold text-navy">Model Categories</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                </div>

                @if ($filterable)
                    {{-- Find a model quickly: keyword, model group and sorting --}}
                    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <label class="relative block flex-1">
                                <span class="sr-only">Search these models</span>
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                                <input type="search" x-model.debounce.150ms="q" placeholder="Search these models" maxlength="80" autocomplete="off"
                                       class="h-11 w-full rounded-xl border border-gray-200 bg-white pl-12 pr-4 text-sm text-navy placeholder:text-slate outline-none transition">
                            </label>
                            <label class="flex items-center gap-2">
                                <span class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-slate">Sort</span>
                                <select x-model="sort" class="h-11 min-w-[11rem] rounded-xl border border-gray-200 bg-white px-4 text-sm text-navy outline-none transition">
                                    <option value="default">Recommended</option>
                                    <option value="az">Name A to Z</option>
                                    <option value="za">Name Z to A</option>
                                    <option value="newest">Newest first</option>
                                </select>
                            </label>
                        </div>

                        @if ($groups->count() > 1)
                            <div class="mt-3 flex flex-wrap gap-2" role="group" aria-label="Model group">
                                <button type="button" class="chip" :class="{ 'chip-active': group === 'all' }" @click="group = 'all'" :aria-pressed="group === 'all'">All models</button>
                                @foreach ($groups as $groupName => $items)
                                    <button type="button" class="chip" :class="{ 'chip-active': group === @js($groupName) }" @click="group = @js($groupName)"
                                            :aria-pressed="group === @js($groupName)">{{ $groupName }} <span class="opacity-70">({{ $items->count() }})</span></button>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-3 flex items-center justify-between gap-3 border-t border-gray-100 pt-3 text-xs text-slate" aria-live="polite">
                            <p>Showing <span class="font-semibold text-navy" x-text="shown"></span> of {{ $products->count() }} models</p>
                            <button type="button" x-show="active" x-cloak @click="reset()" class="font-semibold text-link hover:text-navy">Clear filters</button>
                        </div>
                    </div>

                    <div x-cloak x-show="shown === 0" class="mt-8 rounded-2xl border border-gray-200 bg-white px-6 py-12 text-center">
                        <p class="text-base font-semibold text-navy">No models match your filters.</p>
                        <button type="button" @click="reset()" class="btn-ghost mt-4">Clear filters</button>
                    </div>
                @endif

                @foreach ($groups as $groupName => $items)
                    <div class="mt-8" @if ($filterable) x-show="groupShown(@js($groupName)) > 0" @endif>
                        <h3 class="mb-5 text-center font-mono text-sm font-medium uppercase tracking-[0.2em] text-link">{{ $groupName }}</h3>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($items as $product)
                                <div class="relative flex" @if ($filterable) x-show="matches({{ $product->id }})" :style="{ order: order({{ $product->id }}) }" @endif>
                                <a href="{{ route('product.show', [$product->slug] + $q) }}"
                                   class="w-full group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan/50 hover:shadow-xl">
                                    <div class="flex h-48 items-center justify-center bg-ice p-5">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                                 class="img-load max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($product->image) !!} onload="this.classList.add('is-loaded')">
                                        @else
                                            <svg class="h-12 w-12 text-slate/25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v14.25c0 .828.672 1.5 1.5 1.5Z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex flex-1 flex-col p-5">
                                        <h4 class="text-base font-bold text-navy transition-colors group-hover:text-link">{{ $product->name }}</h4>
                                        @if ($product->short_description)
                                            <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-slate">{{ $product->short_description }}</p>
                                        @endif
                                        <span class="mt-auto pt-4 text-xs font-semibold text-link">View details &rarr;</span>
                                    </div>
                                </a>
                                    @include('public.partials.compare-toggle', ['product' => $product])
                                    @include('public.partials.enquiry-list-toggle', ['product' => $product])
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-12 rounded-2xl border border-gray-100 bg-white py-16 text-center">
                <p class="text-lg font-semibold text-navy">Models for this range are coming soon.</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-5 inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Ask our team</a>
                <x-help-links :verticals="false" />
            </div>
        @endif

        {{-- Long-form content --}}
        @if (filled($category->content))
            <div class="rich-text mx-auto mt-14 max-w-4xl">
                {!! \Illuminate\Support\Str::sanitizeHtml((string) $category->content) !!}
            </div>
        @endif

        @include('public.partials.faq', ['faqs' => $category->faqs, 'title' => 'Frequently Asked Questions about ' . $category->name])

        {{-- CTA --}}
        <div class="mt-16 flex flex-col items-center justify-between gap-5 rounded-3xl bg-gradient-to-br from-navy to-link px-8 py-9 sm:flex-row sm:px-12">
            <div class="text-center sm:text-left">
                <h3 class="text-xl font-bold text-white sm:text-2xl">Need help choosing the right {{ strtolower($category->name) }}?</h3>
                <p class="mt-1.5 text-sm text-white/75">Our specialists can recommend a model for your application.</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-glass shrink-0 px-7 py-3">Talk to an expert</a>
        </div>
    </div>
  </div>
</x-layouts.app>
