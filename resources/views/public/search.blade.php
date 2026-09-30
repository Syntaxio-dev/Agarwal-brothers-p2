<x-layouts.app title="Search Results">
    <h1 class="text-2xl font-bold mb-6">Search results for "{{ $query }}"</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($products as $product)
            <a href="{{ route('product.show', $product->slug) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-32 object-cover rounded mb-2">
                @endif
                <div class="font-semibold">{{ $product->name }}</div>
                <div class="text-slate text-sm">{{ $product->category->brand->name }}</div>
            </a>
        @endforeach
    </div>

    @if ($products->isEmpty())
        <p class="text-slate">No products found.</p>
    @endif
</x-layouts.app>