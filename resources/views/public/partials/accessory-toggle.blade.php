{{-- Add / remove an accessory from the enquiry list (the same list as the products). Needs $accessory (with brand loaded). --}}
@php
    $key = $accessory->listKey();
    $el = [
        'slug' => $key,
        'type' => 'accessory',
        'name' => $accessory->name,
        'image' => $accessory->image ? asset('storage/' . $accessory->image) : '',
        'brand' => $accessory->brand?->name ?: '',
        'category' => 'Accessory',
    ];
@endphp
<button type="button"
        data-item="{{ json_encode($el) }}"
        @click.prevent.stop="$store.enquiryList.toggle(JSON.parse($el.dataset.item))"
        :class="$store.enquiryList.has('{{ $key }}') ? 'border-transparent bg-navy text-white shadow-[inset_0_-2px_0_#00B4D8]' : 'border-navy/15 bg-white text-navy hover:border-cyan hover:shadow-[inset_0_-2px_0_rgb(0_180_216_/_0.5)]'"
        :aria-pressed="$store.enquiryList.has('{{ $key }}') ? 'true' : 'false'"
        aria-label="Add {{ $accessory->name }} to enquiry list"
        class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-lg border px-3 text-[12.5px] font-semibold transition-all duration-200 active:scale-95 {{ $class ?? '' }}">
    <svg x-show="$store.enquiryList.has('{{ $key }}')" x-cloak class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
    <span x-text="$store.enquiryList.has('{{ $key }}') ? 'In list' : '+ Add to list'">+ Add to list</span>
</button>
