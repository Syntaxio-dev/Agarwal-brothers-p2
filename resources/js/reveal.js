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

if (reduce || !('IntersectionObserver' in window)) {
    pending.forEach((el) => show(el));
} else if (pending.length) {
    // The browser tells us when an item moves into view (IntersectionObserver), so nothing is measured while the
    // visitor scrolls. (The old version measured every waiting item on every scroll frame, which made scrolling
    // stutter on long pages.) The numbers come ready-made with each report, so no layout is forced.
    const thresholds = Array.from({ length: 51 }, (_, n) => n / 50);

    const io = new IntersectionObserver((entries) => {
        let step = 0;

        // top-most first, so a row of cards that arrives together is staggered left to right / top to bottom
        entries.sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top || a.boundingClientRect.left - b.boundingClientRect.left);

        entries.forEach((entry) => {
            const el = entry.target;
            if (el.classList.contains('is-revealed')) return;

            const r = entry.boundingClientRect;
            if (!r.width && !r.height) return;            // not on the page right now (display:none): wait until it is

            const vh = entry.rootBounds ? entry.rootBounds.height : window.innerHeight;
            const visible = entry.intersectionRect.height;
            const passedAbove = r.bottom <= 0;            // already scrolled past (page opened part-way down)
            const startedAbove = r.top < 0 && visible > 0; // its start is already above the screen (jumped down with End / a link): show it at once
            const enough = visible >= Math.min(MIN_VISIBLE_PX, r.height * 0.6) && r.top < vh;

            if (!(passedAbove || startedAbove || enough)) return;

            const extra = parseInt(el.dataset.revealDelay || '0', 10) || 0;
            const plainSlideUp = el.dataset.reveal === '' || el.dataset.reveal === 'up';
            if (plainSlideUp && r.height > TALL_PX) el.classList.add('reveal-lg');

            show(el, passedAbove || startedAbove ? 0 : Math.min(step++, 5) * 70 + extra);
            io.unobserve(el);
        });
    }, { threshold: thresholds });

    pending.forEach((el) => io.observe(el));
}
