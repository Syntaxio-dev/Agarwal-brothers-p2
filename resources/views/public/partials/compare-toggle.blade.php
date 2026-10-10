{{-- Add / remove a product from the comparison basket. Place inside a `relative` wrapper (not inside the card link). --}}
@php($pc = ['slug' => $product->slug, 'name' => $product->name, 'image' => $product->image ? asset('storage/' . $product->image) : ''])
<button type="button"
        data-compare="{{ json_encode($pc) }}"
        @click.prevent.stop="$store.compare.toggle(JSON.parse($el.dataset.compare))"
        :class="{ '{{ ($large ?? false) ? 'compare-on' : 'chip-active' }}': $store.compare.has('{{ $product->slug }}') }"
        :aria-pressed="$store.compare.has('{{ $product->slug }}') ? 'true' : 'false'"
        aria-label="Compare {{ $product->name }}" @if (! ($large ?? false)) title="Compare" @endif
        class="{{ ($large ?? false) ? 'btn-ghost' : 'chip absolute bottom-3 right-[3.75rem] z-10 h-9 w-9 justify-center p-0' }} {{ $class ?? '' }}">
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
        <g x-show="! $store.compare.has('{{ $product->slug }}')">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
        </g>
        <g x-show="$store.compare.has('{{ $product->slug }}')" x-cloak>
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </g>
    </svg>
    <span class="{{ ($large ?? false) ? '' : 'sr-only' }}" x-text="$store.compare.has('{{ $product->slug }}') ? 'Added' : 'Compare'">Compare</span>
</button>
