<x-layouts.app title="Agarwal Brothers">
    <div class="rounded-xl overflow-hidden bg-navy text-white mb-10">
        <div x-data="{ active: 0, total: {{ $slides->count() }} }"
            x-init="setInterval(() => active = (active + 1) % total, 4000)"
            class="relative rounded-xl overflow-hidden bg-navy text-white mb-10 h-64">
            @foreach ($slides as $i => $slide)
            <a href="{{ $slide->link_url ?? '#' }}" x-show="active === {{ $i }}" x-transition
                class="absolute inset-0 flex items-center justify-center text-center px-8" @if ($slide->image)
                style="background-image: url('{{ asset('storage/' . $slide->image) }}'); background-size: cover;
                background-position: center;" @endif>
                @if ($slide->video)
                <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover">
                    <source src="{{ asset('storage/' . $slide->video) }}" type="video/mp4">
                </video>
                @endif
                <div class="bg-navy/60 p-6 rounded-lg">
                    <h1 class="text-2xl font-bold mb-2">{{ $slide->title }}</h1>
                    <p class="text-white/80">{{ $slide->subtitle }}</p>
                </div>
            </a>
            @endforeach

            @if ($slides->isEmpty())
            <div class="h-full flex items-center justify-center text-white/50">No slides added yet</div>
            @endif
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4">Our Scientific Verticals</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($verticals as $vertical)
        <a href="{{ route('vertical.show', $vertical->slug) }}" class="bg-white rounded-lg shadow p-4 hover:shadow-md">
            @if ($vertical->image)
            <img src="{{ asset('storage/' . $vertical->image) }}" class="w-full h-24 object-cover rounded mb-2">
            @endif
            <div class="font-semibold">{{ $vertical->name }}</div>
            <div class="text-slate text-sm">{{ $vertical->description }}</div>
        </a>
        @endforeach
    </div>
    <h2 class="text-xl font-bold mb-4 mt-12">Our Trusted Clients</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-6 items-center">
        @foreach ($clients as $client)
        <div class="bg-white rounded-lg shadow p-4 flex items-center justify-center">
            @if ($client->logo)
            <img src="{{ asset('storage/' . $client->logo) }}" class="max-h-12 object-contain">
            @else
            <span class="text-slate text-sm text-center">{{ $client->name }}</span>
            @endif
        </div>
        @endforeach
    </div>
</x-layouts.app>