// Client-side form helpers: filtering of typed characters, inline errors, loading state on submit.
// The same rules are enforced on the server (App\Support\FormRules); this just saves a round trip.

const NAME_ALLOWED = /[^\p{L}\p{M} .'’-]/gu;
const NAME_VALID = /^\p{L}[\p{L}\p{M} .'’-]*$/u;
const EMAIL_VALID = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

// x-data="formGuard(serverErrors)" on a <form>. Fields opt in with data-rule / data-clean / data-label.
window.formGuard = (serverErrors = {}) => ({
    errors: { ...serverErrors },
    sending: false,

    init() {
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) this.sending = false; // coming back with the browser's back button
        });

        this.$nextTick(() => {
            let first = null;
            for (const key of Object.keys(this.errors)) {
                const el = this.fieldFor(key);
                if (el) {
                    el.setAttribute('aria-invalid', 'true');
                    first = first || el;
                }
            }
            if (first && typeof first.focus === 'function') first.focus({ preventScroll: true });
        });
    },

    keyOf(el) {
        return el.name.replace(/\[(\w+)\]/g, '.$1');
    },

    fieldFor(key) {
        const name = key.replace(/\.(\w+)/g, '[$1]');
        return this.$el.querySelector('[name="' + name + '"]');
    },

    // Called on every keystroke: drop characters that can never be valid.
    clean(el) {
        const kind = el.dataset.clean;
        if (kind === 'name') {
            el.value = el.value.replace(NAME_ALLOWED, '').replace(/\s{2,}/g, ' ').slice(0, 80);
        } else if (kind === 'digits') {
            el.value = el.value.replace(/\D/g, '').slice(0, 10);
        }
        if (this.errors[this.keyOf(el)]) this.setError(el, '');
    },

    message(el) {
        const rules = (el.dataset.rule || '').split(' ').filter(Boolean);
        const label = el.dataset.label || 'This field';
        const value = (el.type === 'file' ? el.value : el.value.trim());

        if (!value) {
            return rules.includes('required') ? label + ' is required.' : '';
        }
        if (rules.includes('name')) {
            if (value.length < 2) return 'Please enter your full name.';
            if (!NAME_VALID.test(value)) return "Name can only contain letters, spaces and . ' -";
        }
        if (rules.includes('email') && !EMAIL_VALID.test(value)) return 'Enter a valid email address.';
        if (rules.includes('phone') && !/^\d{10}$/.test(value)) return 'Enter a 10-digit mobile number (digits only, without the country code).';
        return '';
    },

    setError(el, message) {
        const key = this.keyOf(el);
        if (message) {
            this.errors[key] = message;
            el.setAttribute('aria-invalid', 'true');
        } else {
            delete this.errors[key];
            el.removeAttribute('aria-invalid');
        }
    },

    check(el) {
        this.setError(el, this.message(el));
    },

    submit(event) {
        let first = null;
        this.$el.querySelectorAll('[data-rule]').forEach((el) => {
            const msg = this.message(el);
            this.setError(el, msg);
            if (msg && !first) first = el;
        });
        if (first) {
            event.preventDefault();
            first.focus();
            first.scrollIntoView({ block: 'center', behavior: 'smooth' });
            return;
        }
        this.sending = true;
    },
});

// Country picker for the phone field (searchable list, remembers the choice).
window.countryPicker = (options, selected) => ({
    options,
    iso: options.some((o) => o.iso === selected) ? selected : 'IN',
    open: false,
    query: '',

    get current() {
        return this.options.find((o) => o.iso === this.iso) || this.options[0];
    },

    get filtered() {
        const q = this.query.trim().toLowerCase().replace(/^\+/, '');
        if (!q) return this.options;
        return this.options.filter((o) => o.name.toLowerCase().includes(q) || o.code.startsWith(q) || o.iso.toLowerCase() === q);
    },

    pick(option) {
        this.iso = option.iso;
        this.open = false;
        this.query = '';
    },
});
