@php $pinned = $this->getPinned(); @endphp

<x-filament-widgets::widget>
    <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-white/5">
        <span class="mr-1 flex items-center gap-1.5 font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">
            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.48 3.5a.56.56 0 0 1 1.04 0l2.13 5.11a.56.56 0 0 0 .47.35l5.52.44c.5.04.7.66.32.99l-4.2 3.6a.56.56 0 0 0-.18.56l1.28 5.38a.56.56 0 0 1-.84.61l-4.73-2.89a.56.56 0 0 0-.59 0l-4.73 2.89a.56.56 0 0 1-.84-.61l1.28-5.38a.56.56 0 0 0-.18-.56l-4.2-3.6a.56.56 0 0 1 .32-.99l5.52-.44a.56.56 0 0 0 .47-.35L11.48 3.5Z"/></svg>
            My shortcuts
        </span>

        @forelse ($pinned as $item)
            <a href="{{ $item['url'] }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:border-primary-400 hover:text-primary-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                {{ $item['title'] }}
            </a>
        @empty
            <span class="text-sm text-gray-500 dark:text-gray-400">Pin the areas you open every day, so they are one click away.</span>
        @endforelse

        <span class="ml-auto">{{ $this->editShortcutsAction }}</span>
    </div>

    <x-filament-actions::modals />
</x-filament-widgets::widget>
