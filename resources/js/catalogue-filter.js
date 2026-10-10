// Category page: keyword search, model-group chips and sorting, all in the browser (every model is already on the page).
// items: [{ id, name, group, text }]  (text = lower-case name + description, used for the keyword match)
window.catalogueFilter = (items) => ({
    items,
    q: '',
    group: 'all',
    sort: 'default',

    get byId() {
        return Object.fromEntries(this.items.map((i, n) => [i.id, { ...i, n }]));
    },

    matches(id) {
        const item = this.byId[id];
        if (!item) return false;
        if (this.group !== 'all' && item.group !== this.group) return false;
        const q = this.q.trim().toLowerCase();
        return !q || item.text.includes(q);
    },

    // CSS "order" of a card inside its grid.
    order(id) {
        const ids = this.items.map((i) => i.id);
        if (this.sort === 'az') ids.sort((a, b) => this.byId[a].name.localeCompare(this.byId[b].name, undefined, { numeric: true }));
        if (this.sort === 'za') ids.sort((a, b) => this.byId[b].name.localeCompare(this.byId[a].name, undefined, { numeric: true }));
        if (this.sort === 'newest') ids.sort((a, b) => b - a);
        return ids.indexOf(id);
    },

    get shown() {
        return this.items.filter((i) => this.matches(i.id)).length;
    },

    groupShown(name) {
        return this.items.filter((i) => i.group === name && this.matches(i.id)).length;
    },

    groupTotal(name) {
        return this.items.filter((i) => i.group === name).length;
    },

    get active() {
        return this.q.trim() !== '' || this.group !== 'all' || this.sort !== 'default';
    },

    reset() {
        this.q = '';
        this.group = 'all';
        this.sort = 'default';
    },
});
