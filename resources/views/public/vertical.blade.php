<x-layouts.app :title="$vertical->seoTitle()" :description="$vertical->seoDescription()" :image="$vertical->seoImage()">
  <div class="relative overflow-x-clip">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[520px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.16),transparent_70%),radial-gradient(700px_380px_at_6%_16%,rgba(0,119,182,0.08),transparent_70%),radial-gradient(700px_380px_at_96%_22%,rgba(0,180,216,0.10),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-8 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [
            ['Home', '/'],
            ['Verticals', route('verticals.index')],
            [$vertical->name, null],
        ]])

        {{-- Header --}}
        <div data-reveal class="max-w-3xl">
            <span class="section-badge">Vertical</span>
            <h1 class="mt-4 text-[1.65rem] sm:text-4xl font-bold text-navy leading-tight">{{ $vertical->name }}</h1>
            @if ($vertical->description)
                <p class="mt-3 text-[15px] text-slate leading-relaxed sm:text-base">{{ $vertical->description }}</p>
            @endif

            @if ($brandGroups->count())
                <div class="mt-5 flex flex-wrap gap-2 font-mono text-xs text-navy">
                    <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5">{{ $brandGroups->count() }} {{ \Illuminate\Support\Str::plural('brand', $brandGroups->count()) }}</span>
                    <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5">{{ $brandGroups->sum(fn ($g) => $g['categories']->count()) }} product lines</span>
                </div>
            @endif
        </div>

        {{-- Jump to: only when there is more than one brand --}}
        @if ($brandGroups->count() > 1)
            <nav data-reveal class="mt-9 flex flex-wrap items-center gap-2" aria-label="Jump to a brand">
                <span class="mr-1 font-mono text-[10.5px] uppercase tracking-[0.2em] text-slate">Jump to</span>
                @foreach ($brandGroups as $group)
                    <a href="#brand-{{ $group['brand']->slug }}" class="chip">
                        {{ $group['brand']->name }}
                        <span class="ml-1.5 font-mono text-[11px] text-slate">{{ $group['categories']->count() }}</span>
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- One full-width row per brand --}}
        @if ($brandGroups->count())
            <div class="mt-8 flex flex-col gap-5 sm:mt-10">
                @foreach ($brandGroups as $group)
                    @php $brand = $group['brand']; $folds = $brandGroups->count() > 3; @endphp
                    {{-- no overflow-hidden on the row: the hover card must be able to grow past it. Phones: with more than 3 brands a row folds (the first one starts open). --}}
                    <div id="brand-{{ $brand->slug }}" data-reveal x-data="{ open: {{ ! $folds || $loop->first ? 'true' : 'false' }} }"
                         class="relative grid rounded-[20px] border border-gray-200 bg-white shadow-sm hover:z-20 focus-within:z-20 md:grid-cols-[300px_minmax(0,1fr)]">

                        {{-- left panel --}}
                        <div class="flex flex-col justify-between gap-5 border-b border-gray-200 bg-ice p-5 shadow-[inset_3px_0_0_#00B4D8] max-md:rounded-t-[20px] sm:p-7 md:gap-7 md:rounded-l-[20px] md:border-b-0 md:border-r">
                            <div class="flex items-start justify-between gap-3">
                                <a href="{{ route('brand.show', $brand->slug) }}" class="min-w-0" title="{{ $brand->name }}">
                                    @if ($brand->logo)
                                        <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-load h-[46px] max-w-full object-contain object-left mix-blend-multiply" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
                                    @else
                                        <span class="block text-[26px] font-bold leading-tight text-navy">{{ $brand->name }}</span>
                                    @endif
                                </a>
                                @if ($folds)
                                    <button type="button" @click="open = !open" :aria-expanded="open" aria-label="Show or hide the {{ $brand->name }} product lines"
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-navy transition active:bg-navy active:text-white md:hidden">
                                        <svg class="h-4 w-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
                                    </button>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-3 md:flex-col md:items-start md:gap-4">
                                <span class="rounded-md bg-cyan/10 px-2 py-1 font-mono text-[11px] text-link">
                                    {{ $group['categories']->count() }} {{ \Illuminate\Support\Str::plural('line', $group['categories']->count()) }}
                                </span>
                                <div class="flex flex-wrap items-center justify-end gap-x-4 gap-y-2 md:flex-col md:items-start md:gap-2">
                                    @if (($accessoryBrands['general'] ?? false) || in_array($brand->id, $accessoryBrands['ids'] ?? [], true))
                                        <a href="{{ route('brand.accessories', $brand->slug) }}" class="group inline-flex items-center gap-1.5 rounded-full border border-link/30 bg-white px-3 py-1 text-[12.5px] font-semibold text-link transition hover:border-navy hover:bg-navy hover:text-white">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085"/></svg>
                                            Accessories
                                        </a>
                                    @endif
                                    <a href="{{ route('brand.show', $brand->slug) }}" class="group inline-flex items-center gap-1 text-[13.5px] font-semibold text-link hover:text-navy">
                                        View {{ $brand->name }}
                                        <svg class="arrow-nudge h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- right panel: product-line tiles (28px padding so the hover card can grow sideways inside the row) --}}
                        <div class="grid grid-cols-1 content-start gap-3 p-4 sm:grid-cols-2 sm:p-7 lg:grid-cols-3" @if ($folds) :class="open ? '' : 'max-md:hidden'" @endif>
                            @foreach ($group['categories'] as $category)
                                @php $cardText = $category->cardText(); @endphp
                                <a href="{{ route('category.show', [$brand->slug, $category->slug, 'v' => $vertical->slug]) }}" class="line-tile group/tile">
                                    <span class="line-card">
                                        <span class="line-head">
                                            <span class="line-top">
                                                <span class="line-idx">{{ sprintf('%02d', $loop->iteration) }}</span>
                                                <svg class="line-chev h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                                            </span>
                                            <span class="line-name">{{ $category->name }}</span>
                                        </span>
                                        <span class="line-body" aria-hidden="true">
                                            @if ($category->image)
                                                <span class="line-pic block"><img src="{{ asset('storage/' . $category->image) }}" alt="" class="img-load" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($category->image) !!} onload="this.classList.add('is-loaded')"></span>
                                            @else
                                                <span class="line-pic line-pic-empty">
                                                    @if ($brand->logo)<img src="{{ asset('storage/' . $brand->logo) }}" alt="" loading="lazy" decoding="async">@endif
                                                </span>
                                            @endif
                                            <span class="line-text block">
                                                @if ($cardText)<span class="line-desc block line-clamp-3">{{ $cardText }}</span>@endif
                                                <span class="line-more">View category
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                                </span>
                                            </span>
                                        </span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div data-reveal class="mt-10 rounded-2xl border border-gray-100 bg-white py-16 text-center">
                <p class="text-lg font-semibold text-navy">No product lines in this vertical yet.</p>
                <p class="mt-2 text-sm text-slate">Check back soon, or tell us what you need.</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-5 inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Contact us</a>
            </div>
        @endif
    </div>
  </div>
</x-layouts.app>
