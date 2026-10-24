<x-layouts.app :title="$opening->title" :description="$opening->summary ?: $opening->title . ' at Agarwal Brothers, ' . $opening->location . '. Apply online.'">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[480px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.18),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-8 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [['Home', '/'], ['Careers', route('careers')], [$opening->title, null]]])

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10 items-start">

            {{-- Role details --}}
            <div data-reveal="left" class="lg:col-span-2 lg:sticky lg:top-6">
                <span class="section-badge">
                    Now hiring
                </span>
                <h1 class="mt-4 text-[1.65rem] sm:text-3xl font-bold text-navy leading-tight">{{ $opening->title }}</h1>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full bg-white border border-gray-100 px-3 py-1 text-xs font-semibold text-navy">{{ $opening->location }}</span>
                    <span class="rounded-full bg-white border border-gray-100 px-3 py-1 text-xs font-semibold text-navy uppercase">{{ $opening->employment_type }}</span>
                    @if ($opening->department)
                        <span class="rounded-full bg-cyan/10 px-3 py-1 text-xs font-semibold text-link">{{ $opening->department }}</span>
                    @endif
                    @if ($opening->experience)
                        <span class="rounded-full bg-white border border-gray-100 px-3 py-1 text-xs font-semibold text-navy">{{ $opening->experience }}</span>
                    @endif
                    @if ($opening->salary)
                        <span class="rounded-full bg-white border border-gray-100 px-3 py-1 text-xs font-semibold text-navy">{{ $opening->salary }}</span>
                    @endif
                </div>

                @if ($opening->summary)
                    <p class="mt-5 text-[15px] text-slate leading-relaxed sm:text-base">{{ $opening->summary }}</p>
                @endif

                {{-- phones: the form is below the description, so a button takes the visitor straight to it --}}
                <a href="#apply" class="btn-primary mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full px-7 py-3 text-sm font-semibold text-white md:hidden">
                    Apply for this role
                    <svg class="arrow-nudge-down h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/></svg>
                </a>

                @if ($opening->description)
                    <div class="mt-5 text-sm text-slate leading-relaxed
                                [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-1
                                [&_strong]:text-navy [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-navy [&_h3]:font-bold [&_h3]:text-navy">
                        {!! \Illuminate\Support\Str::sanitizeHtml((string) $opening->description) !!}
                    </div>
                @endif
            </div>

            {{-- Application form --}}
            <div data-reveal="right" data-reveal-delay="100" class="lg:col-span-3">
                <h2 class="text-lg font-bold text-navy mb-4">Apply for this role</h2>
                @include('public.partials.application-form', [
                    'action' => route('careers.apply', $opening->slug),
                    'opening' => $opening,
                ])
            </div>
        </div>
    </div>
  </div>
</x-layouts.app>
