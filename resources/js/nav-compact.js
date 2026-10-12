// Navbar squeeze: scrolling DOWN makes the top navbar slimmer (more room for the page); scrolling UP, or being at the
// top of the page, brings it back to full size. Only a class on <html> changes; the sizes live in app.css.

const root = document.documentElement;
const STEP = 6;       // ignore tiny movements (trackpad jitter)
const AFTER = 120;    // do not squeeze until the visitor is a little way down the page

let lastY = window.scrollY;
let queued = false;

const update = () => {
    queued = false;
    const y = Math.max(0, window.scrollY);

    if (y < 40) {
        root.classList.remove('nav-compact');
        lastY = y;
        return;
    }

    const delta = y - lastY;
    if (Math.abs(delta) < STEP) return;

    if (delta > 0 && y > AFTER) root.classList.add('nav-compact');
    else if (delta < 0) root.classList.remove('nav-compact');

    lastY = y;
};

window.addEventListener('scroll', () => {
    if (!queued) {
        queued = true;
        window.requestAnimationFrame(update);
    }
}, { passive: true });

update();
