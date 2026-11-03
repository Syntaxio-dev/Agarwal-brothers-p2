{{-- One product card: picture (Compare in its corner), brand, name, short text, two specs, "View details" and the Add-to-list button.
     Needs $product. Optional: $reason (chip on the picture), $q (query for the product link, e.g. the vertical),
     $filterable (category page: the card takes part in the Alpine filter), $showCategory (default true).
     The WRAPPER lifts, so the buttons that sit on the card move with it. Phones: compact card (picture + name, icon buttons in the corner). --}}
@php
    $specs = collect($product->specs ?? [])
        ->filter(fn ($v, $k) => filled($v) && is_scalar($v) && mb_strtolower(trim((string) $k)) !== 'model')
        ->take(2);
@endphp
<div data-reveal class="group relative flex transition-all duration-300 hover:-translate-y-1"
     @if ($filterable ?? false) x-show="matches({{ $product->id }})" :style="{ order: order({{ $product->id }}) }" @endif>
    <a href="{{ route('product.show', [$product->slug] + ($q ?? [])) }}"
       class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 group-hover:border-cyan/50 group-hover:shadow-xl">
        <div class="relative flex h-[176px] items-center justify-center bg-ice p-2 max-sm:h-36">
            @if (! empty($reason))
                <span class="absolute left-3 top-3 z-10 rounded-md border border-cyan/30 bg-white/95 px-2 py-1 font-mono text-[10px] font-medium uppercase tracking-wider text-link shadow-sm max-sm:hidden">{{ $reason }}</span>
            @endif
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                     class="img-load max-h-full max-w-full mix-blend-multiply object-contain transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($product->image) !!} onload="this.classList.add('is-loaded')">
            @else
                <svg class="h-12 w-12 text-slate/25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/>
                </svg>
            @endif
        </div>

        <div class="flex flex-1 flex-col px-[18px] pb-[18px] pt-4 max-sm:p-3">
            @if ($product->category?->brand)
                <p class="font-mono text-[10.5px] font-medium uppercase tracking-[0.16em] text-link max-sm:hidden">{{ $product->category->brand->name }}</p>
            @endif
            <h3 class="mt-1.5 text-base font-bold leading-snug text-navy transition-colors group-hover:text-link max-sm:mt-0 max-sm:text-sm">{{ $product->name }}</h3>
            @if (($showCategory ?? true) && $product->category)
                <p class="mt-1 text-xs text-slate max-sm:hidden">{{ $product->category->name }}</p>
            @endif
            @if ($product->short_description)
                <p class="mt-2 line-clamp-2 text-[13px] leading-relaxed text-slate max-sm:hidden">{{ $product->short_description }}</p>
            @endif
            @if ($specs->count() >= 2)
                <div class="mt-3 grid grid-cols-2 gap-px overflow-hidden rounded-[10px] bg-[#E5EEF3] max-sm:hidden">
                    @foreach ($specs as $label => $value)
                        <div class="min-w-0 bg-ice px-2.5 py-2">
                            <p class="truncate text-[10.5px] text-slate">{{ $label }}</p>
                            <p class="truncate font-mono text-[12.5px] font-medium text-navy">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
            <span class="mt-auto flex h-[34px] items-center pt-4 text-[12.5px] font-semibold text-link max-sm:hidden" style="box-sizing:content-box">View details <span class="ml-1 inline-block transition-transform duration-200 group-hover:translate-x-1">&rarr;</span></span>
        </div>
    </a>
    @include('public.partials.compare-toggle', ['product' => $product, 'card' => true])
    @include('public.partials.enquiry-list-toggle', ['product' => $product, 'card' => true])
</div>
