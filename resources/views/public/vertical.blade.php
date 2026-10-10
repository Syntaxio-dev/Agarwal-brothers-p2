<x-layouts.app :title="$vertical->seoTitle()" :description="$vertical->seoDescription()" :image="$vertical->seoImage()">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[520px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.16),transparent_70%),radial-gradient(700px_380px_at_6%_16%,rgba(0,119,182,0.08),transparent_70%),radial-gradient(700px_380px_at_96%_22%,rgba(0,180,216,0.10),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-10 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [
            ['Home', '/'],
            ['Verticals', route('verticals.index')],
            [$vertical->name, null],
        ]])

        {{-- Header --}}
        <div class="max-w-3xl">
            <span class="section-badge">Vertical</span>
            <h1 class="mt-4 text-3xl sm:text-4xl font-bold text-navy leading-tight">{{ $vertical->name }}</h1>
            @if ($vertical->description)
                <p class="mt-3 text-base text-slate leading-relaxed">{{ $vertical->description }}</p>
            @endif

            @if ($brandGroups->count())
                <div class="mt-5 flex flex-wrap gap-2 font-mono text-xs text-navy">
                    <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5">{{ $brandGroups->count() }} {{ \Illuminate\Support\Str::plural('brand', $brandGroups->count()) }}</span>
                    <span class="rounded-md border border-gray-200 bg-white px-3 py-1.5">{{ $brandGroups->sum(fn ($g) => $g['categories']->count()) }} product lines</span>
                </div>
            @endif
        </div>

        {{-- Brand-wise menu --}}
        @if ($brandGroups->count())
            <div class="mt-10 grid grid-cols-1 items-start gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($brandGroups as $group)
                    @php $brand = $group['brand']; @endphp
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all duration-300 hover:border-cyan/50 hover:shadow-lg">

                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('brand.show', $brand->slug) }}" class="flex h-12 min-w-0 items-center" title="{{ $brand->name }}">
                                @if ($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-load max-h-full max-w-[160px] object-contain" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
                                @else
                                    <span class="truncate text-lg font-bold text-navy">{{ $brand->name }}</span>
                                @endif
                            </a>
                            <span class="shrink-0 rounded-md bg-cyan/10 px-2 py-1 font-mono text-[11px] text-link">
                                {{ $group['categories']->count() }} {{ \Illuminate\Support\Str::plural('line', $group['categories']->count()) }}
                            </span>
                        </div>

                        <ul class="mt-4 space-y-2">
                            @foreach ($group['categories'] as $category)
                                <li>
                                    <a href="{{ route('category.show', [$brand->slug, $category->slug, 'v' => $vertical->slug]) }}"
                                       class="group flex items-center justify-between gap-3 rounded-lg bg-ice px-3.5 py-2.5 text-sm font-medium text-navy
                                              transition-colors duration-200 hover:bg-navy hover:text-white">
                                        <span class="min-w-0 truncate">{{ $category->name }}</span>
                                        <span class="flex shrink-0 items-center gap-2">
                                            @if ($category->products_count)
                                                <span class="font-mono text-[11px] text-slate group-hover:text-cyan">{{ $category->products_count }}</span>
                                            @endif
                                            <svg class="h-3.5 w-3.5 text-link transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                                            </svg>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-10 rounded-2xl border border-gray-100 bg-white py-16 text-center">
                <p class="text-lg font-semibold text-navy">No product lines in this vertical yet.</p>
                <p class="mt-2 text-sm text-slate">Check back soon, or tell us what you need.</p>
                <a href="{{ route('contact') }}" class="btn-primary mt-5 inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Contact us</a>
            </div>
        @endif
    </div>
  </div>
</x-layouts.app>
