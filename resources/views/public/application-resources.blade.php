@php
    $categories = \App\Models\ApplicationResource::CATEGORIES;
    $style = [
        'appnote' => ['chip' => 'text-link', 'bar' => 'from-navy to-link'],
        'guide' => ['chip' => 'text-link', 'bar' => 'from-link to-cyan'],
        'video' => ['chip' => 'text-link', 'bar' => 'from-navy to-cyan'],
        'brochure' => ['chip' => 'text-link', 'bar' => 'from-cyan to-link'],
    ];
    $icons = [
        'appnote' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'guide' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25',
        'video' => 'm15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z',
        'brochure' => 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3',
    ];
    $present = $resources->pluck('category')->unique();
@endphp

<x-layouts.app title="Application Resources" description="Download application notes, technical guides, brochures and videos to get the most from your laboratory instruments.">
  <div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[560px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.18),transparent_70%),radial-gradient(700px_380px_at_8%_14%,rgba(0,119,182,0.09),transparent_70%),radial-gradient(700px_380px_at_95%_20%,rgba(0,180,216,0.11),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14" x-data="{ active: 'all' }">

        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Application Resources</span>
        </div>

        <div class="text-center flex flex-col items-center gap-3 mb-8">
            <span class="section-badge">
                Resources
            </span>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy leading-tight">
                Application <span class="text-cyan-ink">Resources</span>
            </h1>
            <p class="max-w-2xl text-sm sm:text-base text-slate leading-relaxed">
                Technical guides, application notes and reference material to help you get the most from your laboratory instruments.
            </p>
        </div>

        {{-- Filter tabs --}}
        @if ($resources->count())
            <div class="flex flex-wrap justify-center gap-2 mb-8">
                <button @click="active = 'all'"
                    :class="active === 'all' ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-gray-200 hover:border-cyan/50'"
                    class="px-4 py-2 rounded-full text-sm font-medium border transition-all duration-200">
                    All Resources
                </button>
                @foreach ($categories as $key => $label)
                    @if ($present->contains($key))
                        <button @click="active = '{{ $key }}'"
                            :class="active === '{{ $key }}' ? 'bg-navy text-white border-navy' : 'bg-white text-navy border-gray-200 hover:border-cyan/50'"
                            class="px-4 py-2 rounded-full text-sm font-medium border transition-all duration-200">
                            {{ $label }}s
                        </button>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($resources as $res)
                @php
                    $cat = $res->category;
                    $s = $style[$cat] ?? $style['appnote'];
                    $pdfUrl = $res->pdf ? asset('storage/' . $res->pdf) : null;
                    $href = $pdfUrl ?: ($res->link_url ?: route('contact'));
                    $cta = $pdfUrl ? 'Download PDF' : ($res->link_url ? 'Open' : 'Request');
                @endphp

                <div x-show="active === 'all' || active === '{{ $cat }}'"
                     class="group flex flex-col overflow-hidden rounded-2xl bg-white border border-gray-100 shadow-sm
                            hover:shadow-xl hover:border-cyan/40 hover:-translate-y-1 transition-all duration-300">
                    <div class="h-1.5 bg-gradient-to-r {{ $s['bar'] }}"></div>

                    <div class="relative aspect-[16/10] w-full overflow-hidden bg-gradient-to-br from-ice to-cyan/10 flex items-center justify-center">
                        @if ($res->cover_image)
                            <img src="{{ asset('storage/' . $res->cover_image) }}" alt="{{ $res->title }}"
                                 class="img-load h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($res->cover_image) !!} onload="this.classList.add('is-loaded')">
                        @else
                            <div class="h-16 w-16 rounded-2xl bg-white shadow flex items-center justify-center">
                                <svg class="h-8 w-8 text-link" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$cat] ?? $icons['appnote'] }}"/>
                                </svg>
                            </div>
                        @endif
                        <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide bg-white/95 shadow-sm {{ $s['chip'] }}">
                            {{ $categories[$cat] ?? $cat }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="text-base font-bold text-navy leading-snug line-clamp-2 min-h-[2.75rem] group-hover:text-link transition-colors">
                            {{ $res->title }}
                        </h3>
                        @if ($res->description)
                            <p class="mt-2 text-sm text-slate leading-relaxed line-clamp-3">{{ $res->description }}</p>
                        @endif

                        <div class="mt-auto pt-4 flex items-center justify-between gap-3 border-t border-gray-100">
                            <span class="text-xs text-slate line-clamp-1">{{ $res->source }}</span>
                            <a href="{{ $href }}" @if ($pdfUrl || $res->link_url) target="_blank" rel="noopener" @endif
                               class="shrink-0 inline-flex items-center gap-1.5 rounded-full bg-navy px-4 py-2 text-xs font-semibold text-white hover:bg-link transition-colors btn-primary">
                                {{ $cta }}
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    @if ($pdfUrl)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                                    @endif
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($resources->isEmpty())
            <div class="rounded-2xl bg-white border border-gray-100 text-center py-16">
                <p class="text-lg font-semibold text-navy">Resources coming soon</p>
                <p class="mt-2 text-sm text-slate">Need something specific? Our team can share it directly.</p>
            </div>
        @endif

        {{-- Help banner --}}
        <div class="mt-14 rounded-3xl bg-gradient-to-br from-ice to-cyan/10 border border-cyan/20 px-6 py-8 sm:px-10
                    flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
            <div class="flex-1">
                <h2 class="text-lg sm:text-xl font-bold text-navy">Can't find the resource you need?</h2>
                <p class="mt-1 text-sm text-slate">Our technical team can share application-specific guides, SOPs and instrument selection advice.</p>
            </div>
            <a href="{{ route('contact') }}"
               class="shrink-0 inline-flex items-center gap-2 rounded-full bg-navy px-6 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                Ask Our Team
            </a>
        </div>
    </div>
  </div>
</x-layouts.app>
