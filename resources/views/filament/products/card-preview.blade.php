@php
    /** @var \App\Models\Product|null $product */
    $product = $getRecord();
    $brand = $product?->category?->brand;
    $live = $product && $product->is_active && $brand?->is_active;
    $specCount = count((array) ($product?->specs ?? []));

    // [label, ok?, hint when not ok]
    $checks = $product ? [
        ['Photo', filled($product->image), 'Upload a main image so the card does not look empty.'],
        ['Short description', filled($product->short_description), 'Add one or two lines; they show on the card.'],
        ['Specifications', $specCount > 0, 'Add specifications so customers (and the compare page) can use them.'],
        ['Visible to visitors', $live, $product->is_active ? 'The brand is switched off, so this product is hidden.' : 'Switch Active on when it is ready.'],
    ] : [];
@endphp

@if ($product)
    <div class="grid items-start gap-6 md:grid-cols-[17rem_1fr]">
        {{-- The card exactly as it appears on the category page --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex h-44 items-center justify-center bg-[#F4F9FB] p-5">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                @else
                    <span class="font-mono text-[11px] uppercase tracking-[0.18em] text-gray-400">No photo yet</span>
                @endif
            </div>
            <div class="p-5">
                <p class="font-mono text-[11px] font-medium uppercase tracking-[0.16em] text-[#0077B6]">{{ $brand?->name }}</p>
                <h4 class="mt-1 text-base font-bold leading-snug text-[#0B2545] dark:text-white">{{ $product->name }}</h4>
                @if ($product->short_description)
                    <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-gray-500">{{ $product->short_description }}</p>
                @endif
                <span class="mt-3 inline-block text-xs font-semibold text-[#0077B6]">View details &rarr;</span>
            </div>
        </div>

        {{-- Quick check --}}
        <div>
            <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">Quick check</p>
            <p class="mt-1 text-sm text-gray-500">This is the saved version. After you press Save, open "View on site" at the top to see the real page.</p>

            <ul class="mt-4 space-y-2">
                @foreach ($checks as [$label, $ok, $hint])
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $ok ? 'bg-success-100 text-success-700 dark:bg-success-500/20' : 'bg-danger-100 text-danger-700 dark:bg-danger-500/20' }}">
                            @if ($ok)
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            @else
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 3.5h.01"/></svg>
                            @endif
                        </span>
                        <span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $label }}{{ $label === 'Specifications' && $ok ? ' (' . $specCount . ')' : '' }}</span>
                            @unless ($ok)
                                <span class="block text-xs text-gray-500">{{ $hint }}</span>
                            @endunless
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
