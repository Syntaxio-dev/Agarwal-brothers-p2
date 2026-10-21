@php
    $groups = $this->getGroups();
    $everyone = $this->getEveryone();
@endphp

<x-filament-widgets::widget>
    <x-filament::section collapsible persist-collapsed id="admin-features">
        <x-slot name="heading">What you can do in this admin panel</x-slot>
        <x-slot name="description">
            {{ $this->getRoleText() ?: 'Everything your role can open is listed here.' }} Click a name to open that area.
        </x-slot>

        @foreach ($groups as $groupName => $items)
            <div class="{{ $loop->first ? '' : 'mt-6' }}">
                <p class="mb-3 flex items-center gap-2 font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">
                    <span class="h-0.5 w-4 bg-info-500" style="box-shadow: 2px 0 0 #0B2545;"></span>
                    {{ $groupName }}
                </p>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($items as $item)
                        <a href="{{ $item['url'] }}"
                           class="group rounded-xl border border-gray-200 bg-white p-4 transition hover:border-primary-400 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                            <span class="flex items-center justify-between gap-2 text-sm font-semibold text-gray-900 group-hover:text-primary-700 dark:text-white">
                                {{ $item['title'] }}
                                <svg class="h-4 w-4 shrink-0 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </span>
                            <span class="mt-1.5 block text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $item['text'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="mt-6">
            <p class="mb-3 flex items-center gap-2 font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">
                <span class="h-0.5 w-4 bg-info-500" style="box-shadow: 2px 0 0 #0B2545;"></span>
                For everyone
            </p>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach ($everyone as [$title, $text])
                    <div class="rounded-xl border border-gray-100 bg-gray-50/60 p-4 dark:border-white/5 dark:bg-white/5">
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $title }}</span>
                        <span class="mt-1 block text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $text }}</span>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                Some areas (users, site settings, backups, email templates, activity log) are only for administrators, so you may not see every group above.
                <a href="{{ \App\Support\AdminFeatures::helpUrl() }}" class="font-medium text-primary-600 hover:underline">Open the full how-to guide</a>
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
