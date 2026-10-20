<x-layouts.app title="Careers" description="Join Agarwal Brothers. Explore open roles in sales, service and marketing, or share your profile for future opportunities.">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[620px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.20),transparent_70%),radial-gradient(700px_380px_at_8%_12%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_95%_18%,rgba(0,180,216,0.12),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [['Home', '/'], ['Careers', null]]])

        {{-- ===== Hero ===== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 items-center">
            <div>
                <h1 data-reveal class="text-4xl sm:text-5xl font-bold text-cyan-ink leading-tight">We are hiring</h1>
                <h2 data-reveal class="mt-3 text-2xl sm:text-3xl font-semibold text-navy leading-snug">
                    Do the most meaningful work of your career at <span class="text-link">Agarwal Brothers</span>
                </h2>
                <p data-reveal class="mt-5 text-base text-slate leading-relaxed max-w-xl">
                    Agarwal Brothers is always on the lookout for exceptional talent. We hire for potential, not just positions,
                    and we care deeply about the emotional and mental well-being of every person on our team.
                </p>
                <a data-reveal href="#openings"
                   class="mt-7 inline-flex items-center gap-2 rounded-full bg-navy px-7 py-3 text-sm font-semibold text-white shadow-md
                          hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                    View open roles
                    <svg class="arrow-nudge-down h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                    </svg>
                </a>
            </div>

            <div data-reveal="right" class="rounded-3xl bg-white border border-gray-100 shadow-lg p-5 sm:p-7">
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
                        <div data-reveal="zoom" class="group rounded-2xl border border-gray-100 bg-gradient-to-br from-ice to-white p-4 transition-all duration-300 hover:-translate-y-1 hover:border-cyan/40 hover:shadow-md">
                            <div class="pop-icon h-10 w-10 rounded-xl bg-cyan/10 flex items-center justify-center transition-colors duration-300 group-hover:bg-navy">
                                <svg class="h-5 w-5 text-link transition-colors duration-300 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
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

        {{-- ===== About us as an employer (facts come from App\Support\Company and config/contact.php) ===== --}}
        <div class="mt-16 grid grid-cols-1 items-center gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <div data-reveal="left">
                <span class="section-badge">Who we are</span>
                <h2 class="mt-3 text-xl font-bold leading-tight text-navy sm:text-2xl">
                    A Jaipur company that has equipped <span class="text-cyan-ink">laboratories since {{ \App\Support\Company::foundedYear() }}</span>
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-slate sm:text-base">
                    Agarwal Brothers supplies laboratory equipment, scientific instruments and chemicals. We are an authorised partner of
                    {{ \App\Support\Company::plus('brands') }} global brands and work with laboratories in {{ \App\Support\Company::plus('industries') }} industries,
                    from pharma and dairy to mining and power. Joining us means working with specialists who help those laboratories choose, install and keep their instruments running.
                </p>
            </div>

            <div data-reveal="right" class="rounded-2xl border border-gray-100 bg-ice p-6">
                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.2em] text-cyan-ink">Where you will work</p>
                <p class="mt-3 text-sm font-semibold text-navy">{{ config('contact.head_office.label') }} - {{ config('contact.head_office.city') }}</p>
                <p class="mt-1 text-sm leading-relaxed text-slate">{{ config('contact.head_office.address') }}</p>
                <p class="mt-3 text-sm text-slate">{{ config('contact.working_days.0') }}</p>
                <p class="mt-3 text-xs leading-relaxed text-slate">The location of each role is shown on its card below.</p>
            </div>
        </div>

        {{-- ===== Trust strip: real company facts ===== --}}
        @include('public.partials.trust-strip', ['class' => 'mt-10'])

        {{-- ===== Open roles ===== --}}
        @php
            // Each department gets a small accent (icon, tint and a left edge), all in the site palette.
            $deptStyles = [
                'sales'   => ['icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941', 'chip' => 'bg-link/10 text-link', 'edge' => 'border-l-link', 'pill' => 'bg-link/10 text-link'],
                'service' => ['icon' => 'M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z', 'chip' => 'bg-success/10 text-success', 'edge' => 'border-l-success', 'pill' => 'bg-success/10 text-navy'],
                'office'  => ['icon' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0', 'chip' => 'bg-cyan/15 text-cyan-ink', 'edge' => 'border-l-cyan', 'pill' => 'bg-cyan/15 text-cyan-ink'],
                'ops'     => ['icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z', 'chip' => 'bg-navy/10 text-navy', 'edge' => 'border-l-navy', 'pill' => 'bg-navy/10 text-navy'],
                'other'   => ['icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z', 'chip' => 'bg-slate/10 text-slate', 'edge' => 'border-l-slate', 'pill' => 'bg-slate/10 text-slate'],
            ];
            $deptKey = function (?string $dept): string {
                $d = strtolower((string) $dept);
                foreach (['sales' => ['sales', 'marketing', 'business', 'digital', 'social'],
                          'service' => ['service', 'technical', 'engineer', 'repair'],
                          'office' => ['office', 'support', 'account', 'finance', 'hr', 'admin', 'front', 'back', 'reception'],
                          'ops' => ['operation', 'logistic', 'store', 'warehouse', 'dispatch', 'purchase']] as $key => $words) {
                    foreach ($words as $w) {
                        if (str_contains($d, $w)) {
                            return $key;
                        }
                    }
                }

                return 'other';
            };

            // Department chips: only when the list is long enough to need them (6+ roles in 2+ departments)
            $deptGroups = $openings->groupBy(fn ($o) => strtolower(trim($o->department ?: 'General')));
            $showFilter = $openings->count() >= 6 && $deptGroups->count() > 1;
        @endphp
        <div id="openings" class="mt-16 scroll-mt-6" x-data="{ dept: 'all' }">
            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-8">
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

            @if ($showFilter)
                <div data-reveal class="mx-auto mb-6 flex max-w-4xl flex-wrap justify-center gap-2" role="group" aria-label="Filter roles by department">
                    <button type="button" class="chip" :class="{ 'chip-active': dept === 'all' }" @click="dept = 'all'" :aria-pressed="dept === 'all'">All roles <span class="opacity-70">({{ $openings->count() }})</span></button>
                    @foreach ($deptGroups as $groupSlug => $group)
                        <button type="button" class="chip" :class="{ 'chip-active': dept === @js($groupSlug) }" @click="dept = @js($groupSlug)" :aria-pressed="dept === @js($groupSlug)">{{ trim($group->first()->department ?: 'General') }} <span class="opacity-70">({{ $group->count() }})</span></button>
                    @endforeach
                </div>
            @endif

            <div class="max-w-4xl mx-auto flex flex-col gap-4">
                @forelse ($openings as $opening)
                    @php
                        $ds = $deptStyles[$deptKey($opening->department)];
                        $openingDept = strtolower(trim($opening->department ?: 'General'));
                        $tag = 'inline-flex items-center gap-1.5 rounded-full bg-ice border border-gray-100 px-3 py-1 text-xs font-semibold text-navy';
                    @endphp
                    <div data-reveal @if ($showFilter) x-show="dept === 'all' || dept === @js($openingDept)" @endif
                         class="edge-left group relative overflow-hidden flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl bg-white border border-gray-100 border-l-[3px] {{ $ds['edge'] }} shadow-sm
                                px-5 py-5 hover:shadow-lg hover:border-cyan/40 hover:border-l-cyan transition-all duration-300">
                        <div class="flex min-w-0 flex-1 items-start gap-4">
                            <span class="mt-0.5 hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $ds['chip'] }} sm:flex" aria-hidden="true">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ds['icon'] }}"/>
                                </svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-bold text-navy group-hover:text-link transition-colors">{{ $opening->title }}</h3>
                                @if ($opening->summary)
                                    <p class="mt-1 text-sm text-slate line-clamp-2">{{ $opening->summary }}</p>
                                @endif
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($opening->department)
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $ds['pill'] }}">{{ $opening->department }}</span>
                                    @endif
                                    <span class="{{ $tag }}">
                                        <svg class="h-3.5 w-3.5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                        </svg>
                                        {{ $opening->location }}
                                    </span>
                                    <span class="{{ $tag }} uppercase">
                                        <svg class="h-3.5 w-3.5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                        </svg>
                                        {{ $opening->employment_type }}
                                    </span>
                                    @if ($opening->experience)
                                        <span class="{{ $tag }}">
                                            <svg class="h-3.5 w-3.5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342"/>
                                            </svg>
                                            {{ $opening->experience }}
                                        </span>
                                    @endif
                                    @if ($opening->salary)
                                        <span class="{{ $tag }}">
                                            <svg class="h-3.5 w-3.5 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                                            </svg>
                                            {{ $opening->salary }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('careers.show', $opening->slug) }}"
                           class="shrink-0 inline-flex items-center justify-center gap-2 rounded-full bg-navy px-6 py-2.5 text-sm font-semibold text-white shadow-md
                                  hover:bg-link group-hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                            Apply
                            <svg class="arrow-nudge h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                            </svg>
                        </a>
                    </div>
                @empty
                    <div data-reveal="fade" class="rounded-2xl bg-white border border-gray-100 p-10 text-center text-slate">
                        <p class="font-semibold text-navy">No open positions right now.</p>
                        <p class="mt-1 text-sm">Share your profile below and we'll reach out when a suitable role opens up.</p>
                        <a href="#apply" class="btn-primary mt-5 inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold text-white">
                            Share your profile
                            <svg class="arrow-nudge-down h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                            </svg>
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== What happens after you apply ===== --}}
        <div class="mt-20">
            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">Hiring process</span>
                <h2 class="text-xl sm:text-2xl font-bold text-navy leading-tight">
                    What happens after you <span class="text-cyan-ink">apply</span>
                </h2>
                <p class="max-w-2xl text-sm text-slate">Four simple steps from your application to your first day.</p>
            </div>

            @include('public.partials.steps-flow', ['steps' => config('careers.process')])
        </div>

        {{-- ===== Banner ===== --}}
        <div data-reveal="zoom" class="mt-16 flex flex-col items-start justify-between gap-6 rounded-3xl bg-gradient-to-br from-navy to-link px-7 py-9 sm:flex-row sm:items-center sm:px-12 sm:py-12">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white">Don't see a perfect role?</h2>
                <p class="mt-2 text-sm sm:text-base text-white/80">We always welcome exceptional talent. Share your profile with us.</p>
            </div>
            <a href="#apply" class="btn-glass shrink-0 px-7 py-3">
                Share your profile
                <svg class="arrow-nudge-down h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/>
                </svg>
            </a>
        </div>

        {{-- ===== General application ===== --}}
        <div class="mt-14 max-w-4xl mx-auto">
            <div data-reveal class="text-center flex flex-col items-center gap-3 mb-8">
                <span class="section-badge">
                    Apply to join our team
                </span>
            </div>

            {{-- 'opening' => null on purpose: the list above loops with a variable called $opening, which would otherwise leak into this form and turn it into a job form --}}
            @include('public.partials.application-form', ['action' => route('careers.apply.general'), 'opening' => null])
        </div>
    </div>
  </div>
</x-layouts.app>
