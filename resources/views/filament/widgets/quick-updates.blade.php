@php
    $links = $this->getLinks();
    $groups = $this->getGroups();
    $items = collect($groups)->flatten(1);
    $pending = $items->where('count', '>', 0);

    // Palette only: coral = needs a reply, cyan = coming up, blue = tidy up, teal = all good
    $tones = [
        'warn' => 'bg-danger-50 text-danger-700 ring-danger-200 dark:bg-danger-500/10 dark:text-danger-400 dark:ring-danger-500/30',
        'info' => 'bg-info-50 text-info-700 ring-info-200 dark:bg-info-500/10 dark:text-info-400 dark:ring-info-500/30',
        'todo' => 'bg-primary-50 text-primary-700 ring-primary-200 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/30',
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Quick updates</x-slot>
        <x-slot name="description">
            {{ $pending->isEmpty() ? 'You are all caught up. Nothing needs attention right now.' : $pending->count() . ' ' . \Illuminate\Support\Str::plural('thing', $pending->count()) . ' need your attention. Click a card to open the list.' }}
        </x-slot>

        {{-- Shortcuts --}}
        <div class="flex flex-wrap gap-2">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-primary-400 hover:text-primary-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200">
                    @if ($loop->last)
                        <svg class="h-4 w-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                    @else
                        <svg class="h-4 w-4 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    @endif
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Cards by group --}}
        @foreach ($groups as $groupName => $cards)
            <div class="mt-6">
                <p class="mb-3 flex items-center gap-2 font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">
                    <span class="h-0.5 w-4 bg-info-500" style="box-shadow: 2px 0 0 #0B2545;"></span>
                    {{ $groupName }}
                </p>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($cards as $card)
                        @php($has = $card['count'] > 0)
                        <a href="{{ $card['url'] }}"
                           class="group flex items-center gap-4 rounded-xl border p-4 transition
                                  {{ $has ? 'border-gray-200 bg-white hover:border-primary-400 hover:shadow-md dark:border-white/10 dark:bg-white/5' : 'border-gray-100 bg-gray-50/60 hover:border-gray-300 dark:border-white/5 dark:bg-white/5' }}">
                            @if ($has)
                                <span class="flex h-11 min-w-11 items-center justify-center rounded-lg px-2 text-lg font-semibold ring-1 ring-inset {{ $tones[$card['tone']] ?? $tones['todo'] }}">{{ $card['count'] }}</span>
                            @else
                                <span class="flex h-11 min-w-11 items-center justify-center rounded-lg bg-success-50 text-success-700 ring-1 ring-inset ring-success-200 dark:bg-success-500/10 dark:text-success-400 dark:ring-success-500/30" title="All good">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                </span>
                            @endif
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold {{ $has ? 'text-gray-900 dark:text-white' : 'text-gray-500' }}">{{ $card['label'] }}</span>
                                <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ $has ? $card['hint'] : 'All good' }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if (empty($groups))
            <p class="mt-5 text-sm text-gray-500">There are no quick updates for your role yet.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
