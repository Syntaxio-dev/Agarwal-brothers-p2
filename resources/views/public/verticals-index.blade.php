<x-layouts.app title="Scientific Verticals" description="Explore Agarwal Brothers' scientific verticals: analytical, life sciences, pharma, materials, environmental and more, with instruments from leading global brands.">
  <div class="relative overflow-hidden">
    {{-- Soft blue tint fading into white --}}
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[620px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.22),transparent_70%),radial-gradient(700px_380px_at_8%_12%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_95%_18%,rgba(0,180,216,0.12),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-8 sm:py-14">

        @include('public.partials.breadcrumb', ['items' => [['Home', '/'], ['Verticals', null]]])

        {{-- Header --}}
        <div data-reveal class="text-center flex flex-col items-center gap-3 mb-6 sm:mb-10">
            <span class="section-badge">
                Verticals
            </span>
            <h1 class="text-[1.65rem] sm:text-3xl lg:text-4xl font-bold text-navy leading-tight">
                Explore Our <span class="text-cyan-ink">Scientific Verticals</span>
            </h1>

            <form action="{{ route('search') }}" method="GET" role="search"
                  x-data="ghostSearch()" x-init="startGhost()" @click.outside="close()"
                  class="relative mt-2 flex w-full max-w-xl items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm transition focus-within:border-cyan focus-within:ring-2 focus-within:ring-cyan/20">
                <svg class="ml-3 h-5 w-5 shrink-0 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
                </svg>
                <input type="text" name="q" x-ref="input" maxlength="80" autocomplete="off" aria-label="Search"
                       :placeholder="ghost" @focus="stopGhost(); if (rows.length) open = true" @blur="startGhost()" @input="suggest($event.target.value)" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter="choose($event)" @keydown.escape="close()"
                       class="min-w-0 flex-1 bg-transparent px-1 py-2.5 text-base text-navy placeholder:text-slate outline-none">
                <button type="submit" class="btn-primary shrink-0 rounded-lg px-4 py-2.5 text-sm font-semibold text-white sm:px-6">Search</button>
                <x-search-suggest />
</form>

            <p class="max-w-2xl text-sm sm:text-base text-slate leading-relaxed">
                From research to production, discover how our solutions support every lab need.
            </p>
        </div>

        <div x-data="{ q: '', names: @js($verticals->map(fn ($v) => mb_strtolower($v->name))->values()), hit(n) { const t = this.q.trim().toLowerCase(); return !t || n.includes(t); }, get none() { return !this.names.some((n) => this.hit(n)); } }">
        {{-- Phones only: find a vertical by typing (only when there are enough of them to scroll through) --}}
        @if ($verticals->count() >= 6)
            <div class="relative mb-4 sm:hidden">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
                <input type="search" x-model="q" placeholder="Find a vertical" aria-label="Find a vertical" autocomplete="off"
                       class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm text-navy outline-none placeholder:text-slate focus:border-cyan focus:ring-2 focus:ring-cyan/20">
            </div>
        @endif
        <p x-cloak x-show="none" class="py-8 text-center text-sm text-slate sm:hidden">No vertical matches that.</p>

        {{-- Cards: a compact row each on phones (picture on the left), the full card from 640px --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5">
            @foreach ($verticals as $vertical)
                <a data-reveal data-vertical href="{{ route('vertical.show', $vertical->slug) }}"
                    x-show="hit(@js(mb_strtolower($vertical->name)))"
                    class="group relative flex flex-col overflow-hidden rounded-2xl bg-white max-sm:flex-row
                           border border-gray-100 shadow-sm
                           hover:shadow-xl hover:border-cyan/40 hover:-translate-y-1
                           transition-all duration-300">

                    <div class="relative h-36 overflow-hidden bg-gradient-to-br from-ice to-cyan/10 max-sm:h-auto max-sm:w-24 max-sm:shrink-0">
                        @if ($vertical->image)
                            <img src="{{ asset('storage/' . $vertical->image) }}" alt="{{ $vertical->name }}"
                                class="img-load h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($vertical->image) !!} onload="this.classList.add('is-loaded')">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <div class="h-14 w-14 rounded-full bg-white shadow flex items-center justify-center">
                                    <svg class="h-7 w-7 text-cyan-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                                    </svg>
                                </div>
                            </div>
                        @endif
                        <div class="absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>

                    <div class="flex-1 p-5 max-sm:min-w-0 max-sm:p-3.5 max-sm:pr-10">
                        <h3 class="text-base font-bold text-navy leading-snug group-hover:text-link transition-colors line-clamp-2 min-h-[2.75rem] max-sm:min-h-0 max-sm:text-[15px]">
                            {{ $vertical->name }}
                        </h3>
                        @if ($vertical->description)
                            <p class="mt-2 text-sm text-slate leading-relaxed line-clamp-3 max-sm:mt-1 max-sm:line-clamp-2 max-sm:text-xs">{{ $vertical->description }}</p>
                        @endif
                        <span class="mt-3 inline-flex items-center rounded-full bg-cyan/10 px-3 py-1 text-[11px] font-semibold text-link max-sm:mt-2">
                            {{ $vertical->categories_count }} {{ \Illuminate\Support\Str::plural('category', $vertical->categories_count) }}
                        </span>
                    </div>

                    {{-- phones: a plain arrow replaces the navy 'Know More' bar --}}
                    <span class="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-navy text-white sm:hidden" aria-hidden="true">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                    </span>

                    <div class="px-5 py-2.5 bg-navy text-center text-xs font-semibold text-white max-sm:hidden
                                group-hover:bg-link transition-colors duration-300">
                        Know More
                        <svg class="inline w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
        </div>

        @if ($verticals->isEmpty())
            <div data-reveal="fade" class="mt-6 text-center py-16 text-slate">
                <p class="text-lg">No scientific verticals available yet.</p>
            </div>
        @endif

        {{-- Help banner --}}
        <div data-reveal class="mt-10 sm:mt-14 rounded-3xl bg-gradient-to-br from-ice to-cyan/10 border border-cyan/20 px-5 py-6 sm:px-10 sm:py-8
                    flex flex-col sm:flex-row items-center gap-5 text-center sm:text-left">
            <div class="flex-1">
                <h2 class="text-lg sm:text-xl font-bold text-navy">Not sure which vertical fits your lab?</h2>
                <p class="mt-1 text-sm text-slate">Tell us about your application and our team will recommend the right instruments.</p>
            </div>
            <a href="/contact-us"
               class="shrink-0 inline-flex items-center gap-2 rounded-full bg-navy px-6 py-2.5 text-sm font-semibold text-white shadow-md
                      hover:bg-link hover:scale-105 transition-all duration-300 btn-primary">
                Talk to an Expert
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                </svg>
            </a>
        </div>
    </div>
  </div>
</x-layouts.app>
