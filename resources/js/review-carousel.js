const STEP_PAUSE = 10000;
const SLIDE_MS = 700;

window.reviewCarousel = function (items) {
    return {
        items,
        offset: 0,
        moving: false,
        paused: false,
        step: 0,

        get visible() {
            const w = window.innerWidth;
            return w >= 1024 ? 3 : w >= 768 ? 2 : 1;
        },

        get list() {
            const n = this.items.length;
            return this.items.map((_, i) => this.items[(i + this.offset) % n]);
        },

        init() {
            setInterval(() => {
                if (!this.paused && !this.moving && this.items.length > this.visible) this.advance();
            }, STEP_PAUSE);
        },

        advance() {
            const first = this.$refs.track.firstElementChild;
            if (!first) return;
            this.step = first.offsetWidth;
            this.moving = true;
            setTimeout(() => {
                this.offset = (this.offset + 1) % this.items.length;
                this.moving = false;
            }, SLIDE_MS);
        },
    };
};
