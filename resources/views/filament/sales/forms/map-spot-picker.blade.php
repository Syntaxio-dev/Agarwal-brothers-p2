@php
    $statePath = $getStatePath();
    $mapsKey = (string) config('services.google_maps.key');
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        wire:ignore.self
        x-data="{
            state: $wire.$entangle(@js($statePath)),
            key: @js($mapsKey),
            map: null,
            query: '',
            found: [],
            searching: false,
            satellite: false,
            note: '',
            parse(text) {
                const t = decodeURIComponent(String(text ?? '')).trim();
                const num = '(-?\\d{1,3}\\.\\d{3,})';
                for (const p of ['@' + num + ',' + num, '!3d' + num + '!4d' + num, '[?&](?:q|ll|query)=' + num + ',' + num, '^\\s*' + num + '\\s*[,; ]\\s*' + num + '\\s*$']) {
                    const m = new RegExp(p).exec(t);
                    if (m) return { lat: Number(m[1]), lng: Number(m[2]) };
                }
                return null;
            },
            loadGoogle() {
                if (window.google && window.google.maps && window.google.maps.Map) return Promise.resolve();
                if (!window.__abAdminMaps) {
                    window.__abAdminMaps = new Promise((ok, fail) => {
                        window.__abAdminMapsReady = ok;
                        window.gm_authFailure = () => fail(new Error('key'));
                        const s = document.createElement('script');
                        s.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(this.key) + '&callback=__abAdminMapsReady&v=weekly';
                        s.async = true;
                        s.onerror = () => fail(new Error('net'));
                        document.head.appendChild(s);
                    });
                }
                return window.__abAdminMaps;
            },
            async init() {
                if (!this.key) { this.note = 'The Google Maps key is not set on the server (GOOGLE_MAPS_KEY). You can still type or paste the numbers below.'; return; }
                try {
                    await this.loadGoogle();
                } catch (e) {
                    this.note = e.message === 'key' ? 'Google refused the map key. Check the key and its restrictions. You can still paste the numbers below.' : 'The map could not load. Check the internet. You can still paste the numbers below.';
                    return;
                }

                const start = this.parse(this.state);
                this.map = new google.maps.Map(this.$refs.map, {
                    center: start ?? { lat: 26.9124, lng: 75.7873 },
                    zoom: start ? 18 : 12,
                    mapTypeControl: false, streetViewControl: false, fullscreenControl: false, clickableIcons: false, gestureHandling: 'greedy',
                });

                this.map.addListener('idle', () => {
                    if (this.quiet) return;
                    const c = this.map.getCenter();
                    const text = c.lat().toFixed(6) + ', ' + c.lng().toFixed(6);
                    if (text !== this.state) this.state = text;
                });

                // a pasted link or typed numbers move the map
                this.$watch('state', (value) => {
                    const p = this.parse(value);
                    if (!p || !this.map) return;
                    const c = this.map.getCenter();
                    if (Math.abs(c.lat() - p.lat) > 0.000005 || Math.abs(c.lng() - p.lng) > 0.000005) {
                        this.quiet = true;
                        this.map.setCenter(p);
                        if (this.map.getZoom() < 17) this.map.setZoom(17);
                        setTimeout(() => (this.quiet = false), 400);
                    }
                });
            },
            async search() {
                const q = this.query.trim();
                if (q.length < 3) { this.note = 'Type at least 3 letters.'; return; }
                if (!this.key) { this.note = 'The Google Maps key is not set, so search is off.'; return; }
                this.searching = true;
                this.note = '';
                try {
                    const answer = await fetch('https://places.googleapis.com/v1/places:searchText', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Goog-Api-Key': this.key, 'X-Goog-FieldMask': 'places.displayName,places.formattedAddress,places.location' },
                        body: JSON.stringify({ textQuery: q, regionCode: 'IN', maxResultCount: 6 }),
                    });
                    const data = await answer.json();
                    this.found = (data.places || []).map((r) => ({ name: [r.displayName && r.displayName.text, r.formattedAddress].filter(Boolean).join(', '), lat: r.location.latitude, lng: r.location.longitude }));
                    if (!this.found.length) this.note = answer.ok ? 'Nothing found. Try the area or the town, then move the map.' : 'Search is not allowed for this key (enable Places API (New) for it).';
                } catch (e) {
                    this.note = 'Could not search. Check the internet, or move the map by hand.';
                } finally {
                    this.searching = false;
                }
            },
            go(place) {
                this.found = [];
                this.map.setCenter({ lat: place.lat, lng: place.lng });
                this.map.setZoom(18);
            },
            toggleSatellite() {
                this.satellite = !this.satellite;
                if (this.map) this.map.setMapTypeId(this.satellite ? 'hybrid' : 'roadmap');
            },
        }"
        class="space-y-2"
    >
        <div class="flex gap-2">
            <input type="search" x-model="query" x-on:keydown.enter.prevent="search()" maxlength="150" placeholder="Search the shop, college or address"
                class="fi-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/20 dark:bg-white/5" />
            <button type="button" x-on:click="search()" x-bind:disabled="searching"
                class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-900 dark:bg-white/10 dark:text-white">Search</button>
            <button type="button" x-on:click="toggleSatellite()" x-text="satellite ? 'Map' : 'Satellite'"
                class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-900 dark:bg-white/10 dark:text-white"></button>
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
                style="position:absolute; left:50%; top:50%; width:34px; height:34px; margin:-34px 0 0 -17px; z-index:5; pointer-events:none; color:rgb(var(--primary-600)); fill:white; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round;">
                <path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11z" /><circle cx="12" cy="10" r="2.5" />
            </svg>
        </div>

        <input type="text" x-model.debounce.500ms="state" autocomplete="off" spellcheck="false" maxlength="800"
            placeholder="26.9124, 75.7873 (or paste a Google Maps link)"
            class="fi-input block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-white/20 dark:bg-white/5" />
        <p class="text-xs text-gray-500">Move the map until the pin is on the exact door. The latitude and longitude below fill in by themselves.</p>
    </div>
</x-dynamic-component>
