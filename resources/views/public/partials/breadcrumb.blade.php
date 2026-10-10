{{-- $items: [['Label', url|null], ...]. The last item is the current page.
     $back (optional): ['label' => 'Back to X', 'url' => fallback url]. Goes back in history when the visitor came from a results page. --}}
@php
    $listItems = collect($items)->values()->map(fn ($item, $i) => array_filter([
        '@type' => 'ListItem',
        'position' => $i + 1,
        'name' => $item[0],
        'item' => ($item[1] ?? null) && $i < count($items) - 1 ? \App\Support\Seo::absolute($item[1]) : null,
    ]))->all();

    $crumbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $listItems];
@endphp

<script type="application/ld+json">{!! json_encode($crumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>

<div class="mb-8 flex flex-wrap items-center justify-between gap-x-4 gap-y-3">
    <nav class="crumbs" aria-label="Breadcrumb">
        @foreach ($items as [$label, $url])
            @if ($url && ! $loop->last)
                <a href="{{ $url }}">
                    @if ($loop->first)
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.126 1.126 0 0 1 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
                    @endif
                    {{ $label }}
                </a>
            @else
                <span>{{ $label }}</span>
            @endif
        @endforeach
    </nav>

    @isset($back)
        <a href="{{ $back['url'] }}" x-data="backLink(@js($back['label']))" @click="go($event)"
           class="chip py-2 pl-3 pr-4">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            <span x-text="label">{{ $back['label'] }}</span>
        </a>
    @endisset
</div>
