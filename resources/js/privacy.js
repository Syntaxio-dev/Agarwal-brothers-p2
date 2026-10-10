// Privacy helpers: saved-data expiry, cookie consent, optional analytics, "clear my data".
// Everything the site saves in the visitor's own browser starts with "ab_" so it can be found and removed.
import Alpine from 'alpinejs';

const DAY = 24 * 60 * 60 * 1000;

// Lists saved in localStorage carry a timestamp; anything untouched for longer than its limit is deleted.
// (Visiting and changing the list renews it.)
window.abStore = {
    read(key, ttlDays) {
        try {
            const raw = JSON.parse(localStorage.getItem(key) || 'null');
            if (!raw) return [];
            if (Array.isArray(raw)) return raw; // older format, no timestamp yet
            if (raw.t && Date.now() - raw.t > ttlDays * DAY) {
                localStorage.removeItem(key);
                return [];
            }
            return Array.isArray(raw.items) ? raw.items : [];
        } catch (e) {
            return [];
        }
    },

    write(key, items) {
        try {
            if (!items.length) {
                localStorage.removeItem(key);
                return;
            }
            localStorage.setItem(key, JSON.stringify({ t: Date.now(), items }));
        } catch (e) {}
    },
};

const ANALYTICS_ID = document.querySelector('meta[name="analytics-id"]')?.content || '';
const CONSENT_KEY = 'ab_consent';
const CONSENT_DAYS = 180;

function loadAnalytics() {
    if (!ANALYTICS_ID || window.__abAnalytics) return;
    window.__abAnalytics = true;
    window['ga-disable-' + ANALYTICS_ID] = false;
    window.dataLayer = window.dataLayer || [];
    window.gtag = function () {
        window.dataLayer.push(arguments);
    };
    window.gtag('js', new Date());
    window.gtag('config', ANALYTICS_ID, { anonymize_ip: true });

    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ANALYTICS_ID);
    document.head.appendChild(script);
}

function removeAnalyticsCookies() {
    const host = window.location.hostname;
    const domains = [host, '.' + host, '.' + host.split('.').slice(-2).join('.')];
    document.cookie.split(';').forEach((c) => {
        const name = c.split('=')[0].trim();
        if (name === '_ga' || name === '_gid' || name.startsWith('_ga_') || name.startsWith('_gat')) {
            domains.forEach((d) => {
                document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=' + d;
            });
            document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
        }
    });
}

function stopAnalytics() {
    if (ANALYTICS_ID) window['ga-disable-' + ANALYTICS_ID] = true;
    removeAnalyticsCookies();
}

Alpine.store('consent', {
    choice: null, // 'all' | 'essential' | null (not decided yet)
    open: false, // is the banner showing
    hasAnalytics: ANALYTICS_ID !== '',

    init() {
        try {
            const saved = JSON.parse(localStorage.getItem(CONSENT_KEY) || 'null');
            if (saved && ['all', 'essential'].includes(saved.choice) && Date.now() - saved.t < CONSENT_DAYS * DAY) {
                this.choice = saved.choice;
            } else {
                localStorage.removeItem(CONSENT_KEY);
            }
        } catch (e) {}

        if (this.choice === null) {
            setTimeout(() => (this.open = true), 800);
        }
        this.apply();
    },

    set(choice) {
        this.choice = choice;
        this.open = false;
        try {
            localStorage.setItem(CONSENT_KEY, JSON.stringify({ choice, t: Date.now() }));
        } catch (e) {}
        this.apply();
    },

    reopen() {
        this.open = true;
    },

    apply() {
        if (this.choice === 'all') loadAnalytics();
        if (this.choice === 'essential') stopAnalytics();
    },
});

// Remove everything this site has saved in this browser (lists, recently viewed, consent choice, analytics cookies).
window.abClearSavedData = () => {
    try {
        Object.keys(localStorage).filter((k) => k.startsWith('ab_')).forEach((k) => localStorage.removeItem(k));
        Object.keys(sessionStorage).filter((k) => k.startsWith('ab_')).forEach((k) => sessionStorage.removeItem(k));
    } catch (e) {}
    removeAnalyticsCookies();

    const s = Alpine.store;
    s('compare').items = [];
    s('enquiryList').items = [];
    s('recent').items = [];
    s('recent').dismissed = '';
    s('consent').choice = null;
};
