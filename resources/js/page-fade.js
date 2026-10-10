// Page-to-page transition: when a visitor follows a normal link on this site, the page content fades out for a moment
// (the sidebar stays), then the next page opens and fades in through its own reveal animation.
// Works in every browser. Left alone: new tabs, modified clicks (Ctrl/Cmd/Shift), downloads, other sites,
// same-page #links, the admin area, and visitors who ask for less motion.

const DURATION = 160;

const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)');
const root = document.documentElement;

document.addEventListener('click', (e) => {
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    if (reduceMotion && reduceMotion.matches) return;

    const a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || (a.target && a.target !== '_self') || a.hasAttribute('download') || a.hasAttribute('data-no-fade')) return;

    let url;
    try {
        url = new URL(a.href, window.location.href);
    } catch (err) {
        return;
    }

    if (url.origin !== window.location.origin || !/^https?:$/.test(url.protocol)) return;
    if (url.pathname === window.location.pathname && url.search === window.location.search) return; // same page / #anchor
    if (/^\/(admin|livewire|storage)(\/|$)/.test(url.pathname)) return;

    e.preventDefault();
    root.classList.add('page-leaving');
    window.setTimeout(() => { window.location.assign(url.href); }, DURATION);
    window.setTimeout(() => root.classList.remove('page-leaving'), 2500); // safety: never leave the page faded out
});

// coming back with the browser's Back button can restore the old page from memory: show it again
window.addEventListener('pageshow', (e) => {
    if (e.persisted) root.classList.remove('page-leaving');
});
