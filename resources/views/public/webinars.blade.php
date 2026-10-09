@php
    $tz = \App\Models\Insight::TZ;
    $nextStart = $next?->start_ist;
    $calendar = null;

    if ($next && $nextStart) {
        if ($next->time_tbd) {
            $dates = $nextStart->format('Ymd') . '/' . $nextStart->copy()->addDay()->format('Ymd');
        } else {
            $s = $nextStart->copy()->utc();
            $dates = $s->format('Ymd\THis\Z') . '/' . $s->copy()->addHour()->format('Ymd\THis\Z');
        }
        $calendar = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text=' . urlencode($next->title)
            . '&dates=' . $dates
            . '&details=' . urlencode(strip_tags((string) $next->excerpt))
            . '&location=' . urlencode($next->venue ?: 'Online');
    }
@endphp

<x-layouts.app title="Webinars">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[640px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.20),transparent_70%),radial-gradient(700px_380px_at_6%_16%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_96%_22%,rgba(0,180,216,0.13),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[520px] opacity-40
                bg-[linear-gradient(to_right,rgba(11,37,69,0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgba(11,37,69,0.05)_1px,transparent_1px)] bg-[size:44px_44px]
                [mask-image:linear-gradient(to_bottom,black,transparent)]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        <div class="text-sm text-slate mb-8">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Webinars</span>
        </div>

        {{-- ===== Hero: next webinar + countdown ===== --}}
        @if ($next)
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-center">
                <div class="lg:col-span-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-cyan/40 bg-cyan/10 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.16em] text-link">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            Next Webinar
                        </span>
                        @if ($next->brand)
                            <span class="rounded-full border border-gray-200 bg-white px-3.5 py-1.5 text-xs font-semibold text-navy">{{ $next->brand->name }}</span>
                        @endif
                    </div>

                    <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-bold text-navy leading-tight">{{ $next->title }}</h1>

                    @if ($next->excerpt)
                        <p class="mt-4 max-w-2xl text-base text-slate leading-relaxed">{{ $next->excerpt }}</p>
                    @endif

                    <p class="mt-5 inline-flex items-center gap-2 text-sm font-bold text-navy">
                        <svg class="h-4 w-4 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        {{ $nextStart->format('F j') }} |
                        {{ $next->time_tbd ? 'Time to be announced' : $nextStart->format('h:i A') . ' IST' }}
                        &middot; {{ $next->venue ?: 'Online' }}
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a href="{{ $next->registration_url ?: route('insights.show', $next->slug) }}"
                           @if ($next->registration_url) target="_blank" rel="noopener" @endif
                           class="btn-primary inline-flex items-center gap-2 rounded-full bg-navy px-7 py-3 text-sm font-semibold text-white hover:bg-link">
                            {{ $next->registration_url ? 'Register now' : 'View details' }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                            </svg>
                        </a>
                        @if ($next->registration_url)
                            <a href="{{ route('insights.show', $next->slug) }}" class="btn-ghost">View details</a>
                        @endif
                        <a href="{{ $calendar }}" target="_blank" rel="noopener" class="btn-ghost">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                            </svg>
                            Add to calendar
                        </a>
                    </div>
                </div>

                {{-- Countdown card --}}
                <div class="lg:col-span-2"
                     x-data="{
                        target: new Date('{{ $nextStart->toIso8601String() }}').getTime(),
                        tbd: {{ $next->time_tbd ? 'true' : 'false' }},
                        d: 0, h: 0, m: 0, s: 0, over: false,
                        pad(n) { return String(n).padStart(2, '0') },
                        tick() {
                            let diff = this.target - Date.now();
                            if (diff <= 0) { this.over = true; diff = 0 }
                            this.d = Math.floor(diff / 864e5);
                            this.h = Math.floor(diff % 864e5 / 36e5);
                            this.m = Math.floor(diff % 36e5 / 6e4);
                            this.s = Math.floor(diff % 6e4 / 1e3);
                        }
                     }"
                     x-init="tick(); setInterval(() => tick(), 1000)">
                    <div class="rounded-3xl bg-white/90 border border-gray-100 shadow-xl p-6 sm:p-7">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-link" x-text="over ? 'Happening now' : 'Starts in'"></p>
                                <p class="mt-1 text-lg font-semibold text-navy">Webinar countdown</p>
                            </div>
                            <div class="h-14 w-24 shrink-0 rounded-xl border border-gray-100 bg-white flex items-center justify-center p-2">
                                @if ($next->brand?->logo)
                                    <img src="{{ asset('storage/' . $next->brand->logo) }}" alt="{{ $next->brand->name }}" class="max-h-full max-w-full object-contain">
                                @elseif ($next->brand)
                                    <span class="text-xs font-bold text-navy text-center">{{ $next->brand->name }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 h-px bg-gradient-to-r from-transparent via-cyan/40 to-transparent"></div>

                        <div class="mt-5 grid grid-cols-4 gap-2.5 sm:gap-3">
                            @foreach ([['d', 'Days'], ['h', 'Hours'], ['m', 'Minutes'], ['s', 'Seconds']] as [$key, $label])
                                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-navy to-link px-1 py-3.5 text-center shadow-lg shadow-navy/20">
                                    <div class="absolute inset-x-0 top-1/2 h-px bg-white/10"></div>
                                    <p class="text-2xl sm:text-3xl font-bold text-white tabular-nums leading-none"
                                       x-text="{{ $key === 'd' ? 'pad(d)' : "tbd ? '--' : pad($key)" }}"></p>
                                    <p class="mt-2 text-[9px] sm:text-[10px] font-semibold uppercase tracking-wider text-cyan">{{ $label }}</p>
                                </div>
                            @endforeach
                        </div>

                        <p class="mt-5 text-center text-xs text-slate">Times are displayed in Indian Standard Time (IST)</p>
                    </div>
                </div>
            </div>
        @else
            <div class="relative w-full overflow-hidden rounded-3xl shadow-lg ring-1 ring-black/5">
                @if ($hero)
                    <img src="{{ $hero }}" alt="Webinars" class="block w-full aspect-[16/7] sm:aspect-[3/1] object-cover">
                @else
                    <div class="relative w-full aspect-[16/8] sm:aspect-[3/1] bg-gradient-to-br from-navy to-link flex items-center">
                        <div class="pointer-events-none absolute inset-0
                                    bg-[radial-gradient(600px_300px_at_85%_20%,rgba(0,180,216,0.35),transparent_70%),radial-gradient(500px_260px_at_10%_100%,rgba(0,180,216,0.2),transparent_70%)]"></div>
                        <div class="relative px-6 sm:px-12 lg:px-16">
                            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight">Webinars</h1>
                            <p class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-lg font-semibold text-white/90">
                                <span>Learn</span><span class="h-1.5 w-1.5 rounded-full bg-cyan"></span>
                                <span>Connect</span><span class="h-1.5 w-1.5 rounded-full bg-cyan"></span>
                                <span>Explore</span><span class="h-1.5 w-1.5 rounded-full bg-cyan"></span>
                                <span>Grow</span>
                            </p>
                        </div>
                    </div>
                @endif
            </div>
            <p class="mt-5 text-center text-sm text-slate">No upcoming webinars are scheduled right now. Browse past sessions below.</p>
        @endif

        {{-- ===== Principal filter ===== --}}
        @if ($principals->count())
            <div class="mt-14">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate">Filter by principal</p>
                <div class="mt-3 flex flex-wrap gap-3">
                    <a href="{{ route('insights.webinars') }}" class="chip {{ $selectedSlug ? '' : 'chip-active' }} !pl-4">
                        All Principals ({{ $totalCount }})
                    </a>
                    @foreach ($principals as $row)
                        <a href="{{ route('insights.webinars', ['principal' => $row['brand']->slug]) }}"
                           class="chip {{ $selectedSlug === $row['brand']->slug ? 'chip-active' : '' }}">
                            <span class="flex h-7 w-9 items-center justify-center overflow-hidden rounded-md bg-white p-0.5">
                                @if ($row['brand']->logo)
                                    <img src="{{ asset('storage/' . $row['brand']->logo) }}" alt="" class="max-h-full max-w-full object-contain">
                                @endif
                            </span>
                            {{ $row['brand']->name }} ({{ $row['count'] }})
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== Upcoming ===== --}}
        <div class="mt-12">
            <div class="flex items-center gap-4">
                <h2 class="text-xl sm:text-2xl font-bold text-navy">Upcoming</h2>
                <div class="h-px flex-1 bg-gradient-to-r from-cyan/40 to-transparent"></div>
            </div>

            <div class="mt-6 flex flex-col gap-4">
                @forelse ($upcoming as $w)
                    @include('public.partials.webinar-row', ['w' => $w, 'past' => false])
                @empty
                    <div class="rounded-2xl bg-white border border-gray-100 text-center py-12">
                        <p class="font-semibold text-navy">No upcoming webinars{{ $selectedSlug ? ' for this principal' : '' }}.</p>
                        <p class="mt-1 text-sm text-slate">Check back soon, or explore past sessions below.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== Past ===== --}}
        @if ($past->count())
            <div class="mt-14">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl sm:text-2xl font-bold text-navy">Past webinars &amp; recordings</h2>
                    <div class="h-px flex-1 bg-gradient-to-r from-cyan/40 to-transparent"></div>
                </div>

                <div class="mt-6 flex flex-col gap-4">
                    @foreach ($past as $w)
                        @include('public.partials.webinar-row', ['w' => $w, 'past' => true])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
  </div>
</x-layouts.app>
