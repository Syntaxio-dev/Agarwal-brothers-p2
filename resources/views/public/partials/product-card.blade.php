{{-- One product card (picture, brand, name, compare + enquiry buttons). Needs $product. --}}
    <div data-reveal class="group transition-all duration-300 hover:-translate-y-1 relative flex">
    <a href="{{ route('product.show', $product->slug) }}"
       class="w-full group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 group-hover:border-cyan/50 group-hover:shadow-xl">
        <div class="relative flex h-48 items-center justify-center bg-ice p-5">
            @if (! empty($reason))
                <span class="absolute left-3 top-3 z-10 rounded-md border border-cyan/30 bg-white/95 px-2 py-1 font-mono text-[10px] font-medium uppercase tracking-wider text-link shadow-sm">{{ $reason }}</span>
            @endif
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                     class="img-load max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-105" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($product->image) !!} onload="this.classList.add('is-loaded')">
            @else
                <svg class="h-12 w-12 text-slate/25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z"/>
                </svg>
            @endif
        </div>

        <div class="flex flex-1 flex-col p-5">
            @if ($product->category?->brand)
                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-link">{{ $product->category->brand->name }}</p>
            @endif
            <h3 class="mt-1.5 text-base font-bold leading-snug text-navy transition-colors group-hover:text-link">{{ $product->name }}</h3>
            @if ($product->category)
                <p class="mt-1 text-xs text-slate">{{ $product->category->name }}</p>
            @endif
            @if ($product->short_description)
                <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate">{{ $product->short_description }}</p>
            @endif
            <span class="mt-auto pt-4 text-xs font-semibold text-link">View details <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">&rarr;</span></span>
        </div>
    </a>
        @include('public.partials.compare-toggle', ['product' => $product])
        @include('public.partials.enquiry-list-toggle', ['product' => $product])
    </div>
