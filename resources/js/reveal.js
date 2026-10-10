// Scroll reveal: elements marked data-reveal fade/slide in once, the first time enough of them is on screen.
//
//   data-reveal              slide up (default)
//   data-reveal="left"       slide in from the left      ("right", "zoom" and "fade" work the same way)
//   data-reveal-delay="200"  extra wait in milliseconds
//
// When does an item start? When enough of it is visible to be noticed: 200px, or 60% of it for small items.
// (Starting as soon as the first pixel appears makes tall blocks finish their entrance while still mostly off screen.)
// Tall blocks that slide up get a longer, slightly bigger movement so they feel like the small ones.
// Items that become ready in the same moment (a row of cards) are staggered. Only opacity and transform change,
// so nothing around them moves. Visitors who ask their device to reduce motion see the page without animation,
// and a failsafe in the page head shows everything if this file never loads (see layouts/app.blade.php).

const MIN_VISIBLE_PX = 200;
const TALL_PX = 280;

const pending = [...document.querySelectorAll('[data-reveal]')];

window.__revealOk = true; // tells the failsafe in <head> that we are running

const show = (el, delay = 0) => {
    el.style.setProperty('--reveal-delay', delay + 'ms');
    el.classList.add('is-revealed');
};

const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (reduce) {
    pending.forEach((el) => show(el));
} else if (pending.length) {
    let queued = false;

    const check = () => {
        queued = false;
        const vh = window.innerHeight;
        let step = 0;

        for (let i = 0; i < pending.length; ) {
            const el = pending[i];

            // not on the page right now (e.g. a desktop-only part on a phone): wait until it is
            if (!el.getClientRects().length) {
                i++;
                continue;
            }

            const r = el.getBoundingClientRect();
            const visible = Math.min(r.bottom, vh) - Math.max(r.top, 0);
            const passedAbove = r.bottom <= 0;          // already scrolled past (page opened part-way down)
            const enough = visible >= Math.min(MIN_VISIBLE_PX, r.height * 0.6) && r.top < vh;

            if (passedAbove || enough) {
                const extra = parseInt(el.dataset.revealDelay || '0', 10) || 0;
                const plainSlideUp = el.dataset.reveal === '' || el.dataset.reveal === 'up';
                if (plainSlideUp && r.height > TALL_PX) el.classList.add('reveal-lg');

                show(el, passedAbove ? 0 : Math.min(step++, 5) * 70 + extra);
                pending.splice(i, 1);
            } else {
                i++;
            }
        }

        if (!pending.length) detach();
    };

    const request = () => {
        if (!queued) {
            queued = true;
            requestAnimationFrame(check);
        }
    };

    const detach = () => {
        window.removeEventListener('scroll', request);
        window.removeEventListener('resize', request);
        window.removeEventListener('load', request);
    };

    window.addEventListener('scroll', request, { passive: true });
    window.addEventListener('resize', request);
    window.addEventListener('load', request);      // pictures change heights once they arrive
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(request);
    check();
}
