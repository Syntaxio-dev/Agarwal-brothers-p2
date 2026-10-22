@php
    $rows = $this->getRows();
    $max = max(1, (int) $rows->max('count'));
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Most asked-about products</x-slot>
        <x-slot name="description">Products customers enquired about most in the last {{ $this->getDays() }} days. Good ones to stock, feature on the home page or price well.</x-slot>

        @if ($rows->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No product enquiries in this period yet.</p>
        @else
            <ol class="space-y-3">
                @foreach ($rows as $row)
                    @php $url = $this->url($row['id']); @endphp
                    <li class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $loop->first ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">{{ $loop->iteration }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline justify-between gap-3">
                                @if ($url)
                                    <a href="{{ $url }}" class="min-w-0 truncate text-sm font-medium text-gray-950 hover:text-primary-600 dark:text-white">{{ $row['name'] }}</a>
                                @else
                                    <span class="min-w-0 truncate text-sm font-medium text-gray-950 dark:text-white">{{ $row['name'] }}</span>
                                @endif
                                <span class="shrink-0 text-sm font-semibold text-gray-950 dark:text-white">{{ $row['count'] }} {{ $row['count'] === 1 ? 'enquiry' : 'enquiries' }}</span>
                            </div>
                            @if ($row['brand'])
                                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $row['brand'] }}</span>
                            @endif
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                <div class="h-full rounded-full bg-info-500" style="width: {{ max(6, round($row['count'] / $max * 100)) }}%"></div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
