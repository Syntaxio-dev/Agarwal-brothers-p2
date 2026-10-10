// Logic of the admin "draft" safety net, kept free of browser/Livewire code so it can be tested with Node.
// A draft is a copy of a half-filled form kept in this browser only. It is removed when the form is saved
// and never lives longer than a week.

export const PREFIX = 'ab_admin_draft:';
export const TTL_MS = 7 * 24 * 60 * 60 * 1000; // one week
export const MAX_BYTES = 300 * 1024; // a draft bigger than this (e.g. pasted images) is not kept
export const MAX_DRAFTS = 25; // at most this many drafts at once, oldest go first

export const draftKey = (pathname) => PREFIX + pathname.replace(/\/+$/, '');

const FILE_MARK = 'livewire-file:';

const hasFile = (value) => {
    if (typeof value === 'string') return value.startsWith(FILE_MARK);
    if (Array.isArray(value)) return value.some(hasFile);
    if (value && typeof value === 'object') return Object.values(value).some(hasFile);
    return false;
};

// Form state without anything that cannot be restored. Fields holding a freshly picked (not yet saved) file are
// left out completely, so restoring never touches them.
export const cleanState = (data) => {
    const out = {};
    for (const [key, value] of Object.entries(data || {})) {
        if (!hasFile(value)) out[key] = JSON.parse(JSON.stringify(value ?? null));
    }
    return out;
};

// Same text for the same content whatever the key order, so two states can be compared.
export const stable = (value) => {
    if (Array.isArray(value)) return '[' + value.map(stable).join(',') + ']';
    if (value && typeof value === 'object') {
        return '{' + Object.keys(value).sort().map((k) => JSON.stringify(k) + ':' + stable(value[k])).join(',') + '}';
    }
    return JSON.stringify(value);
};

// Keep a draft of `state` unless it equals the untouched form (`baseline`), in which case drop any old draft.
export const saveDraft = (storage, key, state, baseline, now = Date.now()) => {
    if (stable(state) === stable(baseline)) {
        storage.removeItem(key);
        return 'cleared';
    }
    const json = JSON.stringify({ t: now, data: state });
    if (json.length > MAX_BYTES) return 'too-big';
    try {
        storage.setItem(key, json);
    } catch (e) {
        return 'failed'; // storage full or blocked: carry on without a draft
    }
    return 'saved';
};

export const loadDraft = (storage, key, now = Date.now()) => {
    try {
        const draft = JSON.parse(storage.getItem(key) || 'null');
        if (!draft || typeof draft.t !== 'number' || typeof draft.data !== 'object' || draft.data === null) {
            storage.removeItem(key);
            return null;
        }
        if (now - draft.t > TTL_MS) {
            storage.removeItem(key);
            return null;
        }
        return draft;
    } catch (e) {
        try {
            storage.removeItem(key); // unreadable draft: remove it
        } catch (e2) {}
        return null;
    }
};

// Delete expired or broken drafts and keep only the newest MAX_DRAFTS. Returns how many were removed.
export const purge = (storage, now = Date.now()) => {
    const found = [];
    let removed = 0;
    for (let i = storage.length - 1; i >= 0; i--) {
        const key = storage.key(i);
        if (!key || !key.startsWith(PREFIX)) continue;
        const draft = loadDraft(storage, key, now);
        if (draft) found.push({ key, t: draft.t });
        else removed++;
    }
    found.sort((a, b) => b.t - a.t);
    for (const old of found.slice(MAX_DRAFTS)) {
        storage.removeItem(old.key);
        removed++;
    }
    return removed;
};

// Text typed into a field only reaches the form state when the field loses focus (or after a short pause).
// To keep what is on screen, copy the field values (path like "data.features.<id>.title") over the state.
// Only existing keys are touched; numbers stay numbers.
export const applyFieldValues = (data, fields) => {
    for (const { path, value } of fields) {
        const parts = path.split('.');
        if (parts[0] !== 'data' || parts.length < 2) continue;
        let target = data;
        for (const part of parts.slice(1, -1)) {
            if (target === null || typeof target !== 'object' || !(part in target)) { target = null; break; }
            target = target[part];
        }
        const last = parts[parts.length - 1];
        if (target === null || typeof target !== 'object' || !(last in target)) continue;
        target[last] = typeof target[last] === 'number' && value !== '' && !Number.isNaN(Number(value)) ? Number(value) : value;
    }
    return data;
};

// The draft's values laid over the current form; fields the draft does not hold are kept as they are.
export const mergeRestore = (current, draft) => ({ ...current, ...draft });

export const ago = (ms) => {
    const m = Math.round(ms / 60000);
    if (m < 1) return 'less than a minute ago';
    if (m < 60) return m + (m === 1 ? ' minute ago' : ' minutes ago');
    const h = Math.round(m / 60);
    if (h < 24) return h + (h === 1 ? ' hour ago' : ' hours ago');
    const d = Math.round(h / 24);
    return d + (d === 1 ? ' day ago' : ' days ago');
};
