<x-layouts.app :title="$vertical->name">
    <div class="w-[98%] mx-auto px-4 md:px-10 lg:px-20 py-10 sm:py-14 lg:py-16">

        {{-- Breadcrumb --}}
        <div class="text-sm text-slate mb-6">
            <a href="/" class="hover:text-link transition">Home</a>
            <span class="mx-1.5">/</span>
            <a href="/verticals" class="hover:text-link transition">Verticals</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ $vertical->name }}</span>
        </div>

        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-navy">{{ $vertical->name }}</h1>
        @if ($vertical->description)
            <p class="mt-3 text-slate max-w-3xl leading-relaxed">{{ $vertical->description }}</p>
        @endif

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($vertical->categories as $category)
                <a href="{{ route('category.show', [$category->brand->slug, $category->slug]) }}"
                    class="group flex flex-col overflow-hidden rounded-2xl bg-white
                           ring-1 ring-gray-100 shadow-sm
                           hover:shadow-lg hover:ring-cyan/30 hover:-translate-y-1
                           transition-all duration-300">

                    <div class="h-36 overflow-hidden bg-gray-50">
                        @if ($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @endif
                    </div>

                    <div class="p-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan">{{ $category->brand->name }}</p>
                        <h3 class="mt-1 text-base font-bold text-navy group-hover:text-link transition-colors">
                            {{ $category->name }}
                        </h3>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($vertical->categories->isEmpty())
            <div class="mt-10 text-center py-16 text-slate">
                <p class="text-lg">No categories available in this vertical yet.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
