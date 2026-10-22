@php
    $rows = $this->getRows();
    $max = max(1, (int) $rows->max('open'));
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Team workload</x-slot>
        <x-slot name="description">Open enquiries (not closed) per person, and how long the oldest one has been waiting.</x-slot>

        @if ($rows->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No open enquiries. Nothing is waiting on anybody.</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach ($rows as $row)
                    <li class="py-3">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="min-w-0 truncate font-medium {{ $row['unassigned'] ? 'text-danger-600 dark:text-danger-400' : 'text-gray-950 dark:text-white' }}">
                                {{ $row['unassigned'] ? 'Not assigned to anybody' : $row['name'] }}
                            </span>
                            <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">
                                oldest {{ $this->human(round($row['oldest']->diffInMinutes(now()) / 60, 1)) }}
                            </span>
                            <span class="shrink-0 text-sm font-semibold text-gray-950 dark:text-white">{{ $row['open'] }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                            <div class="h-full rounded-full {{ $row['unassigned'] ? 'bg-danger-500' : 'bg-primary-500' }}" style="width: {{ max(4, round($row['open'] / $max * 100)) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
            <a href="{{ $this->getListUrl() }}" class="mt-4 inline-block text-xs font-medium text-primary-600 hover:underline">Open the enquiry list</a>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
