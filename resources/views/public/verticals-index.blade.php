<x-layouts.app title="Explore Our Scientific Verticals">
  <div class="relative overflow-hidden">
    {{-- Soft blue tint fading into white --}}
    <div class="pointer-events-none absolute inset-x-0 top-0 h-[620px]
                bg-[radial-gradient(900px_420px_at_50%_-8%,rgba(0,180,216,0.22),transparent_70%),radial-gradient(700px_380px_at_8%_12%,rgba(0,119,182,0.10),transparent_70%),radial-gradient(700px_380px_at_95%_18%,rgba(0,180,216,0.12),transparent_70%),linear-gradient(to_bottom,rgba(244,249,251,1),rgba(255,255,255,0))]"></div>

    <div class="relative w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">Verticals</span>
        </div>

        {{-- Header --}}
        <div class="text-center flex flex-col items-center gap-3 mb-10">
            <span class="section-badge">
                Verticals
            </span>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy leading-tight">
                Explore Our <span class="text-cyan">Scientific Verticals</span>
            </h1>

            <form action="{{ route('search') }}" method="GET"
                  x-data="ghostSearch()" x-init="startGhost()"
                  class="relative w-full max-w-xl mt-2">
                <input type="text" name="q" x-ref="input"
                    :placeholder="ghost"
                    @focus="stopGhost()" @blur="startGhost()"
                    class="h-12 w-full rounded-full border-2 border-cyan/40 bg-white pl-12 pr-28
                           text-base text-navy placeholder:text-slate/60 shadow-sm
                           outline-none focus:border-cyan focus:ring-4 focus:ring-cyan/15
                           transition-all duration-200">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>
                </svg>
                <button type="submit"
                    class="absolute right-1.5 top-1/2 -translate-y-1/2 h-9 rounded-full bg-navy px-5 text-sm font-semibold text-white
                           hover:bg-link transition-colors btn-primary">
                    Search
                </button>
            </form>

            <p class="max-w-2xl text-sm sm:text-base text-slate leading-relaxed">
                From research to production, discover how our solutions support every lab need.
            </p>
        </div>

        {{-- Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach ($verticals as $vertical)
                <a href="{{ route('vertical.show', $vertical->slug) }}"
                    class="group flex flex-col overflow-hidden rounded-2xl bg-white
                           border border-gray-100 shadow-sm
                           hover:shadow-xl hover:border-cyan/40 hover:-translate-y-1
                           transition-all duration-300">

                    <div class="relative h-36 overflow-hidden bg-gradient-to-br from-ice to-cyan/10">
                        @if ($vertical->image)
                            <img src="{{ asset('storage/' . $vertical->image) }}" alt="{{ $vertical->name }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @else
                            <div class="flex h-full w-full items-center justify-center">
                                <div class="h-14 w-14 rounded-full bg-white shadow flex items-center justify-center">
                                    <svg class="h-7 w-7 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                                    </svg>
                                </div>
                            </div>
                        @endif
                        <div class="absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>

                    <div class="flex-1 p-5">
                        <h3 class="text-base font-bold text-navy leading-snug group-hover:text-link transition-colors line-clamp-2 min-h-[2.75rem]">
                            {{ $vertical->name }}
                        </h3>
                        @if ($vertical->description)
                            <p class="mt-2 text-sm text-slate leading-relaxed line-clamp-3">{{ $vertical->description }}</p>
                        @endif
                        <span class="mt-3 inline-flex items-center rounded-full bg-cyan/10 px-3 py-1 text-[11px] font-semibold text-link">
                            {{ $vertical->categories_count }} {{ \Illuminate\Support\Str::plural('category', $vertical->categories_count) }}
                        </span>
                    </div>

                    <div class="px-5 py-2.5 bg-navy text-center text-xs font-semibold text-white
                                group-hover:bg-link transition-colors duration-300">
                        Know More
                        <svg class="inline w-3.5 h-3.5 ml-1 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($verticals->isEmpty())
            <div class="mt-6 text-center py-16 text-slate">
                <p class="text-lg">No scientific verticals available yet.</p>
            </div>
        @endif

        {{-- Help banner --}}
        <div class="mt-14 rounded-3xl bg-gradient-to-br from-ice to-cyan/10 border border-cyan/20 px-6 py-8 sm:px-10
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
