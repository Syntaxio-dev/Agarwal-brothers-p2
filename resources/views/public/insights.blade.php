@php
    $meta = [
        'blog' => [
            'banner_title' => 'Scientific Research',
            'banner_tags' => ['Insights', 'Applications', 'Innovation', 'Impact'],
            'heading' => 'Scientific Instrument',
            'accent' => 'Blogs & Insights',
            'ratio' => 'aspect-[16/9]',
            'fit' => 'object-cover',
            'empty' => 'No blogs match these filters.',
        ],
        'news' => [
            'banner_title' => 'News & Events',
            'banner_tags' => ['Exhibitions', 'Initiatives', 'Community', 'Highlights'],
            'heading' => 'Events, Initiatives &',
            'accent' => 'Community Highlights',
            'ratio' => 'aspect-[4/5]',
            'fit' => 'object-contain',
            'empty' => 'No events match these filters.',
        ],
        'webinar' => [
            'banner_title' => 'Webinars',
            'banner_tags' => ['Learn', 'Connect', 'Explore', 'Grow'],
            'heading' => 'Webinars &',
            'accent' => 'Recordings',
            'ratio' => 'aspect-[16/9]',
            'fit' => 'object-cover',
            'empty' => 'No webinars match these filters.',
        ],
    ][$type];

    $months = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June',
               7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];

    $select = 'h-11 min-w-[10rem] rounded-lg border border-gray-200 bg-white px-4 text-sm text-navy outline-none focus:border-cyan focus:ring-2 focus:ring-cyan/20 transition';
@endphp

<x-layouts.app :title="$title" :description="[
        'blog' => 'Blogs and insights from Agarwal Brothers on laboratory instruments, applications and scientific research.',
        'news' => 'News, events and exhibitions from Agarwal Brothers: where to meet our specialists and see instruments in action.',
        'webinar' => 'Upcoming and recorded webinars on analytical instruments and laboratory workflows.',
     ][$type] ?? null">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[620px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.18),transparent_70%),radial-gradient(700px_380px_at_8%_14%,rgba(0,119,182,0.09),transparent_70%),radial-gradient(700px_380px_at_95%_20%,rgba(0,180,216,0.11),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $title }}</span>
        </div>

        {{-- ===== Hero ===== --}}
        @if ($type === 'news' && ! $hero)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
                <div>
                    <span class="section-badge">
                        <svg class="h-3.5 w-3.5 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5m4.75-11.396c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3"/>
                        </svg>
                        Agarwal Brothers Events
                    </span>
                    <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-bold text-navy leading-tight">
                        Events, Exhibitions &amp; <span class="text-cyan-ink">Scientific Updates</span>
                    </h1>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="rounded-full bg-navy px-4 py-1.5 text-[11px] font-bold uppercase tracking-wider text-white">At the center of lab innovation</span>
                        <span class="rounded-full bg-white border border-gray-200 px-4 py-1.5 text-xs font-medium text-navy">Latest updates</span>
                        <span class="rounded-full bg-white border border-gray-200 px-4 py-1.5 text-xs font-medium text-navy">Across India</span>
                    </div>
                    <p class="mt-5 max-w-xl text-base text-slate leading-relaxed">
                        Meet our specialists, explore application-led product demonstrations, and follow
                        Agarwal Brothers' presence across India's leading scientific exhibitions.
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-gray-100 shadow-lg p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-cyan-ink">Discover</p>
                            <h2 class="mt-1 text-lg font-semibold text-navy leading-snug">Explore events, initiatives and community highlights.</h2>
                        </div>
                        <svg class="h-6 w-6 shrink-0 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                        </svg>
                    </div>
                    <div class="mt-4 rounded-xl bg-ice border border-gray-100 px-4 py-4 text-sm text-slate leading-relaxed">
                        Browse our latest exhibitions, technical showcases and industry updates below. Filter by month or year to find an event quickly.
                    </div>
                </div>
            </div>
        @else
            <div class="relative w-full overflow-hidden rounded-3xl shadow-lg ring-1 ring-black/5">
                @if ($hero)
                    <img src="{{ $hero }}" alt="{{ $title }}"
                         class="img-load block w-full aspect-[16/7] sm:aspect-[3/1] object-cover" decoding="async" onload="this.classList.add('is-loaded')">
                @else
                    <div class="relative w-full aspect-[16/8] sm:aspect-[3/1] bg-gradient-to-br from-navy to-link flex items-center">
                        <div class="pointer-events-none absolute inset-0
                                    bg-[radial-gradient(600px_300px_at_85%_20%,rgba(0,180,216,0.35),transparent_70%),radial-gradient(500px_260px_at_10%_100%,rgba(0,180,216,0.2),transparent_70%)]"></div>
                        <div class="pointer-events-none absolute -right-10 -bottom-16 h-64 w-64 rounded-full border-[28px] border-white/5"></div>
                        <div class="pointer-events-none absolute right-24 top-6 h-24 w-24 rounded-full border-[10px] border-cyan/20"></div>

                        <div class="relative px-6 sm:px-12 lg:px-16">
                            <h2 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight">{{ $meta['banner_title'] }}</h2>
                            <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-lg font-semibold text-white/90">
                                @foreach ($meta['banner_tags'] as $tag)
                                    <span>{{ $tag }}</span>
                                    @unless ($loop->last)<span class="h-1.5 w-1.5 rounded-full bg-cyan"></span>@endunless
                                @endforeach
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===== Filters ===== --}}
        <form method="GET" action="{{ url()->current() }}"
              class="mt-12 flex flex-wrap items-center gap-x-12 gap-y-5 rounded-2xl bg-white border border-gray-100 shadow-sm px-6 py-6 sm:px-10 sm:py-8">
            <label class="flex items-center gap-4 text-sm font-semibold text-navy">
                Month
                <select name="month" onchange="this.form.submit()" class="{{ $select }}">
                    <option value="">All Months</option>
                    @foreach ($months as $num => $name)
                        <option value="{{ $num }}" @selected($filters['month'] === $num)>{{ $name }}</option>
                    @endforeach
                </select>
            </label>

            <label class="flex items-center gap-4 text-sm font-semibold text-navy">
                Year
                <select name="year" onchange="this.form.submit()" class="{{ $select }}">
                    <option value="">All Years</option>
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @selected($filters['year'] === (int) $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </label>

            @if ($type !== 'blog')
                <label class="flex items-center gap-3 text-sm font-semibold text-navy cursor-pointer">
                    <input type="checkbox" name="upcoming" value="1" @checked($filters['upcoming']) onchange="this.form.submit()"
                           class="h-4 w-4 rounded border-gray-300 text-cyan focus:ring-cyan">
                    Upcoming Only
                </label>
            @endif

            @if ($hasFilters)
                <a href="{{ url()->current() }}"
                   class="ml-auto rounded-lg bg-navy px-6 py-2.5 text-sm font-semibold text-white hover:bg-link transition-colors">
                    Reset Filters
                </a>
            @endif
        </form>

        {{-- ===== Heading ===== --}}
        <div class="mt-14 mb-8">
            <{{ ($type === 'news' && ! $hero) ? 'h2' : 'h1' }} class="text-2xl sm:text-3xl font-bold text-navy leading-tight">
                {{ $meta['heading'] }} <span class="text-cyan-ink">{{ $meta['accent'] }}</span>
            </{{ ($type === 'news' && ! $hero) ? 'h2' : 'h1' }}>
            <div class="mt-3 h-1 w-16 rounded-full bg-cyan"></div>
        </div>

        {{-- ===== Cards ===== --}}
        @if ($insights->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($insights as $insight)
                    <a href="{{ route('insights.show', $insight->slug) }}"
                        class="group flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-200 shadow-sm
                               hover:shadow-xl hover:border-cyan/50 hover:-translate-y-1 transition-all duration-300">

                        <div class="relative {{ $meta['ratio'] }} w-full overflow-hidden bg-gradient-to-br from-ice to-cyan/10 flex items-center justify-center">
                            @if ($insight->image)
                                <img src="{{ asset('storage/' . $insight->image) }}" alt="{{ $insight->title }}"
                                    class="img-load h-full w-full {{ $meta['fit'] }} transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($insight->image) !!} onload="this.classList.add('is-loaded')">
                            @else
                                <span class="px-6 text-center text-base font-bold text-navy/50 line-clamp-3">{{ $insight->title }}</span>
                            @endif

                            @if ($insight->pdf)
                                <span class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-white/95 px-2.5 py-1 text-[10px] font-bold uppercase text-link shadow">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                    </svg>
                                    PDF
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-5">
                            <p class="text-xs font-semibold text-link">
                                {{ ($insight->event_date ?? $insight->created_at)->format('d M Y') }}
                            </p>
                            <h3 class="mt-2 text-base font-bold text-navy leading-snug line-clamp-2 min-h-[2.75rem]
                                       group-hover:text-link transition-colors">
                                {{ $insight->title }}
                            </h3>
                            @if ($insight->excerpt)
                                <p class="mt-2 text-sm text-slate line-clamp-2 leading-relaxed">{{ $insight->excerpt }}</p>
                            @endif

                            <span class="mt-auto pt-4">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-navy px-5 py-2 text-xs font-semibold text-white group-hover:bg-link transition-colors btn-primary">
                                    {{ $insight->pdf ? 'View Details' : 'Read More' }}
                                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                    </svg>
                                </span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl bg-white border border-gray-100 text-center py-16">
                <p class="text-lg font-semibold text-navy">{{ $hasFilters ? $meta['empty'] : 'Nothing here yet' }}</p>
                <p class="mt-2 text-sm text-slate">
                    {{ $hasFilters ? 'Try a different month or year, or reset the filters.' : 'Check back later for updates.' }}
                </p>
                @if ($hasFilters)
                    <a href="{{ url()->current() }}" class="mt-5 inline-block rounded-full bg-navy px-6 py-2 text-sm font-semibold text-white hover:bg-link transition-colors btn-primary">
                        Reset Filters
                    </a>
                @endif
            </div>
        @endif
    </div>
  </div>
</x-layouts.app>
