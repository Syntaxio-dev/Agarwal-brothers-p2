@props(['jumps'])
{{-- Phones only: a slim bar of jump links under the navbar. It stays while you scroll and highlights the section you are in.
     $jumps = [[element id, label], ...]. The page wrapper must NOT be overflow-hidden on phones (use max-md:overflow-clip), or sticky cannot work. --}}
<nav x-data="{
        cur: '{{ $jumps[0][0] }}', ids: @js(array_column($jumps, 0)),
        spy() {
            let c = this.ids[0];
            for (const id of this.ids) { const e = document.getElementById(id); if (e && e.getBoundingClientRect().top <= 150) c = id; }
            if (c === this.cur) return;
            this.cur = c;
            const r = this.$refs.row, b = this.$refs[c];
            if (r && b) r.scrollTo({ left: b.offsetLeft - (r.clientWidth - b.offsetWidth) / 2, behavior: 'smooth' });
        }
     }" @scroll.window.passive="spy()"
     class="sticky z-30 -mx-4 mt-3 border-b border-gray-100 bg-white px-4 py-2 md:hidden" style="top: calc(var(--nav-h) + 0.05rem)" aria-label="Jump to a section">
    <div x-ref="row" class="relative flex gap-2 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($jumps as [$jid, $jlabel])
            <a href="#{{ $jid }}" x-ref="{{ $jid }}" :class="cur === '{{ $jid }}' ? 'chip-active' : ''" class="chip shrink-0 !px-3.5 !py-1.5 !text-xs">{{ $jlabel }}</a>
        @endforeach
    </div>
</nav>
