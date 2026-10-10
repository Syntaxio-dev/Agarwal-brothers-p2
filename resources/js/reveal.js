// Scroll reveal: elements marked data-reveal fade/slide in once, the first time they scroll into view.
//
//   data-reveal              slide up (default)
//   data-reveal="left"       slide in from the left      ("right", "zoom" and "fade" work the same way)
//   data-reveal-delay="200"  extra wait in milliseconds
//
// Items that appear together (a row of cards) are staggered automatically. Only opacity and transform change,
// so nothing around them moves. Visitors who ask their device to reduce motion see the page without animation,
// and a failsafe in the page head shows everything if this file never loads (see layouts/app.blade.php).

const items = [...document.querySelectorAll('[data-reveal]')];

window.__revealOk = true; // tells the failsafe in <head> that we are running

const showAll = () => items.forEach((el) => el.classList.add('is-revealed'));

if (items.length) {
    const reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduce || !('IntersectionObserver' in window)) {
        showAll();
    } else {
        const observer = new IntersectionObserver(
            (entries) => {
                entries
                    .filter((e) => e.isIntersecting)
                    .sort((a, b) => (a.target.compareDocumentPosition(b.target) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1))
                    .forEach((entry, i) => {
                        const el = entry.target;
                        const extra = parseInt(el.dataset.revealDelay || '0', 10) || 0;
                        el.style.setProperty('--reveal-delay', Math.min(i, 5) * 70 + extra + 'ms');
                        el.classList.add('is-revealed');
                        observer.unobserve(el);
                    });
            },
            // a little before the bottom edge, so things start as they come into view; threshold 0 also suits tall blocks
            { rootMargin: '0px 0px -8% 0px', threshold: 0.01 },
        );

        items.forEach((el) => observer.observe(el));
    }
}
