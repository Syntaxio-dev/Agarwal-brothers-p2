<x-layouts.app :title="$category->name">
    <div class="text-sm text-slate mb-2">
        <a href="/" class="hover:text-link">Home</a> /
        {{ $brand->name }} / {{ $category->name }}
    </div>
    <h1 class="text-3xl font-bold mb-1">{{ $category->name }}</h1>
    <p class="text-slate mb-8">{{ $brand->name }}</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($category->products as $product)
            <a href="{{ route('product.show', $product->slug) }}"
               class="bg-white rounded-lg shadow p-4 hover:shadow-md">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-32 object-cover rounded mb-2">
                @endif
                <div class="font-semibold">{{ $product->name }}</div>
                <div class="text-slate text-sm">{{ $product->short_description }}</div>
            </a>
        @endforeach
    </div>
</x-layouts.app>