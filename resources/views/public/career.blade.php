<x-layouts.app :title="$opening->title" :description="$opening->summary ?: $opening->title . ' at Agarwal Brothers, ' . $opening->location . '. Apply online.'">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[480px]
                bg-[radial-gradient(900px_380px_at_50%_-8%,rgba(0,180,216,0.18),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        <div class="text-sm text-slate mb-8">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('careers') }}" class="hover:text-link transition">Careers</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $opening->title }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-10 items-start">

            {{-- Role details --}}
            <div class="lg:col-span-2 lg:sticky lg:top-6">
                <span class="section-badge">
                    Now hiring
                </span>
                <h1 class="mt-4 text-2xl sm:text-3xl font-bold text-navy leading-tight">{{ $opening->title }}</h1>

                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full bg-white border border-gray-100 px-3 py-1 text-xs font-semibold text-navy">{{ $opening->location }}</span>
                    <span class="rounded-full bg-white border border-gray-100 px-3 py-1 text-xs font-semibold text-navy uppercase">{{ $opening->employment_type }}</span>
                    @if ($opening->department)
                        <span class="rounded-full bg-cyan/10 px-3 py-1 text-xs font-semibold text-link">{{ $opening->department }}</span>
                    @endif
                </div>

                @if ($opening->summary)
                    <p class="mt-5 text-slate leading-relaxed">{{ $opening->summary }}</p>
                @endif

                @if ($opening->description)
                    <div class="mt-5 text-sm text-slate leading-relaxed
                                [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:mb-3 [&_ol]:list-decimal [&_ol]:pl-5 [&_li]:mb-1
                                [&_strong]:text-navy [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-navy [&_h3]:font-bold [&_h3]:text-navy">
                        {!! $opening->description !!}
                    </div>
                @endif
            </div>

            {{-- Application form --}}
            <div class="lg:col-span-3">
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
