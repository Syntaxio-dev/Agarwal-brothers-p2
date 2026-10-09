@php
    $img = fn (?string $field) => ($s && $s->{$field}) ? asset('storage/' . $s->{$field}) : null;
    $founded = $s?->founded_year ?: 1981;

    $intro = $s?->story_intro ?: "Agarwal Brothers has been a trusted name in laboratory equipment, scientific instruments and chemicals since {$founded}. From a single office in Jaipur, we have grown into one of Rajasthan's most respected scientific solutions providers.";

    $points = collect($s?->story_points ?? [])->pluck('text')->filter()->values();
    if ($points->isEmpty()) {
        $points = collect([
            'Authorised partner of 50+ global brands',
            'Installation, calibration and AMC support',
            'Trained application specialists',
            'Fast, reliable service across Rajasthan',
        ]);
    }

    $tabs = [
        'mission' => ['label' => 'Our Mission', 'title' => 'Our Company Mission',
            'text' => $s?->story_mission ?: 'To equip every laboratory we serve with reliable, world-class instruments and the technical support to use them well, so that scientists can focus on their work and not on their equipment.'],
        'vision' => ['label' => 'Our Vision', 'title' => 'Our Company Vision',
            'text' => $s?->story_vision ?: 'To be the most trusted scientific solutions partner in India, known for honest advice, dependable service and long-term relationships with the labs we work with.'],
        'goal' => ['label' => 'Our Goal', 'title' => 'Our Company Goal',
            'text' => $s?->story_goal ?: 'To keep growing our portfolio of global principals and our service reach, while keeping the personal attention that has defined Agarwal Brothers since the very first day.'],
    ];

    $timeline = [
        [(string) $founded, 'The Beginning', 'Founded in Jaipur, Rajasthan, with a vision to bring world-class scientific instruments to Indian laboratories. Started as a small trading firm supplying lab chemicals and basic equipment.'],
        ['1990s', 'Expanding Horizons', 'Established partnerships with leading global instrument manufacturers and expanded the portfolio to analytical instruments, centrifuges, balances and chromatography systems.'],
        ['2000s', "Serving India's Pharma Boom", "As India's pharmaceutical industry grew, so did our reach. We became a preferred supplier to major pharma companies, CROs and academic institutions across Rajasthan and beyond."],
        ['Today', "{$years}+ Years Strong", 'Today Agarwal Brothers represents 50+ global brands and serves 36,000+ customers, continuing our commitment to equipping India\'s labs with the finest scientific solutions.'],
    ];

    $values = [
        ['Quality First', 'We represent only globally certified brands with proven track records, because your research deserves nothing less.', 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ['Customer Partnership', 'We do not just sell instruments. We partner with your lab, understand your application and recommend the right solution.', 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
        ['After-Sales Support', 'Installation, calibration, AMCs and rapid spares support. We stand behind every instrument we sell.', 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.65-5.65 3.029 2.497c.379.312.62.74.757 1.209m-5.786-3.706L5.18 6.748a2.25 2.25 0 0 1 2.12-3.763l6.33 1.53'],
        ['Technical Expertise', 'Our team includes trained application specialists who understand your science, not just the instruments.', 'M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18'],
        ['Pan-India Reach', 'With 12+ branches across India, we bring the same level of expertise and service to labs wherever they are.', 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 0 1 15 0Z'],
        ['Training & Demos', 'We conduct demonstrations, user training and application workshops so your team gets the most from their investment.', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
    ];

    $banner = $img('story_banner');
@endphp

<x-layouts.app title="Our Story">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[640px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.16),transparent_70%),radial-gradient(700px_380px_at_6%_16%,rgba(0,119,182,0.08),transparent_70%),radial-gradient(700px_380px_at_96%_22%,rgba(0,180,216,0.10),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-6 sm:py-10">

        {{-- ===== 1. Banner ===== --}}
        <div class="relative flex h-[250px] items-end overflow-hidden rounded-3xl shadow-lg ring-1 ring-black/5 sm:h-[430px]">
            <img src="{{ $banner ?: asset('building-image.png') }}" alt="Agarwal Brothers office, Jaipur"
                 class="absolute inset-0 h-full w-full object-cover object-[50%_22%]">
            <div class="absolute inset-0 bg-gradient-to-t from-navy/90 via-navy/25 to-transparent"></div>
            <div class="relative px-6 pb-6 text-left sm:px-10 sm:pb-9">
                <h1 class="text-3xl sm:text-5xl font-bold text-white leading-tight">Our Story</h1>
                <p class="mt-3 text-sm text-white/80">
                    <a href="/" class="hover:text-cyan transition">Home</a>
                    <span class="mx-1.5 text-cyan">&rsaquo;</span>
                    <span class="font-semibold text-white">Our Story</span>
                </p>
            </div>
        </div>

        {{-- ===== 2. About ===== --}}
        <div class="mt-16 sm:mt-20 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            <div class="relative mx-auto w-full max-w-lg h-[360px] sm:h-[440px]">
                <div class="absolute left-0 top-0 h-[72%] w-[68%] overflow-hidden rounded-3xl shadow-xl bg-gradient-to-br from-ice to-cyan/20 flex items-center justify-center">
                    @if ($img('story_image_1'))
                        <img src="{{ $img('story_image_1') }}" alt="" class="h-full w-full object-cover">
                    @else
                        <img src="{{ asset('sidebar-logo.png') }}" alt="Agarwal Brothers" class="w-3/4 object-contain">
                    @endif
                </div>

                <div class="absolute bottom-0 right-0 h-[56%] w-[58%] overflow-hidden rounded-3xl border-4 border-white shadow-2xl bg-gradient-to-br from-navy to-link flex items-center justify-center">
                    @if ($img('story_image_2'))
                        <img src="{{ $img('story_image_2') }}" alt="" class="h-full w-full object-cover">
                    @else
                        <div class="text-center text-white">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-cyan">Since</p>
                            <p class="text-4xl sm:text-5xl font-extrabold leading-none">{{ $founded }}</p>
                        </div>
                    @endif
                </div>

                <div class="absolute right-0 top-4 sm:top-6 rounded-2xl bg-gradient-to-br from-navy to-link px-5 py-4 text-white shadow-xl ring-4 ring-white">
                    <p class="text-3xl font-extrabold leading-none">{{ $years }}<span class="text-cyan">+</span></p>
                    <p class="mt-1 text-[10px] font-semibold uppercase tracking-wider text-white/85 leading-tight">Years of<br>Experience</p>
                </div>
            </div>

            <div>
                <span class="section-badge">Company About</span>
                <h2 class="mt-4 text-2xl sm:text-4xl font-bold text-navy leading-tight">
                    Four decades of powering <span class="text-cyan">India's laboratories</span>
                </h2>
                <p class="mt-5 text-base text-slate leading-relaxed">{{ $intro }}</p>

                <p class="mt-6 text-sm font-bold text-navy">What sets us apart</p>
                <ul class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3">
                    @foreach ($points as $point)
                        <li class="flex items-start gap-2.5 text-sm text-slate">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-navy to-link text-white">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>

                <a href="/contact-us"
                   class="mt-8 inline-flex items-center gap-2 rounded-full bg-navy px-7 py-3 text-sm font-semibold text-white hover:bg-link btn-primary">
                    Talk to our team
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- ===== 3. Mission / Vision / Goal ===== --}}
        <div class="mt-20 sm:mt-24 rounded-3xl bg-gradient-to-br from-ice to-cyan/10 border border-cyan/20 p-6 sm:p-10 lg:p-12
                    grid grid-cols-1 lg:grid-cols-2 gap-10 items-center"
             x-data="{ tab: 'mission' }">
            <div>
                <span class="section-badge">About Mission</span>
                <h2 class="mt-4 text-2xl sm:text-3xl font-bold text-navy leading-tight">
                    Our main goal is <span class="text-cyan">satisfied labs</span>, everywhere in India
                </h2>

                <div class="mt-6 flex flex-wrap gap-2.5">
                    @foreach ($tabs as $key => $tab)
                        <button type="button" @click="tab = '{{ $key }}'"
                                :class="tab === '{{ $key }}' ? 'chip-active' : ''"
                                class="chip !pl-4">
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>

                <div class="mt-6 grid">
                    @foreach ($tabs as $key => $tab)
                        <div :class="tab === '{{ $key }}' ? 'opacity-100' : 'opacity-0 pointer-events-none'"
                             class="[grid-area:1/1] transition-opacity duration-300">
                            <h3 class="text-lg font-bold text-navy">{{ $tab['title'] }}</h3>
                            <p class="mt-2 text-sm sm:text-base text-slate leading-relaxed">{{ $tab['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative aspect-[4/3] w-full overflow-hidden rounded-3xl shadow-xl ring-4 ring-white bg-gradient-to-br from-navy to-link flex items-center justify-center">
                @if ($img('story_mission_image'))
                    <img src="{{ $img('story_mission_image') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                @else
                    <div class="absolute inset-0 bg-[radial-gradient(400px_220px_at_80%_10%,rgba(0,180,216,0.4),transparent_70%)]"></div>
                    <div class="relative text-center text-white">
                        <svg class="mx-auto h-12 w-12 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                        </svg>
                        <p class="mt-3 text-sm font-semibold uppercase tracking-[0.25em] text-cyan">Serving science since</p>
                        <p class="text-5xl font-extrabold">{{ $founded }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== 4. Journey ===== --}}
        <div class="mt-20 sm:mt-24">
            <div class="text-center flex flex-col items-center gap-3 mb-10">
                <span class="section-badge">Our Journey</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-navy leading-tight">From one office to <span class="text-cyan">50+ global brands</span></h2>
            </div>

            @php
                $n = count($timeline);
                $pts = [];
                foreach (array_keys($timeline) as $i) {
                    $pts[] = [($i + 0.5) / $n * 1000, $i % 2 === 0 ? 34 : 86];
                }
                $first = $pts[0];
                $last = $pts[$n - 1];
                $d = sprintf('M0 60 C %.1f 60, %.1f %.1f, %.1f %.1f', $first[0] * 0.5, $first[0] * 0.55, $first[1], $first[0], $first[1]);
                for ($i = 1; $i < $n; $i++) {
                    $mx = ($pts[$i - 1][0] + $pts[$i][0]) / 2;
                    $d .= sprintf(' C %.1f %.1f, %.1f %.1f, %.1f %.1f', $mx, $pts[$i - 1][1], $mx, $pts[$i][1], $pts[$i][0], $pts[$i][1]);
                }
                $d .= sprintf(' C %.1f %.1f, %.1f 60, 1000 60', $last[0] + (1000 - $last[0]) * 0.45, $last[1], $last[0] + (1000 - $last[0]) * 0.55);
            @endphp

            {{-- Desktop: dotted wave with nodes, cards below --}}
            <div class="hidden sm:block">
                <div class="relative h-[120px]">
                    <svg viewBox="0 0 1000 120" preserveAspectRatio="none" class="absolute inset-0 h-full w-full overflow-visible" aria-hidden="true">
                        <path d="{{ $d }}" fill="none" stroke="#00B4D8" stroke-width="3.5" stroke-linecap="round"
                              stroke-dasharray="1 10" vector-effect="non-scaling-stroke"/>
                    </svg>

                    @foreach ($timeline as $i => $item)
                        @php $x = ($i + 0.5) / $n * 100; $top = $pts[$i][1] / 120 * 100; @endphp
                        <span class="absolute border-l-2 border-dotted border-cyan/60"
                              style="left: {{ $x }}%; top: {{ $top }}%; height: {{ 100 - $top }}%"></span>
                        <span class="absolute -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-white shadow-md
                                     {{ $loop->last ? 'h-6 w-6 bg-link ring-4 ring-cyan/30' : 'h-5 w-5 bg-cyan' }}"
                              style="left: {{ $x }}%; top: {{ $top }}%"></span>
                    @endforeach
                </div>

                <div class="grid gap-4" style="grid-template-columns: repeat({{ $n }}, minmax(0, 1fr))">
                    @foreach ($timeline as [$when, $title, $text])
                        <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-5 text-center
                                    hover:shadow-lg hover:border-cyan/40 hover:-translate-y-1 transition-all duration-300">
                            <span class="inline-block rounded-md bg-cyan/10 px-2.5 py-1 font-mono text-xs font-medium uppercase tracking-widest text-link">{{ $when }}</span>
                            <h3 class="mt-3 text-base font-bold text-navy leading-snug">{{ $title }}</h3>
                            <p class="mt-1.5 text-sm text-slate leading-relaxed">{{ $text }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Mobile: compact dashed list --}}
            <div class="sm:hidden ml-3 space-y-5 border-l-2 border-dashed border-cyan/50">
                @foreach ($timeline as [$when, $title, $text])
                    <div class="relative pl-6">
                        <span class="absolute -left-[9px] top-5 h-4 w-4 rounded-full border-4 border-white shadow {{ $loop->last ? 'bg-link ring-2 ring-cyan/30' : 'bg-cyan' }}"></span>
                        <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4">
                            <span class="inline-block rounded-md bg-cyan/10 px-2 py-0.5 font-mono text-xs font-medium uppercase tracking-widest text-link">{{ $when }}</span>
                            <h3 class="mt-2 text-base font-bold text-navy">{{ $title }}</h3>
                            <p class="mt-1 text-sm text-slate leading-relaxed">{{ $text }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== 5. Values ===== --}}
        <div class="mt-20 sm:mt-24">
            <div class="text-center flex flex-col items-center gap-3 mb-10">
                <span class="section-badge">Our Values</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-navy leading-tight">What <span class="text-cyan">drives us</span></h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($values as [$title, $text, $icon])
                    <div class="group rounded-2xl bg-white border border-gray-100 shadow-sm p-6 hover:shadow-xl hover:border-cyan/40 hover:-translate-y-1 transition-all duration-300">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-navy to-link text-white shadow-md
                                    group-hover:from-link group-hover:to-cyan transition-all duration-300">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-navy">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-slate leading-relaxed">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== 6. Leadership blocks ===== --}}
        @if ($leaders->count())
            @php $leadBg = $img('leadership_bg'); @endphp
            <div class="mt-20 sm:mt-24">
                <div class="text-center flex flex-col items-center gap-3 mb-10">
                    <span class="section-badge">Meet our leadership</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-navy leading-tight">The people <span class="text-cyan">behind the company</span></h2>
                </div>

                <div class="flex flex-col gap-8 sm:gap-10">
                    @foreach ($leaders as $i => $leader)
                        @php $flip = $i % 2 === 1; @endphp
                        <div class="relative overflow-hidden rounded-3xl shadow-xl ring-1 ring-black/5 bg-gradient-to-br from-navy to-link">
                            @if ($leadBg)
                                <img src="{{ $leadBg }}" alt="" class="absolute inset-0 h-full w-full scale-105 object-cover blur-[3px]">
                                <div class="absolute inset-0 bg-gradient-to-br from-navy/70 via-navy/45 to-link/40"></div>
                            @else
                                <div class="absolute inset-0 bg-[radial-gradient(520px_280px_at_{{ $flip ? '15%' : '85%' }}_10%,rgba(0,180,216,0.38),transparent_70%)]"></div>
                                <div class="pointer-events-none absolute -bottom-24 {{ $flip ? '-right-16' : '-left-16' }} h-72 w-72 rounded-full border-[28px] border-white/5"></div>
                            @endif

                            <div class="relative grid grid-cols-1 md:grid-cols-2 items-center gap-2 md:gap-6 px-5 pt-8 pb-6 md:py-0 md:px-12">
                                {{-- Portrait --}}
                                <div class="{{ $flip ? 'md:order-2' : '' }} flex h-[260px] items-end justify-center md:h-[400px] lg:h-[430px]">
                                    @if ($leader->photo)
                                        <img src="{{ asset('storage/' . $leader->photo) }}" alt="{{ $leader->name }}"
                                             class="h-full w-auto max-w-full object-contain object-bottom drop-shadow-2xl">
                                    @else
                                        <svg viewBox="0 0 200 240" class="h-full w-auto text-white/25" fill="currentColor" role="img" aria-label="Photo placeholder">
                                            <circle cx="100" cy="78" r="46"/>
                                            <path d="M10 240c0-58 40-92 90-92s90 34 90 92z"/>
                                        </svg>
                                    @endif
                                </div>

                                {{-- Quote card (glass) --}}
                                <div class="{{ $flip ? 'md:order-1' : '' }} md:py-12">
                                    <div class="rounded-2xl border border-gray-100 bg-white/95 p-6 shadow-lg sm:p-8">
                                        <svg class="h-7 w-7 text-cyan/60" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10H0z"/>
                                        </svg>
                                        <p class="mt-2 text-base leading-relaxed text-navy sm:text-lg">
                                            {{ $leader->quote ?: 'A message from our leadership will appear here.' }}
                                        </p>
                                        <p class="mt-5 text-lg font-semibold text-link">
                                            {{ $leader->name }}@if ($leader->designation), <span class="font-medium text-navy/80">{{ $leader->designation }}</span>@endif
                                        </p>
                                        @if ($leader->linkedin_url)
                                            <a href="{{ $leader->linkedin_url }}" target="_blank" rel="noopener" aria-label="{{ $leader->name }} on LinkedIn"
                                               class="mt-4 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-navy text-white transition-colors hover:bg-link">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== 6b. Team grid ===== --}}
        @if ($team->count())
            <div class="mt-20 sm:mt-24">
                <div class="text-center flex flex-col items-center gap-3 mb-10">
                    <span class="section-badge">Our people</span>
                    <h2 class="text-2xl sm:text-3xl font-bold text-navy leading-tight">Meet the <span class="text-cyan">team</span></h2>
                </div>

                <div class="flex flex-wrap justify-center gap-6">
                    @foreach ($team as $member)
                        <div class="group w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(25%-1.15rem)] overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm
                                    hover:shadow-xl hover:border-cyan/40 hover:-translate-y-1 transition-all duration-300">
                            <div class="aspect-[4/5] w-full overflow-hidden bg-gradient-to-br from-ice to-cyan/20 flex items-center justify-center">
                                @if ($member->photo)
                                    <img src="{{ asset('storage/' . $member->photo) }}" alt="{{ $member->name }}"
                                         class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <span class="flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-navy to-link text-3xl font-bold text-white shadow-lg">
                                        {{ strtoupper(mb_substr($member->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>
                            <div class="p-5 text-center">
                                <h3 class="text-base font-bold text-navy">{{ $member->name }}</h3>
                                @if ($member->designation)
                                    <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-link">{{ $member->designation }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- ===== 7. Reviews ===== --}}
    @include('public.partials.reviews-carousel')

    {{-- ===== 8. CTA ===== --}}
    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 pb-14 sm:pb-16">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy to-link px-8 py-10 sm:px-12 sm:py-12
                    flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full border-[26px] border-white/5"></div>
            <div class="relative">
                <h3 class="text-xl sm:text-2xl font-bold text-white">Ready to work with us?</h3>
                <p class="mt-2 text-sm text-white/75 max-w-md">Talk to our team about your lab requirements and we will help you find the right solution.</p>
            </div>
            <a href="/contact-us"
               class="btn-glass relative shrink-0 px-7 py-3">
                Get in Touch
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                </svg>
            </a>
        </div>
    </div>
  </div>
</x-layouts.app>
