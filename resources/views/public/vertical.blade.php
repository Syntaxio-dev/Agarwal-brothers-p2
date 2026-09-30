<x-layouts.app :title="$vertical->name">
    <h1 class="text-3xl font-bold mb-2">{{ $vertical->name }}</h1>
    <p class="text-slate mb-8">{{ $vertical->description }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($vertical->categories as $category)
            <a href="{{ route('category.show', [$category->brand->slug, $category->slug]) }}"
               class="bg-white rounded-lg shadow p-4 hover:shadow-md">
                <div class="font-semibold">{{ $category->brand->name }}</div>
                <div class="text-slate text-sm">{{ $category->name }}</div>
            </a>
        @endforeach
    </div>
</x-layouts.app>