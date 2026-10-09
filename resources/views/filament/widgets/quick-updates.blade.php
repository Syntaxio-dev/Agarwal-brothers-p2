@php
    $items = $this->getItems();
    $pending = collect($items)->where('count', '>', 0);
    $tones = [
        'warn' => ['dot' => 'bg-amber-500', 'badge' => 'bg-amber-100 text-amber-800 ring-amber-200'],
        'info' => ['dot' => 'bg-sky-500', 'badge' => 'bg-sky-100 text-sky-800 ring-sky-200'],
        'todo' => ['dot' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200'],
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Quick updates</x-slot>
        <x-slot name="description">
            {{ $pending->isEmpty() ? 'You are all caught up. Nothing needs attention right now.' : $pending->count() . ' ' . \Illuminate\Support\Str::plural('thing', $pending->count()) . ' need your attention.' }}
        </x-slot>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $item)
                @php $t = $tones[$item['tone']] ?? $tones['todo']; $has = $item['count'] > 0; @endphp
                <a href="{{ $item['url'] }}"
                   class="group flex items-center gap-4 rounded-xl border p-4 transition
                          {{ $has ? 'border-gray-200 bg-white hover:border-primary-400 hover:shadow-md dark:border-white/10 dark:bg-white/5' : 'border-gray-100 bg-gray-50/60 opacity-70 hover:opacity-100 dark:border-white/5 dark:bg-white/5' }}">
                    <span class="flex h-11 min-w-11 items-center justify-center rounded-lg px-2 text-lg font-semibold ring-1 ring-inset {{ $has ? $t['badge'] : 'bg-gray-100 text-gray-400 ring-gray-200' }}">
                        {{ $item['count'] }}
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $item['label'] }}</span>
                        <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ $item['hint'] }}</span>
                    </span>
                    <svg class="h-4 w-4 shrink-0 text-gray-400 transition group-hover:translate-x-0.5 group-hover:text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/></svg>
                </a>
            @endforeach
        </div>

        @if (empty($items))
            <p class="text-sm text-gray-500">There are no quick updates for your role yet.</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
