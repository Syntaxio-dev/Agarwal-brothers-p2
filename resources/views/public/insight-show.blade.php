<x-layouts.app :title="$insight->title">
    @if ($insight->image)
        <img src="{{ asset('storage/' . $insight->image) }}" class="w-full max-h-96 object-cover rounded-lg mb-6">
    @endif
    <h1 class="text-3xl font-bold mb-2">{{ $insight->title }}</h1>
    @if ($insight->event_date)
        <div class="text-link mb-4">{{ $insight->event_date->format('d M Y') }}</div>
    @endif
    <div class="prose max-w-none">{!! $insight->content !!}</div>
</x-layouts.app>