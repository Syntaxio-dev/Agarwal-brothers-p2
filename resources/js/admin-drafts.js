// Admin safety net for long forms: keeps a draft in this browser while you type and offers to restore it
// after an accident. See admin-drafts-core.js for the rules (one week at most, removed on save, nothing sent to the server).
import { ago, applyFieldValues, cleanState, draftKey, loadDraft, mergeRestore, purge, saveDraft, stable } from './admin-drafts-core';

const INTERVAL_MS = 12000;

const onForm = () => /\/(create|edit)\/?$/.test(location.pathname) && !location.pathname.includes('/login');

function formComponent() {
    if (!window.Livewire) return null;
    // The page component that owns the form state ($data); relation managers and widgets do not have it.
    return window.Livewire.all().find((c) => c.$wire && c.$wire.data && typeof c.$wire.data === 'object' && c.el.querySelector('form')) || null;
}

// What is on screen right now: the form state plus text still being typed in a field.
function currentState(component) {
    const data = JSON.parse(JSON.stringify(component.$wire.data));
    const fields = [];
    component.el.querySelectorAll('input, textarea').forEach((el) => {
        const attr = [...el.attributes].find((a) => a.name.startsWith('wire:model'));
        const textual = el.tagName === 'TEXTAREA' || ['text', 'email', 'number', 'tel', 'url', 'search'].includes(el.type);
        if (attr && textual) fields.push({ path: attr.value, value: el.value });
    });

    return cleanState(applyFieldValues(data, fields));
}

function banner(component, draft, onRestore, onDiscard) {
    const host = document.querySelector('.fi-main') || document.querySelector('main') || document.body;
    const bar = document.createElement('div');
    bar.setAttribute('role', 'status');
    bar.dataset.abDraftBanner = '1';
    bar.style.cssText = 'margin:16px 0;padding:14px 16px;border-radius:12px;background:#F4F9FB;border:1px solid #CDEFF7;box-shadow:inset 3px 0 0 #00B4D8;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;font-size:14px;color:#0B2545;';

    const text = document.createElement('div');
    text.innerHTML = '<div style="font-family:ui-monospace,monospace;font-size:11px;letter-spacing:.2em;text-transform:uppercase;color:#0077B6;font-weight:600">Draft found</div>'
        + '<div style="margin-top:2px"></div>';
    text.lastChild.textContent = 'You have unsaved changes from ' + ago(Date.now() - draft.t) + '. Restore them? Pictures and files are not part of a draft.';

    const buttons = document.createElement('div');
    buttons.style.cssText = 'display:flex;gap:8px';

    const restore = document.createElement('button');
    restore.type = 'button';
    restore.textContent = 'Restore draft';
    restore.style.cssText = 'background:#0B2545;color:#fff;border:0;border-radius:8px;padding:8px 16px;font-weight:600;font-size:13px;cursor:pointer;box-shadow:inset 0 -2px 0 #00B4D8;';

    const discard = document.createElement('button');
    discard.type = 'button';
    discard.textContent = 'Discard';
    discard.style.cssText = 'background:#fff;color:#0B2545;border:1px solid rgba(11,37,69,.2);border-radius:8px;padding:8px 16px;font-weight:600;font-size:13px;cursor:pointer;';

    restore.addEventListener('click', () => { onRestore(); bar.remove(); });
    discard.addEventListener('click', () => { onDiscard(); bar.remove(); });

    buttons.append(restore, discard);
    bar.append(text, buttons);
    host.prepend(bar);
}

// Livewire may still be mounting the page when this file runs, so look for the form a few times.
function waitForForm(done, tries = 0) {
    const component = formComponent();
    if (component) return done(component);
    if (tries < 30) setTimeout(() => waitForForm(done, tries + 1), 250);
}

function start(component) {
    purge(localStorage); // every admin form page load also sweeps away old drafts (older than a week)

    const key = draftKey(location.pathname);
    let baseline = currentState(component);

    const draft = loadDraft(localStorage, key);
    if (draft && stable(draft.data) !== stable(baseline)) {
        banner(
            component,
            draft,
            () => {
                const merged = mergeRestore(JSON.parse(JSON.stringify(component.$wire.data)), draft.data);
                component.$wire.$set('data', merged);
            },
            () => localStorage.removeItem(key),
        );
    } else if (draft) {
        localStorage.removeItem(key);
    }

    const keep = () => {
        try {
            saveDraft(localStorage, key, currentState(component), baseline);
        } catch (e) {}
    };

    setInterval(keep, INTERVAL_MS);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') keep(); });
    window.addEventListener('pagehide', keep);

    // A successful save or create makes the saved form the new starting point and removes the draft.
    window.Livewire.hook('commit', ({ commit, succeed }) => {
        const saving = (commit.calls || []).some((c) => ['save', 'create', 'saveAndClose'].includes(c.method));
        if (!saving) return;
        succeed(({ snapshot }) => {
            try {
                const parsed = typeof snapshot === 'string' ? JSON.parse(snapshot) : snapshot;
                const errors = (parsed && parsed.memo && parsed.memo.errors) || {};
                if (Object.keys(errors).length === 0) {
                    localStorage.removeItem(key);
                    baseline = currentState(component);
                }
            } catch (e) {}
        });
    });
}

const boot = () => {
    if (!onForm()) return;
    purge(localStorage); // sweep old drafts on every form page, even if the form never shows up
    if (window.Livewire) waitForForm(start);
    else document.addEventListener('livewire:initialized', () => waitForForm(start), { once: true });
};

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
