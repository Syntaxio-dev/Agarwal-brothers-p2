@php
    $rows = $this->getRows();
    $pill = [
        'created' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400',
        'updated' => 'bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-400',
        'deleted' => 'bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400',
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Team activity</x-slot>
        <x-slot name="description">The latest changes made in the admin panel, so everyone knows who did what.</x-slot>

        @if ($rows->isEmpty())
            <p class="text-sm text-gray-500">Nothing yet. Changes made by your team will show up here.</p>
        @else
            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($rows as $row)
                    <div class="flex items-center gap-3 py-3">
                        <span class="w-20 shrink-0 rounded-md px-2 py-1 text-center text-xs font-semibold {{ $pill[$row->action] ?? '' }}">{{ $row->actionLabel() }}</span>
                        <span class="min-w-0 flex-1 text-sm">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $row->user_name }}</span>
                            <span class="text-gray-500">{{ $row->action }} {{ strtolower($row->typeLabel()) }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $row->subject_label }}</span>
                            @if ($row->action === 'updated')
                                <span class="block truncate text-xs text-gray-500">{{ $row->summary() }}</span>
                            @endif
                        </span>
                        <span class="shrink-0 text-xs text-gray-400">{{ $row->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 text-right">
                <a href="{{ $this->getAllUrl() }}" class="font-mono text-xs font-semibold uppercase tracking-widest text-primary-600 hover:underline">See all activity &rarr;</a>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
