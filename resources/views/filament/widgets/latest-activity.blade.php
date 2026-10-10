@php
    $rows = $this->getRows();
    $pill = [
        'warning' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400',
        'info' => 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-400',
        'success' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Latest activity</x-slot>
        <x-slot name="description">The newest enquiries, messages and applications. Click one to open it.</x-slot>

        @if ($rows->isEmpty())
            <p class="text-sm text-gray-500">Nothing yet. New enquiries, messages and applications will show up here.</p>
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($rows as $row)
                    <a href="{{ $row['url'] }}" class="flex items-center gap-3 py-3 transition hover:bg-gray-50 dark:hover:bg-white/5 sm:px-2 sm:-mx-2 sm:rounded-lg">
                        <span class="w-24 shrink-0 rounded-md px-2 py-1 text-center text-xs font-semibold {{ $pill[$row['tone']] ?? '' }}">{{ $row['type'] }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $row['title'] }}</span>
                            <span class="block truncate text-xs text-gray-500">{{ $row['detail'] }}</span>
                        </span>
                        @if ($row['status'] === 'new')
                            <span class="hidden rounded-full bg-danger-500 px-2 py-0.5 text-[11px] font-bold uppercase text-white sm:inline">New</span>
                        @endif
                        <span class="shrink-0 text-xs text-gray-400">{{ $row['at']->diffForHumans() }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
