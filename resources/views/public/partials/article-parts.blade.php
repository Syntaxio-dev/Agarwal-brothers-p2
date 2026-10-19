{{-- The article body as ordered parts (text, picture, photo slider / grid, sources). Used by blogs, news and webinars. --}}
@php
    $parts = $insight->articleParts();
    $imgSize = ['wide' => 'max-w-full', 'medium' => 'max-w-2xl', 'small' => 'max-w-md'];
@endphp

@foreach ($parts as $part)
    @php
        $type = $part['type'] ?? '';
        $d = $part['data'] ?? [];
    @endphp

    @if ($type === 'text' && filled($d['content'] ?? null))
        <div data-reveal class="rich-text mt-8 first:mt-10">{!! \Illuminate\Support\Str::sanitizeHtml((string) $d['content']) !!}</div>

    @elseif ($type === 'image' && filled($d['image'] ?? null))
        <figure data-reveal class="mx-auto mt-8 {{ $imgSize[$d['size'] ?? 'wide'] ?? 'max-w-full' }}">
            <img src="{{ asset('storage/' . $d['image']) }}" alt="{{ $d['alt'] ?? '' }}"
                 class="img-load w-full rounded-2xl object-cover shadow-md ring-1 ring-black/5" loading="lazy" decoding="async"
                 {!! \App\Support\Img::attrs($d['image']) !!} onload="this.classList.add('is-loaded')">
            @if (filled($d['caption'] ?? null))
                <figcaption class="mt-2 text-center text-sm text-slate">{{ $d['caption'] }}</figcaption>
            @endif
        </figure>

    @elseif ($type === 'gallery' && count(array_filter((array) ($d['images'] ?? []))) > 0)
        @php
            $images = array_values(array_filter((array) $d['images']));
            $count = count($images);
            $isCarousel = ($d['style'] ?? 'carousel') === 'carousel' && $count > 1;
        @endphp

        @if ($isCarousel)
            <figure data-reveal class="mt-8" role="group" aria-roledescription="carousel" aria-label="Photo slider"
                    x-data="{ i: 0, n: {{ $count }}, x0: null,
                              go(k) { this.i = (k + this.n) % this.n },
                              swipe(x) { if (this.x0 === null) return; const dx = x - this.x0; this.x0 = null; if (Math.abs(dx) > 40) this.go(this.i + (dx < 0 ? 1 : -1)) } }"
                    @keydown.arrow-left.prevent="go(i - 1)" @keydown.arrow-right.prevent="go(i + 1)">
                <div class="relative overflow-hidden rounded-2xl bg-ice shadow-md ring-1 ring-black/5" tabindex="0"
                     style="touch-action: pan-y pinch-zoom; overscroll-behavior-x: contain;"
                     @touchstart.passive="x0 = $event.touches[0].clientX" @touchend.passive="swipe($event.changedTouches[0].clientX)">
                    <div class="grid">
                        @foreach ($images as $k => $img)
                            <img src="{{ asset('storage/' . $img) }}" alt="{{ filled($d['caption'] ?? null) ? $d['caption'] . ' (' . ($k + 1) . ' of ' . $count . ')' : 'Photo ' . ($k + 1) . ' of ' . $count }}"
                                 class="aspect-[16/9] w-full object-contain transition-opacity duration-500 [grid-area:1/1]"
                                 :class="i === {{ $k }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                                 :aria-hidden="i !== {{ $k }}" loading="lazy" decoding="async">
                        @endforeach
                    </div>
                    <button type="button" @click="go(i - 1)" aria-label="Previous photo"
                            class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-navy shadow transition hover:bg-navy hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    </button>
                    <button type="button" @click="go(i + 1)" aria-label="Next photo"
                            class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/95 text-navy shadow transition hover:bg-navy hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                    </button>
                    <span class="absolute bottom-3 right-3 rounded-full bg-navy/80 px-2.5 py-1 font-mono text-xs text-white" aria-live="polite"><span x-text="i + 1"></span> / {{ $count }}</span>
                </div>
                <div class="mt-3 flex justify-center gap-2">
                    @foreach ($images as $k => $img)
                        <button type="button" @click="go({{ $k }})" aria-label="Show photo {{ $k + 1 }}"
                                class="h-2.5 rounded-full transition-all duration-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-cyan"
                                :class="i === {{ $k }} ? 'w-6 bg-link' : 'w-2.5 bg-gray-300 hover:bg-gray-400'"></button>
                    @endforeach
                </div>
                @if (filled($d['caption'] ?? null))
                    <figcaption class="mt-2 text-center text-sm text-slate">{{ $d['caption'] }}</figcaption>
                @endif
            </figure>
        @else
            <figure data-reveal class="mt-8">
                <div class="grid gap-3 {{ $count === 1 ? 'grid-cols-1' : 'grid-cols-2' }} {{ $count >= 3 ? 'md:grid-cols-3' : '' }}">
                    @foreach ($images as $img)
                        <img src="{{ asset('storage/' . $img) }}" alt="{{ $d['caption'] ?? '' }}"
                             class="img-load aspect-[4/3] w-full rounded-xl object-cover shadow-sm ring-1 ring-black/5" loading="lazy" decoding="async"
                             {!! \App\Support\Img::attrs($img) !!} onload="this.classList.add('is-loaded')">
                    @endforeach
                </div>
                @if (filled($d['caption'] ?? null))
                    <figcaption class="mt-2 text-center text-sm text-slate">{{ $d['caption'] }}</figcaption>
                @endif
            </figure>
        @endif

    @elseif ($type === 'sources' && count(array_filter((array) ($d['items'] ?? []), fn ($i) => filled($i['url'] ?? null))) > 0)
        <section data-reveal class="mt-10 rounded-2xl border border-gray-200 bg-ice p-5 sm:p-6" aria-labelledby="sources-heading">
            <h2 id="sources-heading" class="font-mono text-xs font-medium uppercase tracking-[0.2em] text-cyan-ink">Sources</h2>
            <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate marker:text-slate">
                @foreach ($d['items'] as $item)
                    @if (filled($item['url'] ?? null) && \App\Rules\SafeLink::passes($item['url']))
                        <li>
                            <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer nofollow" class="font-medium text-link underline-offset-2 hover:underline">{{ $item['label'] ?? $item['url'] }}</a>
                            @if (filled($item['accessed'] ?? null))<span class="text-slate"> (read {{ $item['accessed'] }})</span>@endif
                        </li>
                    @endif
                @endforeach
            </ol>
        </section>
    @endif
@endforeach
