<x-layouts.app :title="$title">
    <h1 class="text-3xl font-bold mb-8">{{ $title }}</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($insights as $insight)
            <a href="{{ route('insights.show', $insight->slug) }}" class="bg-white rounded-lg shadow overflow-hidden hover:shadow-md">
                @if ($insight->image)
                    <img src="{{ asset('storage/' . $insight->image) }}" class="w-full h-40 object-cover">
                @endif
                <div class="p-4">
                    @if ($insight->event_date)
                        <div class="text-sm text-link mb-1">{{ $insight->event_date->format('d M Y') }}</div>
                    @endif
                    <div class="font-semibold mb-1">{{ $insight->title }}</div>
                    <div class="text-slate text-sm">{{ $insight->excerpt }}</div>
                </div>
            </a>
        @endforeach

        @if ($insights->isEmpty())
            <p class="text-slate">Nothing here yet.</p>
        @endif
    </div>
</x-layouts.app>