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

    // The toolbar (search + sort) is always there now, so the list can always be filtered
    $filterable = true;
    $filterData = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'group' => $p->model_group ?: 'Models',
        'text' => mb_strtolower($p->name . ' ' . $p->short_description),
    ])->values();

    $brochureUrl = filled($brand->brochure) ? asset('storage/' . $brand->brochure) : null;
    $phone = config('contact.call');
    $hours = config('contact.working_days.0');
    $hasIntro = filled($category->heading) || filled($category->description);
@endphp

<x-layouts.app :title="$category->seoTitle()" :description="$category->seoDescription()" :image="$category->seoImage()">
  <div class="relative overflow-x-clip">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[480px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.14),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-8 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => $crumbs])

        {{-- Title banner: category (with its full intro, always shown) on the left, brand on the right --}}
        <div data-reveal class="flex flex-col items-start justify-between gap-5 rounded-2xl border border-gray-200 bg-gradient-to-br from-white to-ice px-9 py-7 shadow-sm sm:flex-row sm:items-center max-sm:gap-3 max-sm:px-5 max-sm:py-5">
            <div class="min-w-0 max-w-3xl">
                <p class="font-mono text-xs font-medium uppercase tracking-[0.2em] text-link">{{ $brand->name }}</p>
                <h1 class="mt-2 text-[1.65rem] sm:text-4xl font-bold text-navy leading-tight">{{ $category->name }}</h1>
                @if ($hasIntro)
                    @if ($category->heading)
                        <h2 class="mt-3 text-[17px] font-semibold leading-snug text-link">{{ $category->heading }}</h2>
                    @endif
                    @if ($category->description)
                        <p class="mt-3 text-[14.5px] leading-[1.65] text-navy/80">{{ $category->description }}</p>
                    @endif
                @endif
            </div>
            <a href="{{ route('brand.show', $brand->slug) }}" class="flex shrink-0 items-center justify-center rounded-xl border border-gray-100 bg-white" style="width: clamp(9rem, 9vw + 6.5rem, 16rem); aspect-ratio: 5 / 2; padding: clamp(0.5rem, 1.2vw, 1rem);" title="{{ $brand->name }}">
                @if ($brand->logo)
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-load max-h-full max-w-full object-contain" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
                @else
                    <span class="text-base font-bold text-navy">{{ $brand->name }}</span>
                @endif
            </a>
        </div>

        {{-- Rail (left from lg, after the models on phones) + models --}}
        <div class="mt-8 flex flex-col gap-7 lg:grid lg:grid-cols-[252px_minmax(0,1fr)] lg:items-start" @if ($products->count()) x-data="catalogueFilter(@js($filterData))" @endif>

            <aside class="flex flex-col gap-4 max-lg:order-last lg:sticky lg:top-[calc(var(--nav-h,4.5rem)+1rem)]" aria-label="More about {{ $brand->name }}">

                {{-- Model group (desktop; phones use the chip row above the models) --}}
                @if ($products->count() && $groups->count() > 1)
                    <div data-reveal class="rounded-2xl border border-gray-200 bg-white p-[18px] shadow-sm max-lg:hidden">
                        <p class="mb-3 font-mono text-[10.5px] font-medium uppercase tracking-[0.2em] text-slate">Model group</p>
                        <div class="flex flex-col gap-1" role="group" aria-label="Model group">
                            <button type="button" @click="group = 'all'" :aria-pressed="group === 'all'"
                                    class="flex items-center justify-between rounded-[10px] px-3 py-2.5 text-left text-sm font-medium transition-colors"
                                    :class="group === 'all' ? 'bg-navy text-white' : 'text-navy hover:bg-ice'">
                                <span>All models</span>
                                <span class="font-mono text-[11px]" :class="group === 'all' ? 'text-cyan' : 'text-slate'">{{ $products->count() }}</span>
                            </button>
                            @foreach ($groups as $groupName => $items)
                                <button type="button" @click="group = @js($groupName)" :aria-pressed="group === @js($groupName)"
                                        class="flex items-center justify-between gap-2 rounded-[10px] px-3 py-2.5 text-left text-sm font-medium transition-colors"
                                        :class="group === @js($groupName) ? 'bg-navy text-white' : 'text-navy hover:bg-ice'">
                                    <span class="min-w-0 truncate">{{ $groupName }}</span>
                                    <span class="font-mono text-[11px]" :class="group === @js($groupName) ? 'text-cyan' : 'text-slate'">{{ $items->count() }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- More from this brand --}}
                @if ($siblings->count())
                    <div data-reveal class="rounded-2xl border border-gray-200 bg-white p-[18px] shadow-sm">
                        <p class="mb-2 font-mono text-[10.5px] font-medium uppercase tracking-[0.2em] text-slate">More from {{ $brand->name }}</p>
                        {{-- phones: sideways chips --}}
                        <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] lg:hidden [&::-webkit-scrollbar]:hidden">
                            @foreach ($siblings as $other)
                                <a href="{{ route('category.show', [$brand->slug, $other->slug] + $q) }}" class="chip shrink-0">{{ $other->name }}</a>
                            @endforeach
                        </div>
                        {{-- desktop: rows with hairlines --}}
                        <ul class="divide-y divide-gray-100 max-lg:hidden">
                            @foreach ($siblings as $other)
                                <li>
                                    <a href="{{ route('category.show', [$brand->slug, $other->slug] + $q) }}" class="group/row flex items-center justify-between gap-2 py-2.5 text-[13.5px] font-medium text-navy transition-colors hover:text-link">
                                        <span class="min-w-0">{{ $other->name }}</span>
                                        <svg class="h-3.5 w-3.5 shrink-0 text-cyan transition-transform duration-200 group-hover/row:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Brand brochure (only when the brand has a PDF) --}}
                @if ($brochureUrl)
                    <a data-reveal href="{{ $brochureUrl }}" target="_blank" rel="noopener noreferrer"
                       class="group block rounded-2xl border border-gray-200 bg-white p-[18px] shadow-sm transition-all duration-300 hover:-translate-y-[3px] hover:border-cyan/50 hover:shadow-xl">
                        <div class="flex items-center gap-3">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-ice text-link">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            </span>
                            <span class="font-mono text-[10.5px] font-medium uppercase tracking-[0.16em] text-slate">Brochure &middot; PDF</span>
                        </div>
                        <p class="mt-3 text-[15px] font-semibold leading-snug text-navy">Read the {{ $brand->name }} brochure to find your best fit</p>
                        <p class="mt-1.5 text-[13px] leading-relaxed text-slate">Can't see the model you need here? Browse the full range in the brand brochure.</p>
                        <span class="mt-3 inline-flex items-center gap-1 text-[13px] font-semibold text-link">Open brochure <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">&rarr;</span></span>
                    </a>
                @endif

                {{-- Help card --}}
                <div data-reveal class="relative overflow-hidden rounded-2xl bg-navy p-5">
                    <div class="pointer-events-none absolute -right-8 -top-8 h-28 w-28 rounded-full bg-cyan/20 blur-2xl"></div>
                    <h3 class="relative text-[16px] font-bold leading-snug text-white">Not sure which model fits?</h3>
                    <p class="relative mt-1.5 text-[13px] leading-relaxed text-white/75">Our specialists can recommend one for your application.</p>
                    <a href="{{ route('contact') }}#contact-form" class="btn-glass relative mt-4 inline-flex px-4 py-2 text-[13px]">Talk to an expert</a>
                </div>
            </aside>

            {{-- Models --}}
            <div class="min-w-0">
                @if ($products->count())
                    {{-- Toolbar --}}
                    <div data-reveal class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-sm">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <label class="relative block flex-1">
                                <span class="sr-only">Search these models</span>
                                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                                <input type="search" x-model.debounce.150ms="q" placeholder="Search these models" maxlength="80" autocomplete="off"
                                       class="h-11 w-full rounded-xl border border-gray-200 bg-white pl-12 pr-4 text-sm text-navy placeholder:text-slate outline-none transition">
                            </label>
                            <label class="flex items-center gap-2">
                                <span class="font-mono text-[11px] font-medium uppercase tracking-[0.18em] text-slate">Sort</span>
                                <select x-model="sort" class="h-11 min-w-[11rem] max-sm:min-w-0 max-sm:flex-1 rounded-xl border border-gray-200 bg-white px-4 text-sm text-navy outline-none transition">
                                    <option value="default">Recommended</option>
                                    <option value="az">Name A to Z</option>
                                    <option value="za">Name Z to A</option>
                                    <option value="newest">Newest first</option>
                                </select>
                            </label>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-1 border-t border-gray-100 pt-3 text-xs text-slate" aria-live="polite">
                            <p class="flex items-center gap-3">
                                <span>Showing <span class="font-semibold text-navy" x-text="shown"></span> of {{ $products->count() }} models</span>
                                <button type="button" x-show="active" x-cloak @click="reset()" class="font-semibold text-link hover:text-navy">Clear filters</button>
                            </p>
                            <p class="flex items-center gap-3 font-mono text-[11px]">
                                <span><span class="font-semibold text-navy" x-text="$store.enquiryList.items.length">0</span> in enquiry list</span>
                                <span><span class="font-semibold text-navy" x-text="$store.compare.items.length">0</span> to compare</span>
                            </p>
                        </div>
                    </div>

                    {{-- Model groups as a sideways chip row (phones and tablets) --}}
                    @if ($groups->count() > 1)
                        <div class="mt-4 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] lg:hidden [&::-webkit-scrollbar]:hidden max-sm:-mx-4 max-sm:px-4" role="group" aria-label="Model group">
                            <button type="button" class="chip shrink-0" :class="{ 'chip-active': group === 'all' }" @click="group = 'all'" :aria-pressed="group === 'all'">All models</button>
                            @foreach ($groups as $groupName => $items)
                                <button type="button" class="chip shrink-0" :class="{ 'chip-active': group === @js($groupName) }" @click="group = @js($groupName)"
                                        :aria-pressed="group === @js($groupName)">{{ $groupName }} <span class="opacity-70">({{ $items->count() }})</span></button>
                            @endforeach
                        </div>
                    @endif

                    <div x-cloak x-show="shown === 0" class="mt-6 rounded-2xl border border-gray-200 bg-white px-6 py-12 text-center shadow-sm">
                        <p class="text-base font-semibold text-navy">No models match your filters.</p>
                        <button type="button" @click="reset()" class="btn-ghost mt-4">Clear filters</button>
                    </div>

                    @foreach ($groups as $groupName => $items)
                        <div class="mt-7" x-show="groupShown(@js($groupName)) > 0">
                            <div data-reveal class="mb-4 flex items-center gap-3">
                                <h3 class="font-mono text-[12.5px] font-medium uppercase tracking-[0.2em] text-link">{{ $groupName }}</h3>
                                <span class="font-mono text-[11px] text-slate">{{ $items->count() }}</span>
                                <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 max-sm:grid-cols-2 max-sm:gap-3">
                                @foreach ($items as $product)
                                    @include('public.partials.product-card', ['product' => $product, 'q' => $q, 'filterable' => true, 'showCategory' => false])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Empty state --}}
                    <div data-reveal class="relative rounded-[20px] border border-gray-200 bg-white p-10 text-center shadow-sm max-sm:p-6">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-ice text-link">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                        </span>
                        <h2 class="mt-5 text-[22px] font-bold text-navy">Models for this range are coming soon.</h2>
                        <p class="mx-auto mt-2 max-w-md text-[14.5px] leading-relaxed text-slate">Tell us what you need and our team will reply with the right options.</p>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('contact') }}" class="btn-primary inline-flex items-center rounded-full px-6 py-2.5 text-sm font-semibold text-white">Ask our team</a>
                            @if ($phone)
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="btn-ghost inline-flex items-center gap-2">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                                    {{ $phone }}
                                </a>
                            @endif
                        </div>
                        @if ($hours)
                            <p class="mt-4 text-xs text-slate">{{ $hours }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Long-form content --}}
        @if (filled($category->content))
            <div data-reveal class="rich-text mx-auto mt-14 max-w-4xl">
                {!! \Illuminate\Support\Str::sanitizeHtml((string) $category->content) !!}
            </div>
        @endif

        @include('public.partials.faq', ['faqs' => $category->faqs, 'title' => 'Frequently asked questions about ' . $category->name])

        {{-- CTA --}}
        <div data-reveal="zoom" class="mt-16 flex flex-col items-center justify-between gap-5 rounded-3xl bg-gradient-to-br from-navy to-link px-8 py-9 sm:flex-row sm:px-12 max-sm:mt-12 max-sm:px-6 max-sm:py-7">
            <div class="text-center sm:text-left">
                <h3 class="text-xl font-bold text-white sm:text-2xl">Need help choosing the right {{ strtolower($category->name) }}?</h3>
                <p class="mt-1.5 text-sm text-white/75">Our specialists can recommend a model for your application.</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-glass shrink-0 px-7 py-3 max-sm:w-full max-sm:justify-center">Talk to an expert</a>
        </div>
    </div>
  </div>
</x-layouts.app>
