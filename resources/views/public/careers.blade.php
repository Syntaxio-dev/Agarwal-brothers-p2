<x-layouts.app title="Careers" description="Join Agarwal Brothers. Explore open roles in sales, service and marketing, or share your profile for future opportunities.">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[620px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.20),transparent_70%),radial-gradient(700px_380px_at_8%_12%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_95%_18%,rgba(0,180,216,0.12),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        <div class="text-sm text-slate mb-8">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Careers</span>
        </div>

        {{-- ===== Hero ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <div>
                <h1 class="text-4xl sm:text-5xl font-bold text-cyan-ink leading-tight">We are hiring</h1>
                <h2 class="mt-3 text-2xl sm:text-3xl font-semibold text-navy leading-snug">
                    Do the most meaningful work of your career at <span class="text-link">Agarwal Brothers</span>
                </h2>
                <p class="mt-5 text-base text-slate leading-relaxed max-w-xl">
                    Agarwal Brothers is always on the lookout for exceptional talent. We hire for potential, not just positions,
                    and we care deeply about the emotional and mental well-being of every person on our team.
                </p>
                <a href="#openings"
                   class="mt-7 inline-flex items-center gap-2 rounded-full bg-navy px-7 py-3 text-sm font-semibold text-white shadow-md
                          hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                    View open roles
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                    </svg>
                </a>
            </div>

            <div class="rounded-3xl bg-white border border-gray-100 shadow-lg p-5 sm:p-7">
                <div class="flex justify-center">
                    <span class="section-badge">
                        Life at Agarwal Brothers
                    </span>
                </div>
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ([
                        ['Well-being first', 'We look after our people\'s emotional and mental health, not just their output.', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
                        ['Room to grow', 'Training, mentoring and clear paths to take on more responsibility.', 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941'],
                        ['Learn from experts', 'Work alongside specialists in laboratory instruments, chemicals and service.', 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342'],
                        ['Open culture', 'Ideas are welcome at every level, and people are treated with respect.', 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
                    ] as [$title, $desc, $icon])
                        <div class="rounded-2xl border border-gray-100 bg-gradient-to-br from-ice to-white p-4">
                            <div class="h-10 w-10 rounded-xl bg-cyan/10 flex items-center justify-center">
                                <svg class="h-5 w-5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                                </svg>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-navy">{{ $title }}</h3>
                            <p class="mt-1 text-xs text-slate leading-relaxed">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Open roles ===== --}}
        <div id="openings" class="mt-20 scroll-mt-6">
            <div class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">
                    Careers @ Agarwal Brothers
                </span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    Let's Work <span class="text-cyan-ink">Together</span>
                </h2>
                <p class="max-w-2xl text-sm text-slate">
                    Join a passionate team and do your best work, backed by a culture of growth.
                </p>
            </div>

            <div class="max-w-4xl mx-auto flex flex-col gap-4">
                @forelse ($openings as $opening)
                    <div class="group flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl bg-white border border-gray-100 shadow-sm
                                px-6 py-5 hover:shadow-lg hover:border-cyan/40 transition-all duration-300">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-navy group-hover:text-link transition-colors">{{ $opening->title }}</h3>
                            @if ($opening->summary)
                                <p class="mt-1 text-sm text-slate line-clamp-2">{{ $opening->summary }}</p>
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-ice border border-gray-100 px-3 py-1 text-xs font-semibold text-navy">
                                    <svg class="h-3.5 w-3.5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                    </svg>
                                    {{ $opening->location }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-ice border border-gray-100 px-3 py-1 text-xs font-semibold text-navy uppercase">
                                    <svg class="h-3.5 w-3.5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                    {{ $opening->employment_type }}
                                </span>
                                @if ($opening->department)
                                    <span class="inline-flex items-center rounded-full bg-cyan/10 px-3 py-1 text-xs font-semibold text-link">
                                        {{ $opening->department }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('careers.show', $opening->slug) }}"
                           class="shrink-0 inline-flex items-center justify-center gap-2 rounded-full bg-navy px-6 py-2.5 text-sm font-semibold text-white shadow-md
                                  hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                            Apply
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                            </svg>
                        </a>
                    </div>
                @empty
                    <div class="rounded-2xl bg-white border border-gray-100 p-10 text-center text-slate">
                        <p class="font-semibold text-navy">No open positions right now.</p>
                        <p class="mt-1 text-sm">Share your profile below and we'll reach out when a suitable role opens up.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== Banner ===== --}}
        <div class="mt-16 rounded-3xl bg-gradient-to-br from-navy to-link px-7 py-9 sm:px-12 sm:py-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-white">Don't see a perfect role?</h2>
            <p class="mt-2 text-sm sm:text-base text-white/80">We always welcome exceptional talent. Share your profile with us.</p>
        </div>

        {{-- ===== General application ===== --}}
        <div class="mt-14 max-w-4xl mx-auto">
            <div class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">
                    Apply to join our team
                </span>
            </div>

            @include('public.partials.application-form', ['action' => route('careers.apply.general')])
        </div>
    </div>
  </div>
</x-layouts.app>
