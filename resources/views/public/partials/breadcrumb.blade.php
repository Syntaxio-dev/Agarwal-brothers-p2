{{-- $items: [['Label', url|null], ...]. The last item is the current page. --}}
<nav class="crumbs mb-8" aria-label="Breadcrumb">
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
