import '@fontsource/ibm-plex-sans/latin-400.css';
import '@fontsource/ibm-plex-sans/latin-500.css';
import '@fontsource/ibm-plex-sans/latin-600.css';
import '@fontsource/ibm-plex-mono/latin-400.css';

import Alpine from 'alpinejs';
import './brand-map';
import './review-carousel';
import './form-guard';
import './catalogue-filter';
import './privacy';
import './reveal';
import './page-fade';

// Ghost search — animated placeholder that cycles through search suggestions
// Recent searches: kept only in this visitor's browser (30 days, removed by "Clear saved data" on the privacy page)
window.abSearches = {
    key: 'ab_recent_searches',
    read() {
        return abStore.read(this.key, 30);
    },
    add(q) {
        q = String(q || '').trim().slice(0, 80);
        if (q.length < 2) return;
        const list = this.read().filter((x) => x.toLowerCase() !== q.toLowerCase());
        list.unshift(q);
        abStore.write(this.key, list.slice(0, 6));
    },
    clear() {
        abStore.write(this.key, []);
    },
};

window.ghostSearch = function () {
    return {
        ghost: '',
        hints: [
            'Search "Rotary Evaporator"',
            'Search "HPLC System"',
            'Search "Spectrophotometer"',
            'Search "Lab Chemicals"',
            'Search "Analytical Balance"',
            'Search "pH Meter"',
            'Search "Centrifuge"',
            'Search "Microscope"',
            'Search "Water Purification"',
            'Search "Fume Hood"',
        ],
        hintIndex: 0,
        charIndex: 0,
        typing: true,
        timer: null,
        running: false,

        // Live suggestions
        rows: [],
        open: false,
        loading: false,
        active: -1,
        lastQuery: '',
        corrected: null,
        recents: [],
        showRecent: false,
        suggestTimer: null,
        controller: null,

        suggest(value) {
            const q = value.trim();
            this.active = -1;
            clearTimeout(this.suggestTimer);
            if (q.length < 2) {
                this.rows = [];
                this.corrected = null;
                this.lastQuery = q;
                this.recents = abSearches.read();
                this.showRecent = this.recents.length > 0 && q.length === 0;
                this.open = this.showRecent;
                return;
            }
            this.showRecent = false;
            this.suggestTimer = setTimeout(() => this.fetchSuggestions(q), 180);
        },

        async fetchSuggestions(q) {
            if (this.controller) this.controller.abort();
            this.controller = new AbortController();
            this.loading = true;
            try {
                const res = await fetch('/search/suggest?q=' + encodeURIComponent(q), {
                    headers: { Accept: 'application/json' },
                    signal: this.controller.signal,
                });
                if (!res.ok) throw new Error('bad response');
                const data = await res.json();
                this.rows = data.rows || [];
                this.corrected = data.corrected || null;
                this.lastQuery = q;
                this.open = true;
            } catch (e) {
                if (e.name !== 'AbortError') {
                    this.rows = [];
                    this.open = false;
                }
            } finally {
                this.loading = false;
            }
        },

        // Split a label around the typed text so the match can be highlighted.
        parts(label) {
            const q = this.corrected || this.lastQuery;
            const at = q ? label.toLowerCase().indexOf(q.toLowerCase()) : -1;
            if (at < 0) return [{ t: label, hit: false }];
            return [
                { t: label.slice(0, at), hit: false },
                { t: label.slice(at, at + q.length), hit: true },
                { t: label.slice(at + q.length), hit: false },
            ].filter((x) => x.t !== '');
        },

        showHeading(i) {
            return i === 0 || this.rows[i].type !== this.rows[i - 1].type;
        },

        move(step) {
            if (!this.open || !this.rows.length) return;
            this.active = (this.active + step + this.rows.length + 1) % (this.rows.length + 1);
            if (this.active === this.rows.length) this.active = -1;
        },

        choose(event) {
            if (this.open && this.active >= 0 && this.rows[this.active]) {
                event.preventDefault();
                window.location.href = this.rows[this.active].url;
            }
        },

        close() {
            this.open = false;
            this.showRecent = false;
            this.active = -1;
        },

        clearRecents() {
            abSearches.clear();
            this.recents = [];
            this.showRecent = false;
            this.open = false;
        },

        startGhost() {
            if (this.running) return;
            if (this.$refs.input && this.$refs.input.value.length > 0) return;
            this.running = true;
            this.hintIndex = Math.floor(Math.random() * this.hints.length);
            this.charIndex = 0;
            this.typing = true;
            this.ghost = '';
            this.tick();
        },

        stopGhost() {
            this.running = false;
            clearTimeout(this.timer);
            this.ghost = 'Search products, brands, categories...';

            // an empty box that is clicked shows the visitor's recent searches
            if (!this.rows.length && !(this.$refs.input && this.$refs.input.value)) {
                this.recents = abSearches.read();
                if (this.recents.length) {
                    this.showRecent = true;
                    this.open = true;
                }
            }
        },

        tick() {
            if (!this.running) return;

            const currentHint = this.hints[this.hintIndex];

            if (this.typing) {
                this.charIndex++;
                this.ghost = currentHint.substring(0, this.charIndex);

                if (this.charIndex >= currentHint.length) {
                    // pause at full text, then start erasing
                    this.timer = setTimeout(() => {
                        this.typing = false;
                        this.tick();
                    }, 2000);
                    return;
                }
                this.timer = setTimeout(() => this.tick(), 60 + Math.random() * 40);
            } else {
                this.charIndex--;
                this.ghost = currentHint.substring(0, this.charIndex);

                if (this.charIndex <= 0) {
                    // move to next hint
                    this.hintIndex = (this.hintIndex + 1) % this.hints.length;
                    this.typing = true;
                    this.timer = setTimeout(() => this.tick(), 400);
                    return;
                }
                this.timer = setTimeout(() => this.tick(), 30);
            }
        }
    };
};

// Product comparison basket: kept in the browser (no login), max 4 products.
Alpine.store('compare', {
    max: 4,
    items: [],
    notice: '',

    init() {
        try {
            const saved = abStore.read('ab_compare', 7);
            this.items = (Array.isArray(saved) ? saved : [])
                .filter((p) => p && typeof p.slug === 'string' && /^[a-z0-9-]+$/i.test(p.slug))
                .slice(0, this.max);
        } catch (e) {
            this.items = [];
        }
    },

    save() {
        try {
            abStore.write('ab_compare', this.items);
        } catch (e) {}
    },

    has(slug) {
        return this.items.some((p) => p.slug === slug);
    },

    toggle(product) {
        if (this.has(product.slug)) {
            return this.remove(product.slug);
        }
        if (this.items.length >= this.max) {
            this.notice = 'You can compare up to ' + this.max + ' products. Remove one first.';
            setTimeout(() => (this.notice = ''), 3500);
            return;
        }
        this.items.push({ slug: product.slug, name: product.name, image: product.image || '' });
        this.save();
    },

    remove(slug) {
        this.items = this.items.filter((p) => p.slug !== slug);
        this.save();
    },

    set(list) {
        this.items = list.slice(0, this.max);
        this.save();
    },

    clear() {
        this.items = [];
        this.save();
    },

    get url() {
        return '/compare?p=' + this.items.map((p) => p.slug).join(',');
    },
});
Alpine.store('compare').init();

// Enquiry list ("cart"): products a visitor wants one combined enquiry for. Kept in the browser.
Alpine.store('enquiryList', {
    max: 20,
    items: [],
    toast: '',
    toastTimer: null,

    init() {
        try {
            const saved = abStore.read('ab_enquiry_list', 30);
            this.items = (Array.isArray(saved) ? saved : [])
                .filter((p) => p && typeof p.slug === 'string' && /^[a-z0-9-]+$/i.test(p.slug))
                .map((p) => ({ ...p, qty: Math.min(999, Math.max(1, parseInt(p.qty, 10) || 1)) }))
                .slice(0, this.max);
        } catch (e) {
            this.items = [];
        }
    },

    save() {
        try {
            abStore.write('ab_enquiry_list', this.items);
        } catch (e) {}
    },

    has(slug) {
        return this.items.some((p) => p.slug === slug);
    },

    get count() {
        return this.items.length;
    },

    get units() {
        return this.items.reduce((n, p) => n + p.qty, 0);
    },

    say(message) {
        this.toast = message;
        clearTimeout(this.toastTimer);
        this.toastTimer = setTimeout(() => (this.toast = ''), 4000);
    },

    toggle(product) {
        if (this.has(product.slug)) {
            this.remove(product.slug);
            return;
        }
        if (this.items.length >= this.max) {
            this.say('Your list is full (' + this.max + ' products). Please send this enquiry first.');
            return;
        }
        this.items.push({
            slug: product.slug,
            name: product.name,
            image: product.image || '',
            brand: product.brand || '',
            category: product.category || '',
            qty: 1,
        });
        this.save();
        this.say('Added to your enquiry list');
    },

    remove(slug) {
        this.items = this.items.filter((p) => p.slug !== slug);
        this.save();
    },

    setQty(slug, qty) {
        const item = this.items.find((p) => p.slug === slug);
        if (!item) return;
        item.qty = Math.min(999, Math.max(1, parseInt(qty, 10) || 1));
        this.save();
    },

    clear() {
        this.items = [];
        this.save();
    },
});
Alpine.store('enquiryList').init();

// "Back to results": returns to the page the visitor came from when that was a results page, else to the category.
window.backLink = (fallbackLabel) => ({
    label: fallbackLabel,
    useHistory: false,

    init() {
        try {
            const ref = document.referrer ? new URL(document.referrer) : null;
            if (!ref || ref.origin !== window.location.origin || window.history.length < 2) return;

            const path = ref.pathname;
            if (path.startsWith('/search')) {
                this.label = 'Back to search results';
            } else if (path.startsWith('/compare')) {
                this.label = 'Back to comparison';
            } else if (path.startsWith('/enquiry-list')) {
                this.label = 'Back to enquiry list';
            } else if (path.startsWith('/brands/') || path.startsWith('/verticals/') || path === '/verticals') {
                this.label = 'Back to results';
            } else {
                return;
            }
            this.useHistory = true;
        } catch (e) {}
    },

    go(event) {
        if (this.useHistory) {
            event.preventDefault();
            window.history.back();
        }
    },
});

// Recently viewed products (kept in the browser, newest first).
Alpine.store('recent', {
    max: 8,
    items: [],
    dismissed: '',

    init() {
        try {
            const saved = abStore.read('ab_recent', 30);
            this.items = (Array.isArray(saved) ? saved : [])
                .filter((p) => p && typeof p.slug === 'string' && /^[a-z0-9-]+$/i.test(p.slug))
                .slice(0, this.max);
            this.dismissed = sessionStorage.getItem('ab_recent_dismissed') || '';
        } catch (e) {
            this.items = [];
        }
    },

    add(product) {
        this.items = [product, ...this.items.filter((p) => p.slug !== product.slug)].slice(0, this.max);
        try {
            abStore.write('ab_recent', this.items);
        } catch (e) {}
    },

    // Newest product that is not the page being viewed.
    latest(currentSlug) {
        return this.items.find((p) => p.slug !== currentSlug) || null;
    },

    dismiss(slug) {
        this.dismissed = slug;
        try {
            sessionStorage.setItem('ab_recent_dismissed', slug);
        } catch (e) {}
    },
});
Alpine.store('recent').init();

// Images that finished (or failed) before their onload could run: stop their placeholder shimmer.
const settleImages = () => {
    document.querySelectorAll('img.img-load:not(.is-loaded)').forEach((img) => {
        if (img.complete) {
            img.classList.add('is-loaded');
        } else {
            img.addEventListener('error', () => img.classList.add('is-loaded'), { once: true });
        }
    });
};
document.addEventListener('DOMContentLoaded', settleImages);
window.addEventListener('pageshow', settleImages);

window.Alpine = Alpine;
Alpine.start();
