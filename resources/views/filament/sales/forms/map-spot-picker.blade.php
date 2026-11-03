@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore.self
        x-data="{
            state: $wire.$entangle(@js($statePath)),
            map: null,
            query: '',
            found: [],
            searching: false,
            note: '',
            parse(text) {
                const t = decodeURIComponent(String(text ?? '')).trim();
                const num = '(-?\\d{1,3}\\.\\d{3,})';
                for (const p of ['@' + num + ',' + num, '!3d' + num + '!4d' + num, '[?&](?:q|ll|query)=' + num + ',' + num, '^\\s*' + num + '\\s*[,; ]\\s*' + num + '\\s*$']) {
                    const m = new RegExp(p).exec(t);
                    if (m) return [Number(m[1]), Number(m[2])];
                }
                return null;
            },
            load(tag, attrs) {
                return new Promise((ok, fail) => {
                    const el = Object.assign(document.createElement(tag), attrs);
                    el.onload = ok;
                    el.onerror = fail;
                    document.head.appendChild(el);
                });
            },
            async init() {
                if (!window.L) {
                    if (!document.getElementById('ab-leaflet-css')) await this.load('link', { id: 'ab-leaflet-css', rel: 'stylesheet', href: @js(asset('vendor/leaflet/leaflet.css')) });
                    await this.load('script', { src: @js(asset('vendor/leaflet/leaflet.js')) });
                }

                const start = this.parse(this.state);
                this.map = L.map(this.$refs.map, { center: start ?? [26.9124, 75.7873], zoom: start ? 18 : 12, maxZoom: 19 });
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(this.map);

                this.map.on('moveend', () => {
                    if (this.quiet) return;
                    const c = this.map.getCenter();
                    const text = c.lat.toFixed(6) + ', ' + c.lng.toFixed(6);
                    if (text !== this.state) this.state = text;
                });

                // a pasted link or typed numbers move the map
                this.$watch('state', (value) => {
                    const p = this.parse(value);
                    if (!p || !this.map) return;
                    const c = this.map.getCenter();
                    if (Math.abs(c.lat - p[0]) > 0.000005 || Math.abs(c.lng - p[1]) > 0.000005) {
                        this.quiet = true;
                        this.map.setView(p, Math.max(this.map.getZoom(), 17));
                        setTimeout(() => (this.quiet = false), 300);
                    }
                });

                new ResizeObserver(() => this.map && this.map.invalidateSize()).observe(this.$refs.map);
                setTimeout(() => this.map.invalidateSize(), 350);
            },
            async search() {
                const q = this.query.trim();
                if (q.length < 3) { this.note = 'Type at least 3 letters.'; return; }
                this.searching = true;
                this.note = '';
                try {
                    const answer = await fetch('https://nominatim.openstreetmap.org/search?format=jsonv2&limit=6&countrycodes=in&q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
                    const rows = await answer.json();
                    this.found = rows.map((r) => ({ name: r.display_name, lat: Number(r.lat), lng: Number(r.lon) }));
                    if (!this.found.length) this.note = 'Nothing found. Try the area or the town, then move the map.';
                } catch (e) {
                    this.note = 'Could not search. Check the internet, or move the map by hand.';
                } finally {
                    this.searching = false;
                }
            },
            go(place) {
                this.found = [];
                this.map.setView([place.lat, place.lng], 18);
            },
        }"
        class="space-y-2"
    >
        <div class="flex gap-2">
            <input type="search" x-model="query" x-on:keydown.enter.prevent="search()" maxlength="150" placeholder="Search the shop, area or address"
                class="fi-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/20 dark:bg-white/5" />
            <button type="button" x-on:click="search()" x-bind:disabled="searching"
                class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-900 dark:bg-white/10 dark:text-white">Search</button>
        </div>

        <template x-if="found.length">
            <div class="max-h-48 divide-y divide-gray-200 overflow-y-auto rounded-lg border border-gray-200 text-sm dark:divide-white/10 dark:border-white/10">
                <template x-for="place in found" :key="place.lat + ',' + place.lng">
                    <button type="button" x-on:click="go(place)" class="block w-full px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-white/5" x-text="place.name"></button>
                </template>
            </div>
        </template>
        <p x-show="note" x-text="note" class="text-xs text-gray-500"></p>

        <div class="relative overflow-hidden rounded-xl border border-gray-300 dark:border-white/20" style="height: 360px;">
            <div x-ref="map" style="position:absolute; inset:0;" wire:ignore></div>
            <svg viewBox="0 0 24 24" aria-hidden="true"
                style="position:absolute; left:50%; top:50%; width:34px; height:34px; margin:-34px 0 0 -17px; z-index:500; pointer-events:none; color:rgb(var(--primary-600)); fill:white; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round;">
                <path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11z" /><circle cx="12" cy="10" r="2.5" />
            </svg>
        </div>

        <input type="text" x-model.debounce.500ms="state" autocomplete="off" spellcheck="false" maxlength="800"
            placeholder="26.9124, 75.7873 (or paste a Google Maps link)"
            class="fi-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/20 dark:bg-white/5" />
        <p class="text-xs text-gray-500">Move the map until the pin is on the exact door. The latitude and longitude below fill in by themselves.</p>
    </div>
</x-dynamic-component>
