@php
    $category = $product->category;
    $brand = $category->brand;
    $q = $vertical ? ['v' => $vertical->slug] : [];

    $crumbs = [['Home', '/']];
    if ($vertical) {
        $crumbs[] = ['Verticals', route('verticals.index')];
        $crumbs[] = [$vertical->name, route('vertical.show', $vertical->slug)];
    } else {
        $crumbs[] = [$brand->name, route('brand.show', $brand->slug)];
    }
    $crumbs[] = [$category->name, route('category.show', [$brand->slug, $category->slug] + $q)];
    $crumbs[] = [$product->name, null];

    $images = collect([$product->image])->merge($product->gallery ?? [])->filter()->map(fn ($p) => asset('storage/' . $p))->values();

    $features = collect($product->features ?? [])->filter(fn ($f) => filled($f['title'] ?? null));
    $advantages = collect($product->advantages ?? [])->filter(fn ($f) => filled($f['title'] ?? null));
    $specs = collect($product->specs ?? []);
    $docs = collect($product->documents ?? [])->filter(fn ($d) => filled($d['title'] ?? null) && (filled($d['file'] ?? null) || \App\Rules\SafeLink::passes($d['url'] ?? null)));

    $videoId = null;
    if ($product->video_url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~', $product->video_url, $m)) {
        $videoId = $m[1];
    }

    $overview = filled($product->overview) ? $product->overview : null;

    $input = 'w-full h-11 rounded-xl border border-gray-200 bg-white px-4 text-sm text-navy placeholder:text-slate outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20';
    $check = 'M4.5 12.75l6 6 9-13.5';
@endphp

@php
    $schemaImages = collect([$product->image])->merge($product->gallery ?? [])->filter()
        ->map(fn ($p) => \App\Support\Seo::storage($p))->values()->all();

    $productSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => $product->seoDescription(),
        'image' => $schemaImages,
        'category' => $product->category->name,
        'brand' => ['@type' => 'Brand', 'name' => $product->category->brand->name],
        'url' => \App\Support\Seo::canonical(),
    ]);
@endphp
<x-layouts.app :title="$product->seoTitle()" :description="$product->seoDescription()" :image="$product->seoImage()"
               og-type="product" :schema="$productSchema">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[460px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.14),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-10 sm:py-14"
         x-data="{
             lightbox: false, i: 0, imgs: @js($images), sx: 0,
             go(n) {
                 if (this.imgs.length < 2) return;
                 this.i = (n + this.imgs.length) % this.imgs.length;
                 [1, -1].forEach(d => { const near = new Image(); near.src = this.imgs[(this.i + d + this.imgs.length) % this.imgs.length]; });   // the neighbours are ready before they are needed
             },
             prev() { this.go(this.i - 1); },
             next() { this.go(this.i + 1); },
             swipe(x) { const d = x - this.sx; if (Math.abs(d) > 40) { d < 0 ? this.next() : this.prev(); } },
         }"
         @keydown.escape.window="lightbox = false"
         @keydown.arrow-left.window="lightbox && prev()" @keydown.arrow-right.window="lightbox && next()">

        {{-- Remember this product for the "Recently viewed" card --}}
        <span class="hidden" x-init="$store.recent.add(@js([
            'slug' => $product->slug,
            'name' => $product->name,
            'image' => $product->image ? asset('storage/' . $product->image) : '',
            'brand' => $brand->name,
        ]))"></span>

        @include('public.partials.breadcrumb', ['items' => $crumbs, 'back' => [
            'label' => 'Back to ' . $category->name,
            'url' => route('category.show', [$brand->slug, $category->slug] + $q),
        ]])

        {{-- ===== Hero banner ===== --}}
        <div class="grid grid-cols-1 items-center gap-8 overflow-hidden rounded-3xl border border-cyan/20 bg-gradient-to-br from-ice via-white to-cyan/10 p-6 shadow-sm sm:p-10 lg:grid-cols-5 lg:gap-12">
            {{-- Photos: the big photo with previous / next arrows and a counter, thumbnails below; click the photo to enlarge --}}
            <div data-reveal="left" class="lg:col-span-2">
                <div class="relative flex h-64 items-center justify-center overflow-hidden rounded-2xl bg-white/80 shadow-inner sm:h-80"
                     @if ($images->count() > 1)
                         style="touch-action: pan-y pinch-zoom; overscroll-behavior-x: contain;"
                         tabindex="0" role="group" aria-roledescription="carousel" aria-label="{{ $product->name }} photos"
                         @keydown.arrow-left.prevent="! lightbox && prev()" @keydown.arrow-right.prevent="! lightbox && next()"
                         @touchstart.passive="sx = $event.changedTouches[0].clientX" @touchend.passive="swipe($event.changedTouches[0].clientX)"
                     @endif>
                    @if ($images->count())
                        {{-- every photo sits side by side in a track; changing photo slides the track sideways --}}
                        <div class="photo-track flex h-full w-full" :style="{ transform: 'translateX(' + (-i * 100) + '%)' }">
                            @foreach ($images as $idx => $url)
                                <div class="flex h-full w-full shrink-0 items-center justify-center p-3" @if ($idx > 0) :aria-hidden="i !== {{ $idx }}" @endif>
                                    <img src="{{ $url }}" alt="{{ $product->name }} - photo {{ $idx + 1 }}"
                                         @click="lightbox = true" @if ($idx > 1) loading="lazy" @endif
                                         class="img-load max-h-full max-w-full cursor-zoom-in select-none mix-blend-multiply object-contain drop-shadow-xl" decoding="async" draggable="false" onload="this.classList.add('is-loaded')">
                                </div>
                            @endforeach
                        </div>
                        @if ($images->count() > 1)
                            <button type="button" @click.stop="prev()" aria-label="Previous photo"
                                class="absolute left-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white text-navy shadow-md ring-1 ring-black/5 transition hover:bg-cyan focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan sm:h-10 sm:w-10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                        </button>
                            <button type="button" @click.stop="next()" aria-label="Next photo"
                                class="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-white text-navy shadow-md ring-1 ring-black/5 transition hover:bg-cyan focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan sm:h-10 sm:w-10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </button>
                            <p class="pointer-events-none absolute bottom-2 right-3 rounded-full bg-navy/80 px-2.5 py-1 font-mono text-[11px] tracking-wider text-white" aria-live="polite">
                                <span x-text="i + 1">1</span> / {{ $images->count() }}
                            </p>
                        @endif
                    @else
                        <svg class="h-20 w-20 text-slate/25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v14.25c0 .828.672 1.5 1.5 1.5Z"/>
                    </svg>
                    @endif
                </div>

                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Choose a photo">
                        @foreach ($images as $idx => $url)
                            <button type="button" @click="go({{ $idx }})" role="tab" aria-label="Show photo {{ $idx + 1 }}"
                                    :aria-selected="i === {{ $idx }}"
                                    :class="i === {{ $idx }} ? 'border-cyan ring-2 ring-cyan/30' : 'border-gray-200 opacity-80 hover:border-cyan/60 hover:opacity-100'"
                                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border-2 bg-white p-1 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan sm:h-16 sm:w-16">
                                <img src="{{ $url }}" alt="" class="max-h-full max-w-full object-contain" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div data-reveal="right" data-reveal-delay="100" class="lg:col-span-3">
                <div class="flex items-center justify-between gap-4">
                    <p class="font-mono text-xs font-medium uppercase tracking-[0.2em] text-link">{{ $category->name }}</p>
                    <a href="{{ route('brand.show', $brand->slug) }}" class="flex h-11 w-32 shrink-0 items-center justify-center rounded-lg border border-gray-100 bg-white p-2" title="{{ $brand->name }}">
                        @if ($brand->logo)
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="img-load max-h-full max-w-full object-contain" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
                        @else
                            <span class="text-sm font-bold text-navy">{{ $brand->name }}</span>
                        @endif
                    </a>
                </div>

                <h1 class="mt-3 text-2xl font-bold leading-tight text-navy sm:text-3xl lg:text-4xl">{{ $product->heading ?: $product->name }}</h1>

                @if ($product->short_description)
                    <p class="mt-4 text-base leading-relaxed text-slate">{{ $product->short_description }}</p>
                @endif

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="#enquiry-form" class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.4 5.25a1.5 1.5 0 0 1-1.6 0L2.25 6.75"/></svg>
                        Enquiry
                    </a>
                    @include('public.partials.compare-toggle', ['product' => $product, 'large' => true])
                    @include('public.partials.enquiry-list-toggle', ['product' => $product, 'large' => true])
                    @if ($images->count())
                        <button type="button" @click="lightbox = true" class="btn-ghost">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5a1.5 1.5 0 0 0 1.5-1.5V5.25a1.5 1.5 0 0 0-1.5-1.5H3.75a1.5 1.5 0 0 0-1.5 1.5v14.25c0 .828.672 1.5 1.5 1.5Z"/></svg>
                            Show Image{{ $images->count() > 1 ? 's' : '' }}
                        </button>
                    @endif
                    @if ($specs->count() || $features->count() || $advantages->count() || $product->short_description)
                        <a href="{{ route('product.spec-sheet', $product->slug) }}?print=1" target="_blank" rel="noopener" class="btn-ghost">
                            <svg class="arrow-nudge-down h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Spec sheet
                        </a>
                    @endif
                    <a href="{{ route('contact') }}#contact-form" class="btn-ghost">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                        Meet Expert
                    </a>
                </div>
            </div>
        </div>

        {{-- ===== Technical specifications ===== --}}
        @if ($specs->count())
            <section data-reveal class="mt-14 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="text-center">
                    <span class="section-badge">Specifications</span>
                    <h2 class="mt-3 text-xl font-bold text-navy sm:text-2xl">Technical specifications</h2>
                </div>
                <dl class="mt-8 grid grid-cols-1 gap-x-8 md:grid-cols-2">
                    @foreach ($specs as $key => $value)
                        <div class="flex items-start justify-between gap-4 border-b border-gray-100 py-3">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate">{{ $key }}</dt>
                            <dd class="spec-value text-right text-sm text-navy">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        {{-- ===== Key features & advantages (side by side from large screens) ===== --}}
        @if ($features->count() || $advantages->count())
            <section data-reveal class="mt-8 grid grid-cols-1 gap-8 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-10 {{ $features->count() && $advantages->count() ? 'lg:grid-cols-2 lg:gap-12' : '' }}">
                @if ($features->count())
                    <div>
                        <div class="text-center lg:text-left">
                            <span class="section-badge">Features</span>
                            <h2 class="mt-3 text-xl font-bold text-navy sm:text-2xl">Key features of <span class="text-cyan-ink">{{ $product->name }}</span></h2>
                        </div>
                        <div class="mt-8 grid grid-cols-1 gap-4">
                            @foreach ($features as $f)
                                <div data-reveal class="flex gap-4 rounded-xl border border-gray-100 bg-ice p-4 transition-colors hover:border-cyan/50">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-navy to-link text-white">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $check }}"/></svg>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-semibold text-navy">{{ $f['title'] }}</h3>
                                        @if (filled($f['text'] ?? null))
                                            <p class="mt-1 text-sm leading-relaxed text-slate">{{ $f['text'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($advantages->count())
                    <div>
                        <div class="text-center lg:text-left">
                            <span class="section-badge">Advantages</span>
                            <h2 class="mt-3 text-xl font-bold text-navy sm:text-2xl">Key advantages of <span class="text-cyan-ink">{{ $product->name }}</span></h2>
                        </div>
                        <div class="mt-8 grid grid-cols-1 gap-4">
                            @foreach ($advantages as $f)
                                <div data-reveal class="flex gap-4 rounded-xl border border-gray-100 bg-ice p-4 transition-colors hover:border-cyan/50">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-link to-cyan text-white">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 14.25 2.25 12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/></svg>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-semibold text-navy">{{ $f['title'] }}</h3>
                                        @if (filled($f['text'] ?? null))
                                            <p class="mt-1 text-sm leading-relaxed text-slate">{{ $f['text'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- ===== Overview (full-width card, text in two columns on large screens so the sides are not empty) ===== --}}
        @if ($overview || $product->short_description)
            <section data-reveal class="mt-8 rounded-3xl border border-gray-200 bg-ice p-6 sm:p-10">
                <div class="text-center">
                    <span class="section-badge">Overview</span>
                    <h2 class="mt-3 text-xl font-bold text-navy sm:text-2xl">About <span class="text-cyan-ink">{{ $product->name }}</span></h2>
                </div>
                @if ($overview)
                    <div class="mt-8 text-left leading-relaxed text-slate lg:columns-2 lg:gap-12
                                [&_p]:mb-4 [&_ul]:mb-4 [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:mb-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_strong]:text-navy [&_a]:text-link [&_a]:underline [&_li]:break-inside-avoid">
                        {!! \Illuminate\Support\Str::sanitizeHtml((string) $overview) !!}
                    </div>
                @else
                    <p class="mt-8 leading-relaxed text-slate">{{ $product->short_description }}</p>
                @endif
            </section>
        @endif

        {{-- ===== Documents & research papers ===== --}}
        @if ($docs->count())
            <section data-reveal class="mt-8 rounded-3xl border border-gray-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="text-center">
                    <span class="section-badge">Resources</span>
                    <h2 class="mt-3 text-xl font-bold text-navy sm:text-2xl">Documents &amp; research papers</h2>
                </div>
                <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($docs as $d)
                        @php $href = filled($d['file'] ?? null) ? asset('storage/' . $d['file']) : $d['url']; @endphp
                        <a data-reveal href="{{ $href }}" target="_blank" rel="noopener"
                           class="group flex items-center gap-4 rounded-xl border border-gray-200 bg-ice p-4 transition-all hover:border-cyan/60 hover:shadow-md">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-white text-link shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-mono text-[11px] uppercase tracking-widest text-link">{{ $d['type'] ?? 'Document' }}</span>
                                <span class="block truncate text-sm font-semibold text-navy group-hover:text-link">{{ $d['title'] }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-slate transition-transform group-hover:translate-y-0.5 group-hover:text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== Video ===== --}}
        @if ($videoId)
            <section class="mt-10">
                <div data-reveal="zoom" class="mx-auto max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-navy shadow-lg">
                    <div class="aspect-video">
                        <iframe class="h-full w-full" src="https://www.youtube-nocookie.com/embed/{{ $videoId }}" title="{{ $product->name }} video"
                                loading="lazy" allowfullscreen
                                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                </div>
            </section>
        @endif

        @include('public.partials.faq', ['faqs' => $product->faqs, 'title' => 'Frequently asked questions: ' . $product->name])

        {{-- ===== Enquiry form (wide rectangle) ===== --}}
        <section id="enquiry-form" class="mt-16 scroll-mt-6">
            <div data-reveal class="rounded-3xl border border-cyan/20 bg-gradient-to-br from-ice to-cyan/10 p-3 sm:p-4">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-10">
                    <div class="text-center">
                        <span class="section-badge">Enquiry</span>
                        <h2 class="mt-3 text-xl font-bold text-navy sm:text-2xl">Enquire about <span class="text-cyan-ink">{{ $product->name }}</span></h2>
                        <p class="mt-1 text-sm text-slate">Share your requirement and our team will get back to you.</p>
                    </div>

                    @if (session('success'))
                        <div class="mt-5"><x-form.alert>{{ session('success') }}</x-form.alert></div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-5"><x-form.alert type="error">Please fix the highlighted fields and try again.</x-form.alert></div>
                    @endif

                    <form method="POST" action="{{ route('enquiry.store') }}" class="mt-8 space-y-5" x-data="formGuard(@js(collect($errors->messages())->map(fn ($m) => $m[0])->all()))" @submit="submit($event)" novalidate>
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="hidden" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-6">
                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-sm font-semibold text-navy">Name <span class="text-alert">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required data-rule="required name" data-clean="name" data-label="Name" maxlength="80" autocomplete="name" @input="clean($el)" @blur="check($el)" class="{{ $input }}">
                                <x-form.error name="name" />
                            </div>
                            <div class="lg:col-span-2">
                                <label class="mb-1.5 block text-sm font-semibold text-navy">Email <span class="text-alert">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required data-rule="required email" data-label="Email" maxlength="255" autocomplete="email" @input="clean($el)" @blur="check($el)" class="{{ $input }}">
                                <x-form.error name="email" />
                            </div>
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label class="mb-1.5 block text-sm font-semibold text-navy">Phone</label>
                                <x-form.phone :input="$input" rounded="rounded-xl" />
                            </div>
                            <div class="lg:col-span-3">
                                <label class="mb-1.5 block text-sm font-semibold text-navy">Application Budget</label>
                                <input type="text" name="budget" value="{{ old('budget') }}" class="{{ $input }}">
                            </div>
                            <div class="lg:col-span-3">
                                <label class="mb-1.5 block text-sm font-semibold text-navy">Where is the order coming from?</label>
                                <input type="text" name="order_location" value="{{ old('order_location') }}" class="{{ $input }}">
                            </div>
                        </div>

                        @if ($questions->isNotEmpty())
                            {{-- Quick questions from Admin > Enquiry questions (by vertical); all are required --}}
                            <div class="rounded-2xl border border-cyan/20 bg-ice p-5 sm:p-6">
                                <p class="text-sm font-semibold text-navy">A few quick questions <span class="font-normal text-slate">(they help us suggest the right model)</span></p>
                                <div class="mt-4 grid grid-cols-1 gap-5 lg:grid-cols-2">
                                    @foreach ($questions as $q)
                                        @if ($q->type === 'choice')
                                            <fieldset class="{{ count($q->optionList()) > 3 ? 'lg:col-span-2' : '' }}">
                                                <legend class="mb-2 text-sm font-medium text-navy">{{ $q->question }} <span class="text-alert">*</span></legend>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach ($q->optionList() as $opt)
                                                        <label class="cursor-pointer">
                                                            <input type="radio" name="answers[{{ $q->id }}]" value="{{ $opt }}" class="peer sr-only" @if ($loop->first) data-rule="required" data-label="Please choose an answer" @endif @change="check($el.closest('fieldset').querySelector('[data-rule]'))" @checked(old('answers.' . $q->id) === $opt)>
                                                            <span class="inline-block rounded-full border border-gray-200 bg-white px-4 py-2 text-sm text-navy transition-colors hover:border-cyan peer-checked:border-navy peer-checked:bg-navy peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-cyan">{{ $opt }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <x-form.error name="answers.{{ $q->id }}" />
                                            </fieldset>
                                        @else
                                            <div>
                                                <label class="mb-1.5 block text-sm font-medium text-navy">{{ $q->question }} <span class="text-alert">*</span></label>
                                                <input type="text" name="answers[{{ $q->id }}]" value="{{ old('answers.' . $q->id) }}" maxlength="300" required data-rule="required" data-label="Please answer this" @input="clean($el)" @blur="check($el)" class="{{ $input }}">
                                                <x-form.error name="answers.{{ $q->id }}" />
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-navy">Message</label>
                            <textarea name="message" rows="4"
                                class="w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20">{{ old('message') }}</textarea>
                        </div>

                        <div class="text-center">
                            <x-form.submit label="Submit Enquiry" />
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- ===== Similar products (may be other brands) ===== --}}
        @if ($similar->count())
            <section class="mt-16">
                <div data-reveal class="flex flex-col gap-1">
                    <div class="flex items-center gap-4">
                        <h2 class="text-xl font-bold text-navy sm:text-2xl">Similar products</h2>
                        <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    </div>
                    <p class="text-sm text-slate">Alternatives with a similar purpose and specifications, including other brands.</p>
                </div>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($similar as $row)
                        @include('public.partials.product-card', ['product' => $row['product'], 'reason' => $row['reason']])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== Customers also compared (built from real comparisons; hidden until there are some) ===== --}}
        @if ($alsoCompared->count())
            <section class="mt-14">
                <div data-reveal class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-navy sm:text-2xl">Customers also compared</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                </div>
                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($alsoCompared as $other)
                        @include('public.partials.product-card', ['product' => $other])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== Image lightbox ===== --}}
        <div x-show="lightbox" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-navy/90 p-4" @click.self="lightbox = false">
            <button type="button" @click="lightbox = false" aria-label="Close" class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-white text-navy shadow-lg hover:bg-cyan">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
            <template x-if="imgs.length > 1">
                <button type="button" @click="prev()" aria-label="Previous" class="absolute left-4 flex h-11 w-11 items-center justify-center rounded-full bg-white text-navy shadow-lg hover:bg-cyan">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                </button>
            </template>
            <div class="h-[85vh] w-full max-w-5xl overflow-hidden"
                 @touchstart.passive="sx = $event.changedTouches[0].clientX" @touchend.passive="swipe($event.changedTouches[0].clientX)">
                <div class="photo-track flex h-full w-full" :style="{ transform: 'translateX(' + (-i * 100) + '%)' }">
                    <template x-for="(src, idx) in imgs" :key="idx">
                        <div class="flex h-full w-full shrink-0 items-center justify-center">
                            <img :src="lightbox ? src : ''" :alt="@js($product->name) + ' image ' + (idx + 1)" :aria-hidden="idx !== i" draggable="false"
                                 class="max-h-[85vh] max-w-full select-none rounded-xl bg-white object-contain p-4 shadow-2xl">
                        </div>
                    </template>
                </div>
            </div>
            <p x-show="imgs.length > 1" class="absolute bottom-5 left-1/2 -translate-x-1/2 rounded-full bg-white/90 px-3 py-1 font-mono text-xs tracking-wider text-navy"><span x-text="i + 1"></span> / <span x-text="imgs.length"></span></p>
            <template x-if="imgs.length > 1">
                <button type="button" @click="next()" aria-label="Next" class="absolute right-4 flex h-11 w-11 items-center justify-center rounded-full bg-white text-navy shadow-lg hover:bg-cyan">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                </button>
            </template>
        </div>
    </div>
  </div>
</x-layouts.app>
