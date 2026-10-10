// Run with: node --test tests/js
import test from 'node:test';
import assert from 'node:assert/strict';
import {
    MAX_BYTES, MAX_DRAFTS, PREFIX, TTL_MS, ago, applyFieldValues, cleanState, draftKey, loadDraft, mergeRestore, purge, saveDraft, stable,
} from '../../resources/js/admin-drafts-core.js';

// A tiny stand-in for localStorage
class Store {
    constructor() { this.map = new Map(); }
    get length() { return this.map.size; }
    key(i) { return [...this.map.keys()][i] ?? null; }
    getItem(k) { return this.map.has(k) ? this.map.get(k) : null; }
    setItem(k, v) { this.map.set(k, String(v)); }
    removeItem(k) { this.map.delete(k); }
}

const DAY = 24 * 60 * 60 * 1000;

test('a draft lives for one week at most', () => {
    assert.equal(TTL_MS, 7 * DAY);
    const s = new Store();
    const now = 1_000_000_000_000;
    saveDraft(s, 'ab_admin_draft:/a/edit', { name: 'x' }, { name: '' }, now);

    assert.deepEqual(loadDraft(s, 'ab_admin_draft:/a/edit', now + 6 * DAY).data, { name: 'x' });
    assert.equal(loadDraft(s, 'ab_admin_draft:/a/edit', now + 7 * DAY + 1), null);       // expired: gone
    assert.equal(s.getItem('ab_admin_draft:/a/edit'), null);                              // and removed from storage
});

test('purge removes expired and broken drafts but leaves other keys alone', () => {
    const s = new Store();
    const now = 5_000_000_000_000;
    saveDraft(s, PREFIX + '/fresh/edit', { a: 1 }, {}, now - DAY);
    saveDraft(s, PREFIX + '/old/edit', { a: 1 }, {}, now - 8 * DAY);
    s.setItem(PREFIX + '/broken/edit', '{not json');
    s.setItem('ab_compare', '[]');                                                         // another feature's data

    const removed = purge(s, now);

    assert.equal(removed, 2);
    assert.notEqual(s.getItem(PREFIX + '/fresh/edit'), null);
    assert.equal(s.getItem(PREFIX + '/old/edit'), null);
    assert.equal(s.getItem(PREFIX + '/broken/edit'), null);
    assert.equal(s.getItem('ab_compare'), '[]');
});

test('only the newest drafts are kept', () => {
    const s = new Store();
    const now = 9_000_000_000_000;
    for (let i = 0; i < MAX_DRAFTS + 5; i++) saveDraft(s, PREFIX + '/p' + i + '/edit', { i: 1 }, {}, now - i * 1000);

    purge(s, now);

    assert.equal(s.length, MAX_DRAFTS);
    assert.notEqual(s.getItem(PREFIX + '/p0/edit'), null);                                 // newest stays
    assert.equal(s.getItem(PREFIX + '/p' + (MAX_DRAFTS + 4) + '/edit'), null);             // oldest goes
});

test('a form that is back to its saved state leaves no draft', () => {
    const s = new Store();
    const baseline = { name: 'Entris', specs: { A: '1' } };

    assert.equal(saveDraft(s, 'k', { name: 'Entris II', specs: { A: '1' } }, baseline), 'saved');
    assert.notEqual(s.getItem('k'), null);

    // same content, different key order: still "unchanged"
    assert.equal(saveDraft(s, 'k', { specs: { A: '1' }, name: 'Entris' }, baseline), 'cleared');
    assert.equal(s.getItem('k'), null);
});

test('huge drafts and full storage are skipped without errors', () => {
    const s = new Store();
    assert.equal(saveDraft(s, 'k', { body: 'x'.repeat(MAX_BYTES + 10) }, {}), 'too-big');
    assert.equal(s.getItem('k'), null);

    const full = { setItem() { throw new Error('QuotaExceededError'); }, removeItem() {}, getItem: () => null };
    assert.equal(saveDraft(full, 'k', { a: 1 }, {}), 'failed');
});

test('freshly picked files are never part of a draft, existing paths are', () => {
    const state = cleanState({
        name: 'Entris',
        image: { 'uuid-1': 'livewire-file:abc123.png' },                                    // just uploaded, not saved
        gallery: ['products/a.png', 'products/b.png'],                                       // already stored files
        documents: [{ title: 'Brochure', file: { u2: 'livewire-file:zzz.pdf' } }],            // a file inside a repeater row
        faqs: [{ question: 'Why?', answer: 'Because' }],
    });

    assert.deepEqual(Object.keys(state).sort(), ['faqs', 'gallery', 'name']);
    assert.deepEqual(state.gallery, ['products/a.png', 'products/b.png']);
});

test('restoring keeps every field the draft does not hold', () => {
    const current = { name: 'Saved name', image: { u: 'products/x.png' }, specs: { A: '1' } };
    const draft = { name: 'Edited name', specs: { A: '1', B: '2' } };

    assert.deepEqual(mergeRestore(current, draft), { name: 'Edited name', image: { u: 'products/x.png' }, specs: { A: '1', B: '2' } });
});

test('keys, comparison and wording', () => {
    assert.equal(draftKey('/admin/products/12/edit'), PREFIX + '/admin/products/12/edit');
    assert.equal(draftKey('/admin/products/create/'), PREFIX + '/admin/products/create');
    assert.equal(stable({ b: 1, a: [2, { d: 1, c: 2 }] }), stable({ a: [2, { c: 2, d: 1 }], b: 1 }));
    assert.equal(ago(10_000), 'less than a minute ago');
    assert.equal(ago(5 * 60_000), '5 minutes ago');
    assert.equal(ago(3 * 3600_000), '3 hours ago');
    assert.equal(ago(2 * DAY), '2 days ago');
});

test('broken stored values do not crash loading', () => {
    const s = new Store();
    s.setItem('a', 'null');
    s.setItem('b', JSON.stringify({ t: 'x', data: {} }));
    s.setItem('c', JSON.stringify({ t: Date.now(), data: null }));

    assert.equal(loadDraft(s, 'a'), null);
    assert.equal(loadDraft(s, 'b'), null);
    assert.equal(loadDraft(s, 'c'), null);
    assert.equal(loadDraft(s, 'missing'), null);
});

test('text still being typed is copied from the screen into the state', () => {
    const data = { name: 'Old', qty: 5, features: { 'abc-1': { title: 'Fast', text: '' } }, other: 'keep' };

    applyFieldValues(data, [
        { path: 'data.name', value: 'New name typed' },
        { path: 'data.qty', value: '7' },
        { path: 'data.features.abc-1.text', value: 'Typed in a repeater row' },
        { path: 'data.missing', value: 'ignored' },                       // keys that do not exist are not invented
        { path: 'data.features.nope.title', value: 'ignored too' },
        { path: 'notData.name', value: 'ignored' },
    ]);

    assert.equal(data.name, 'New name typed');
    assert.equal(data.qty, 7);                                            // number stays a number
    assert.equal(data.features['abc-1'].text, 'Typed in a repeater row');
    assert.equal(data.other, 'keep');
    assert.equal('missing' in data, false);
    assert.equal('nope' in data.features, false);
});
