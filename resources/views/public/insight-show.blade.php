@php
    use App\Support\Seo;

    $type = $insight->type;
    $labels = ['blog' => 'Blog', 'news' => 'News & Events', 'webinar' => 'Webinar'];
    $plural = ['blog' => 'Blogs', 'news' => 'News & Events', 'webinar' => 'Webinars'];
    $indexRoutes = ['blog' => 'insights.blogs', 'news' => 'insights.news', 'webinar' => 'insights.webinars'];

    $typeLabel = $labels[$type] ?? 'Insight';
    $indexUrl = route($indexRoutes[$type] ?? 'insights.blogs');

    $isWebinar = $type === 'webinar';
    $poster = $type === 'news' && $insight->image;
    $start = $isWebinar ? $insight->start_ist : null;
    $upcoming = $isWebinar && $insight->isUpcoming();

    $date = $isWebinar && $start ? $start : ($insight->event_date ?? $insight->created_at);
    $minutes = max(1, (int) ceil(str_word_count(strip_tags((string) $insight->content)) / 200));

    $url = Seo::canonical();
    $shareWa = 'https://wa.me/?text=' . rawurlencode($insight->title . ' ' . $url);
    $shareLi = 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode($url);

    $calendar = null;
    if ($isWebinar && $start) {
        if ($insight->time_tbd) {
            $dates = $start->format('Ymd') . '/' . $start->copy()->addDay()->format('Ymd');
        } else {
            $s = $start->copy()->utc();
            $dates = $s->format('Ymd\THis\Z') . '/' . $s->copy()->addHour()->format('Ymd\THis\Z');
        }
        $calendar = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode($insight->title)
            . '&dates=' . $dates
            . '&details=' . urlencode(strip_tags((string) $insight->excerpt))
            . '&location=' . urlencode($insight->venue ?: 'Online');
    }

    $org = ['@type' => 'Organization', 'name' => 'Agarwal Brothers', 'url' => Seo::absolute('/')];
    $schema = $isWebinar
        ? array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $insight->title,
            'description' => $insight->seoDescription(),
            'image' => array_filter([$insight->seoImage()]),
            'startDate' => $start ? ($insight->time_tbd ? $start->format('Y-m-d') : $start->toIso8601String()) : null,
            'eventAttendanceMode' => 'https://schema.org/OnlineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => ['@type' => 'VirtualLocation', 'url' => $insight->registration_url ?: $url],
            'organizer' => $org,
        ])
        : array_filter([
            '@context' => 'https://schema.org',
            '@type' => $type === 'news' ? 'NewsArticle' : 'BlogPosting',
            'headline' => \Illuminate\Support\Str::limit($insight->title, 110, ''),
            'description' => $insight->seoDescription(),
            'image' => array_filter([$insight->seoImage()]),
            'datePublished' => $insight->created_at?->toAtomString(),
            'dateModified' => $insight->updated_at?->toAtomString(),
            'author' => $org,
            'publisher' => $org + ['logo' => ['@type' => 'ImageObject', 'url' => Seo::absolute('sidebar-logo.png')]],
            'mainEntityOfPage' => $url,
        ]);

    $chip = 'inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-1.5 font-mono text-xs text-navy';
@endphp

<x-layouts.app :title="$insight->seoTitle()" :description="$insight->seoDescription()" :image="$insight->seoImage()"
               :og-type="$isWebinar ? 'website' : 'article'" :schema="$schema">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[480px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.14),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-16 py-10 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [
            ['Home', '/'],
            [$plural[$type] ?? 'Insights', $indexUrl],
            [\Illuminate\Support\Str::limit($insight->title, 48), null],
        ]])

        <div class="{{ $poster ? 'grid items-start gap-10 lg:grid-cols-5 lg:gap-14' : 'mx-auto max-w-3xl' }}">

            {{-- News poster (portrait) --}}
            @if ($poster)
                <aside class="lg:col-span-2 lg:sticky lg:top-6">
                    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-ice p-3 shadow-lg">
                        <img src="{{ asset('storage/' . $insight->image) }}" alt="{{ $insight->title }}" class="img-load w-full rounded-2xl object-contain" decoding="async" {!! \App\Support\Img::attrs($insight->image) !!} onload="this.classList.add('is-loaded')">
                    </div>
                </aside>
            @endif

            <article class="{{ $poster ? 'lg:col-span-3' : '' }}">

                {{-- Header --}}
                <span class="section-badge">{{ $typeLabel }}</span>
                <h1 class="mt-4 text-3xl font-bold leading-tight text-navy sm:text-4xl">{{ $insight->title }}</h1>

                @if ($insight->excerpt)
                    <p class="mt-4 text-lg leading-relaxed text-slate">{{ $insight->excerpt }}</p>
                @endif

                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="{{ $chip }}">
                        <svg class="h-3.5 w-3.5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        {{ $date->format('F j, Y') }}
                    </span>
                    @if ($type === 'blog' && filled($insight->content))
                        <span class="{{ $chip }}">{{ $minutes }} min read</span>
                    @endif
                    @if ($isWebinar && $insight->brand)
                        <span class="{{ $chip }}">{{ $insight->brand->name }}</span>
                    @endif
                </div>

                {{-- Cover (blog / webinar) --}}
                @if (! $poster && $insight->image)
                    <img src="{{ asset('storage/' . $insight->image) }}" alt="{{ $insight->title }}"
                         class="img-load mt-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-md ring-1 ring-black/5" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($insight->image) !!} onload="this.classList.add('is-loaded')">
                @endif

                {{-- Webinar details --}}
                @if ($isWebinar && $start)
                    <div class="mt-8 rounded-2xl border border-cyan/25 bg-gradient-to-br from-ice to-cyan/10 p-5 sm:p-6">
                        <p class="font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-link">
                            {{ $upcoming ? 'Upcoming webinar' : 'Webinar ended' }}
                        </p>

                        <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate">Date</dt>
                                <dd class="mt-1 text-sm font-semibold text-navy">{{ $start->format('l, F j, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate">Time</dt>
                                <dd class="mt-1 text-sm font-semibold text-navy">{{ $insight->time_tbd ? 'To be announced' : $start->format('h:i A') . ' IST' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate">Mode</dt>
                                <dd class="mt-1 text-sm font-semibold text-navy">{{ $insight->venue ?: 'Online' }}</dd>
                            </div>
                        </dl>

                        <div class="mt-5 flex flex-wrap gap-3">
                            @if ($upcoming && $insight->registration_url)
                                <a href="{{ $insight->registration_url }}" target="_blank" rel="noopener"
                                   class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">
                                    Register now
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
                                </a>
                            @elseif (! $upcoming && $insight->recording_url)
                                <a href="{{ $insight->recording_url }}" target="_blank" rel="noopener"
                                   class="btn-primary inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white">
                                    Watch recording
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/></svg>
                                </a>
                            @endif
                            @if ($upcoming && $calendar)
                                <a href="{{ $calendar }}" target="_blank" rel="noopener" class="btn-ghost">Add to calendar</a>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Attached PDF --}}
                @if ($insight->pdf)
                    @php $pdfUrl = asset('storage/' . $insight->pdf); @endphp
                    <div class="mt-8 rounded-2xl border border-cyan/25 bg-gradient-to-br from-ice to-cyan/10 p-5 sm:p-6">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white text-link shadow-sm">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            </span>
                            <div class="flex-1">
                                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-link">Attached document</p>
                                <p class="mt-1 text-sm text-slate">Read it below or download the PDF.</p>
                            </div>
                            <a href="{{ $pdfUrl }}" download target="_blank" rel="noopener"
                               class="btn-primary inline-flex items-center justify-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                Download PDF
                            </a>
                        </div>
                        <iframe src="{{ $pdfUrl }}" title="{{ $insight->title }} (PDF)"
                                class="mt-5 hidden h-[75vh] w-full rounded-xl border border-gray-200 bg-white sm:block"></iframe>
                    </div>
                @endif

                {{-- Body --}}
                @if (filled($insight->content))
                    <div class="rich-text mt-10">{!! \Illuminate\Support\Str::sanitizeHtml((string) $insight->content) !!}</div>
                @endif

                {{-- Share --}}
                <div class="mt-12 flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6"
                     x-data="{ copied: false, copy() { const u = @js($url); (navigator.clipboard ? navigator.clipboard.writeText(u) : Promise.reject()).then(() => { this.copied = true; setTimeout(() => this.copied = false, 1800) }).catch(() => window.prompt('Copy this link', u)) } }">
                    <span class="font-mono text-xs font-medium uppercase tracking-[0.2em] text-slate">Share</span>

                    <a href="{{ $shareWa }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp" title="WhatsApp"
                       class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-navy transition-colors hover:border-transparent hover:bg-navy hover:text-white">
                        <svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    </a>

                    <a href="{{ $shareLi }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn" title="LinkedIn"
                       class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-navy transition-colors hover:border-transparent hover:bg-navy hover:text-white">
                        <svg class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>

                    <button type="button" @click="copy()" aria-label="Copy link" title="Copy link"
                            class="flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 text-navy transition-colors hover:border-transparent hover:bg-navy hover:text-white">
                        <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                        <span class="font-mono text-xs" x-text="copied ? 'Copied' : 'Copy link'"></span>
                    </button>
                </div>
            </article>
        </div>

        {{-- Related --}}
        @if ($related->count())
            <section class="mt-16">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold text-navy sm:text-2xl">More {{ strtolower($plural[$type] ?? 'insights') }}</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-cyan/50 to-transparent"></div>
                    <a href="{{ $indexUrl }}" class="shrink-0 text-sm font-semibold text-link hover:text-navy">View all &rarr;</a>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $r)
                        @php $rd = $r->type === 'webinar' && $r->start_ist ? $r->start_ist : ($r->event_date ?? $r->created_at); @endphp
                        <a href="{{ route('insights.show', $r->slug) }}"
                           class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan/50 hover:shadow-xl">
                            <div class="flex aspect-[16/9] items-center justify-center overflow-hidden bg-gradient-to-br from-ice to-cyan/10">
                                @if ($r->image)
                                    <img src="{{ asset('storage/' . $r->image) }}" alt="{{ $r->title }}"
                                         class="img-load h-full w-full {{ $r->type === 'news' ? 'object-contain' : 'object-cover' }} transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($r->image) !!} onload="this.classList.add('is-loaded')">
                                @else
                                    <span class="line-clamp-3 px-6 text-center text-sm font-bold text-navy/50">{{ $r->title }}</span>
                                @endif
                            </div>
                            <div class="flex flex-1 flex-col p-5">
                                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-link">{{ $rd->format('d M Y') }}</p>
                                <h3 class="mt-2 line-clamp-2 text-base font-bold leading-snug text-navy transition-colors group-hover:text-link">{{ $r->title }}</h3>
                                <span class="mt-auto pt-4 text-xs font-semibold text-link">Read more &rarr;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- CTA --}}
        <div class="mt-16 flex flex-col items-center justify-between gap-5 rounded-3xl bg-gradient-to-br from-navy to-link px-8 py-9 sm:flex-row sm:px-12">
            <div class="text-center sm:text-left">
                <h3 class="text-xl font-bold text-white sm:text-2xl">Have a question about this topic?</h3>
                <p class="mt-1.5 text-sm text-white/75">Our specialists are happy to help you choose the right instrument for your lab.</p>
            </div>
            <a href="{{ route('contact') }}" class="btn-glass shrink-0 px-7 py-3">Talk to an expert</a>
        </div>
    </div>
  </div>
</x-layouts.app>
