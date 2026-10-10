{{-- Add / remove a product from the enquiry list. $large = full-size button (product page), otherwise a card chip. --}}
@php
    $el = [
        'slug' => $product->slug,
        'name' => $product->name,
        'image' => $product->image ? asset('storage/' . $product->image) : '',
        'brand' => $product->category?->brand?->name,
        'category' => $product->category?->name,
    ];
@endphp
<button type="button"
        data-item="{{ json_encode($el) }}"
        @click.prevent.stop="$store.enquiryList.toggle(JSON.parse($el.dataset.item))"
        :class="{ '{{ ($large ?? false) ? 'compare-on' : 'chip-active' }}': $store.enquiryList.has('{{ $product->slug }}') }"
        :aria-pressed="$store.enquiryList.has('{{ $product->slug }}') ? 'true' : 'false'"
        aria-label="Add {{ $product->name }} to enquiry list" @if (! ($large ?? false)) title="Add to enquiry list" @endif
        class="{{ ($large ?? false) ? 'btn-ghost' : 'chip absolute bottom-3 right-4 z-10 h-9 w-9 justify-center p-0 hover:scale-110 active:scale-95' }} {{ $class ?? '' }}">
    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <g x-show="! $store.enquiryList.has('{{ $product->slug }}')">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm0 5.25h.007v.008H3.75V12Zm0 5.25h.007v.008H3.75v-.008Z"/>
        </g>
        <g x-show="$store.enquiryList.has('{{ $product->slug }}')" x-cloak>
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </g>
    </svg>
    <span class="{{ ($large ?? false) ? '' : 'sr-only' }}" x-text="$store.enquiryList.has('{{ $product->slug }}') ? '{{ ($large ?? false) ? 'In enquiry list' : 'In list' }}' : '{{ ($large ?? false) ? 'Add to enquiry list' : 'Add to list' }}'">{{ ($large ?? false) ? 'Add to enquiry list' : 'Add to list' }}</span>
</button>
