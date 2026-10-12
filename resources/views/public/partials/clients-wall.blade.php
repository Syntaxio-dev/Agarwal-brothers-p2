{{-- Our clients: sector chips, logo grid (left) and a live featured-client panel (right). Needs $clientWall (all active
     clients, top clients first). Chips filter the grid in the browser; clicking a logo fills the panel. --}}
@php
    $industries = config('industries');
    $industryCounts = $clientWall->groupBy('industry')->map->count();
    $sectors = collect($industries)->filter(fn ($i, $key) => $industryCounts->get($key, 0) > 0);

    $clientData = $clientWall->map(fn ($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'logo' => $c->logo ? asset('storage/' . $c->logo) : null,
        'initials' => strtoupper(mb_substr($c->name, 0, 2)),
        'sector' => $c->industry,
        'sectorLabel' => $industries[$c->industry]['label'] ?? null,
        'city' => $c->city,
        'since' => $c->since_year,
        'supplied' => $c->supplied,
        'note' => $c->testimonial,
    ])->values();

    $defaultId = optional($clientWall->firstWhere('is_featured', true) ?? $clientWall->first())->id;
@endphp

@if ($clientWall->count())
<section class="py-12 sm:py-16 bg-ice" id="clients"
         x-data="{
             clients: @js($clientData),
             sector: 'all',
             featuredId: {{ (int) $defaultId }},
             fade: false,
             get current() { return this.clients.find((c) => c.id === this.featuredId) || this.clients[0]; },
             inSector(s) { return this.sector === 'all' || this.sector === s; },
             pick(id) {
                 if (id === this.featuredId) return;
                 this.fade = true;
                 setTimeout(() => { this.featuredId = id; this.fade = false; }, 140);
             },
             setSector(key) {
                 this.sector = key;
                 const first = this.clients.find((c) => this.inSector(c.sector));
                 if (first) this.pick(first.id);
             },
         }">
    <div class="w-[98%] mx-auto md:px-5 lg:px-20">

        <div data-reveal class="mb-8 flex flex-col items-center gap-3 text-center">
            <span class="section-badge">Our Clients</span>
            <h2 class="text-xl font-semibold leading-tight text-navy sm:text-2xl">
                Trusted by <span class="text-cyan-ink">Industry Leaders</span>
            </h2>
            <p class="max-w-2xl text-sm text-slate">
                Laboratories and organisations that rely on us for instruments, service and support.
            </p>
        </div>

        {{-- Sector chips --}}
        @if ($sectors->count() > 1)
            <div data-reveal class="mb-6 flex flex-wrap justify-center gap-2" role="group" aria-label="Filter clients by sector">
                <button type="button" class="chip" @click="setSector('all')" :class="sector === 'all' ? 'chip-active' : ''" :aria-pressed="sector === 'all'">All sectors</button>
                @foreach ($sectors as $key => $industry)
                    <button type="button" class="chip" @click="setSector('{{ $key }}')" :class="sector === '{{ $key }}' ? 'chip-active' : ''" :aria-pressed="sector === '{{ $key }}'">{{ $industry['label'] }}</button>
                @endforeach
            </div>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-[1fr_1.15fr] lg:gap-8">

            {{-- Logo grid --}}
            <div data-reveal="left" class="grid grid-cols-3 gap-2.5">
                @foreach ($clientWall as $client)
                    @php $sectorKey = $client->industry ?: '_none'; @endphp
                    <button type="button"
                            x-show="inSector('{{ $sectorKey }}')"
                            x-transition.opacity.duration.250ms
                            @click="pick({{ $client->id }})"
                            aria-label="Show details for {{ $client->name }}"
                            :aria-pressed="featuredId === {{ $client->id }}"
                            :class="featuredId === {{ $client->id }} ? 'border-cyan shadow-md' : 'border-gray-200 hover:border-cyan/50'"
                            style="--pop-delay: {{ min($loop->index, 14) * 45 }}ms"
                            class="pop-tile group flex h-20 items-center justify-center rounded-xl border-2 bg-white p-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg active:scale-95 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan sm:h-24">
                        @if ($client->logo)
                            <img src="{{ asset('storage/' . $client->logo) }}" alt="" :class="featuredId === {{ $client->id }} ? '' : 'grayscale opacity-70'" class="img-load max-h-full max-w-full object-contain transition duration-300 group-hover:scale-105 group-hover:opacity-100 group-hover:grayscale-0"
                                 loading="lazy" decoding="async" {!! \App\Support\Img::attrs($client->logo) !!} onload="this.classList.add('is-loaded')">
                        @else
                            <span :class="featuredId === {{ $client->id }} ? 'text-navy' : 'text-navy/50'" class="text-xl font-black transition-all duration-300 group-hover:scale-110 group-hover:text-navy">{{ strtoupper(mb_substr($client->name, 0, 2)) }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- Featured client panel --}}
            <div data-reveal="right" class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-shadow duration-300 hover:shadow-lg sm:p-8 lg:sticky lg:top-28">
                <span class="absolute inset-x-0 top-0 h-[3px] bg-cyan" aria-hidden="true"></span>
                <div aria-live="polite" class="transition-all duration-200" :class="fade ? 'translate-y-1 opacity-0' : ''">
                    <div class="flex h-24 w-40 items-center justify-center rounded-xl border border-gray-100 bg-white p-3 transition-all duration-300" :class="fade ? 'scale-90' : 'scale-100'">
                        <img x-show="current.logo" :src="current.logo" :alt="current.name" class="max-h-full max-w-full object-contain">
                        <span x-show="!current.logo" class="text-3xl font-black text-navy/70" x-text="current.initials"></span>
                    </div>
                    <h3 class="mt-5 text-2xl font-bold leading-tight text-navy" x-text="current.name"></h3>
                    <p class="mt-2 font-mono text-[11px] uppercase tracking-widest text-cyan-ink"
                       x-show="current.sectorLabel || current.since"
                       x-text="[current.sectorLabel, current.since ? 'Since ' + current.since : null, current.city].filter(Boolean).join(' · ')"></p>
                    <p x-show="current.supplied" class="mt-4 text-sm text-navy"><span class="font-semibold">We supply:</span> <span x-text="current.supplied"></span></p>
                    <p x-show="current.note" class="mt-3 text-base leading-relaxed text-slate" x-text="current.note"></p>
                </div>
                <a href="{{ route('contact') }}#contact-form"
                   class="btn-primary mt-6 inline-flex items-center gap-2 rounded-full bg-navy px-7 py-3 text-sm font-semibold text-white transition-all duration-300">
                    Talk to our team
                    <svg class="arrow-nudge h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endif
