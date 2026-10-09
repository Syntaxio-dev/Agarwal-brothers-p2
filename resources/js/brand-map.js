import JSVectorMap from 'jsvectormap';
import 'jsvectormap/dist/jsvectormap.css';

const CLOSE_DELAY = 250;

window.brandMap = function (countries) {
    return {
        countries,
        active: null,
        pos: { x: 0, y: 0, below: false },
        closeTimer: null,
        map: null,

        async init() {
            // The bundled world map file expects a global jsVectorMap.
            window.jsVectorMap = JSVectorMap;
            await import('jsvectormap/dist/maps/world.js');

            const el = this.$refs.map;
            this.map = new JSVectorMap({
                selector: el,
                map: 'world',
                zoomButtons: false,
                zoomOnScroll: false,
                showTooltip: false,
                regionStyle: {
                    initial: { fill: '#C3D4E2', stroke: '#F4F9FB', strokeWidth: 0.5 },
                    hover: { fill: '#BBD0E0' },
                },
                markers: this.countries.map(c => ({ name: c.name, coords: [c.lat, c.lng] })),
                markerStyle: {
                    initial: { fill: '#0B2545', stroke: '#FFFFFF', strokeWidth: 2.5, r: 8 },
                    hover: { fill: '#00B4D8', stroke: '#FFFFFF', strokeWidth: 2.5, r: 10 },
                },
            });

            el.addEventListener('pointerover', e => {
                if (e.pointerType !== 'mouse') return;
                const m = e.target.closest('.jvm-marker');
                if (m) this.open(Number(m.getAttribute('data-index')), m);
            });
            el.addEventListener('pointerout', e => {
                if (e.pointerType !== 'mouse') return;
                if (e.target.closest('.jvm-marker')) this.scheduleClose();
            });
            el.addEventListener('click', e => {
                const m = e.target.closest('.jvm-marker');
                if (!m) return this.close();
                const i = Number(m.getAttribute('data-index'));
                this.active === i && e.pointerType !== 'mouse' ? this.close() : this.open(i, m);
            });
        },

        open(index, markerEl) {
            this.cancelClose();
            const wrap = this.$refs.wrap.getBoundingClientRect();
            const m = markerEl.getBoundingClientRect();
            const x = m.left + m.width / 2 - wrap.left;
            const y = m.top - wrap.top;
            this.pos = {
                x: Math.min(Math.max(x, 130), wrap.width - 130),
                y: y < 190 ? m.bottom - wrap.top : y,
                below: y < 190,
            };
            this.active = index;
        },

        scheduleClose() {
            this.cancelClose();
            this.closeTimer = setTimeout(() => (this.active = null), CLOSE_DELAY);
        },

        cancelClose() {
            clearTimeout(this.closeTimer);
        },

        close() {
            this.cancelClose();
            this.active = null;
        },
    };
};
