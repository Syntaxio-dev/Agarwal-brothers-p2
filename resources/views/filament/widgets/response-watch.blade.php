@php
    $oldest = $this->getOldest();
    $late = $oldest && $oldest['hours'] > $this->getTargetHours();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Oldest waiting</x-slot>
        <x-slot name="description">The enquiry or message nobody has answered for the longest time.</x-slot>

        @if ($oldest)
            <a href="{{ $oldest['url'] }}"
               class="group flex items-center gap-4 rounded-xl border p-4 transition {{ $late ? 'border-danger-200 bg-danger-50 hover:border-danger-400 dark:border-danger-500/30 dark:bg-danger-500/10' : 'border-gray-200 bg-gray-50 hover:border-gray-300 dark:border-white/10 dark:bg-white/5' }}">
                <span class="flex h-12 min-w-12 items-center justify-center rounded-lg px-2 text-sm font-semibold {{ $late ? 'bg-danger-600 text-white' : 'bg-white text-gray-700 ring-1 ring-inset ring-gray-300 dark:bg-white/10 dark:text-gray-200 dark:ring-white/20' }}">
                    {{ $this->human($oldest['hours']) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $oldest['name'] }}</span>
                    <span class="mt-0.5 block text-xs text-gray-600 dark:text-gray-400">
                        {{ $oldest['type'] }} received {{ $oldest['since']->timezone('Asia/Kolkata')->format('j M, g:i A') }}
                        @if ($late) <span class="font-semibold text-danger-600 dark:text-danger-400">· later than the {{ $this->human((float) $this->getTargetHours()) }} target</span> @endif
                    </span>
                </span>
                <svg class="h-4 w-4 shrink-0 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
            </a>
        @else
            <div class="flex items-center gap-3 rounded-xl border border-success-200 bg-success-50 p-4 text-sm font-medium text-success-700 dark:border-success-500/30 dark:bg-success-500/10 dark:text-success-400">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                Nothing is waiting. Every enquiry and message has been looked at.
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
