{{-- A brand with its logo: picture area, name and number of product lines. Needs $brand (with categories_count). --}}
<a data-reveal href="{{ route('brand.show', $brand->slug) }}"
   class="edge-top group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-cyan/50 hover:shadow-xl">
    <div class="flex h-24 items-center justify-center bg-ice p-4">
        @if ($brand->logo)
            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }} logo"
                 class="img-load max-h-full max-w-full object-contain transition-transform duration-500 group-hover:scale-110" loading="lazy" decoding="async" {!! \App\Support\Img::attrs($brand->logo) !!} onload="this.classList.add('is-loaded')">
        @else
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-navy to-link text-lg font-bold text-white transition-transform duration-500 group-hover:scale-110">{{ \Illuminate\Support\Str::substr($brand->name, 0, 2) }}</span>
        @endif
    </div>
    <div class="flex items-center justify-between gap-2 px-4 py-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-bold text-navy transition-colors group-hover:text-link">{{ $brand->name }}</p>
            <p class="font-mono text-[11px] text-slate">{{ $brand->categories_count }} {{ \Illuminate\Support\Str::plural('line', $brand->categories_count) }}</p>
        </div>
        <span class="shrink-0 text-link transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true">&rarr;</span>
    </div>
</a>
