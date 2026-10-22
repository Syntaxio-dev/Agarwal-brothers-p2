@php
    $health = $this->getHealth();
    $stale = $this->getStale();
    $bar = $health['percent'] >= 90 ? 'bg-success-500' : ($health['percent'] >= 60 ? 'bg-info-500' : 'bg-danger-500');
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Catalogue health</x-slot>
        <x-slot name="description">A live product is complete when it has a photo, specifications and an overview.</x-slot>

        <div>
            <div class="flex items-end justify-between gap-3">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    <span class="text-2xl font-semibold text-gray-950 dark:text-white">{{ $health['complete'] }}</span>
                    of {{ $health['total'] }} live {{ $health['total'] === 1 ? 'product is' : 'products are' }} complete
                </p>
                <span class="text-lg font-semibold text-gray-950 dark:text-white">{{ $health['percent'] }}%</span>
            </div>
            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10" role="progressbar" aria-valuenow="{{ $health['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full {{ $bar }}" style="width: {{ $health['percent'] }}%"></div>
            </div>
            @if ($health['complete'] < $health['total'])
                <a href="{{ $this->listUrl() }}" class="mt-2 inline-block text-xs font-medium text-primary-600 hover:underline">See the live products and fix the missing parts</a>
            @endif
        </div>

        <div class="mt-6 border-t border-gray-100 pt-4 dark:border-white/5">
            <p class="text-sm font-semibold text-gray-950 dark:text-white">Not edited for {{ $this->getMonths() }} months</p>
            @if ($stale['count'] === 0)
                <p class="mt-1 text-sm text-success-600 dark:text-success-400">Nothing is stale. Every live product was updated recently.</p>
            @else
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stale['count'] }} live {{ $stale['count'] === 1 ? 'product' : 'products' }}. The oldest ones:</p>
                <ul class="mt-2 divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($stale['rows'] as $product)
                        <li class="flex items-center justify-between gap-3 py-2 text-sm">
                            <a href="{{ $this->editUrl($product->id) }}" class="min-w-0 truncate font-medium text-gray-950 hover:text-primary-600 dark:text-white">{{ $product->name }}</a>
                            <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ $product->updated_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
