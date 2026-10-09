import '@fontsource/ibm-plex-sans/latin-400.css';
import '@fontsource/ibm-plex-sans/latin-500.css';
import '@fontsource/ibm-plex-sans/latin-600.css';
import '@fontsource/ibm-plex-mono/latin-400.css';

import Alpine from 'alpinejs';
import './brand-map';
import './review-carousel';

// Ghost search — animated placeholder that cycles through search suggestions
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

window.Alpine = Alpine;
Alpine.start();
