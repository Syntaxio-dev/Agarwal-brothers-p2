<x-layouts.app title="Explore Our Scientific Verticals">
    <h1 class="text-3xl font-bold mb-2">Explore Our Scientific Verticals</h1>
    <p class="text-slate mb-8">From research to production, discover how our solutions support every lab need.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($verticals as $vertical)
            <a href="{{ route('vertical.show', $vertical->slug) }}"
               class="bg-white rounded-lg shadow p-4 hover:shadow-md">
                @if ($vertical->image)
                    <img src="{{ asset('storage/' . $vertical->image) }}" class="w-full h-24 object-cover rounded mb-2">
                @endif
                <div class="font-semibold">{{ $vertical->name }}</div>
                <div class="text-slate text-sm">{{ $vertical->description }}</div>
            </a>
        @endforeach
    </div>
</x-layouts.app>