@php
    $links = $this->getPrimaryLinks();
    $count = $this->getAttentionCount();
    $webinars = $this->getWebinars();
@endphp

<x-filament-widgets::widget>
    <div class="flex flex-col gap-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6 dark:border-white/10 dark:bg-white/5">
        <div class="min-w-0">
            <h2 class="text-xl font-semibold tracking-tight text-gray-950 sm:text-2xl dark:text-white">{{ $this->getGreeting() }} <span aria-hidden="true">👋</span></h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if ($count > 0)
                    <span class="font-semibold text-danger-600 dark:text-danger-400">{{ $count }} {{ \Illuminate\Support\Str::plural('thing', $count) }} need attention</span>
                @else
                    <span class="font-semibold text-success-600 dark:text-success-400">You are all caught up</span>
                @endif
                <span aria-hidden="true"> · </span>{{ $this->getDateText() }}
            </p>
        </div>

        @if ($links)
            <div class="flex flex-wrap gap-2">
                @foreach ($links as $link)
                    <x-filament::button tag="a" :href="$link['url']" :color="$loop->first ? 'primary' : 'gray'" :outlined="! $loop->first" icon="heroicon-m-plus">
                        {{ $link['label'] }}
                    </x-filament::button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Webinars in the next 7 days: easy to miss in the list below, so they sit right under the greeting --}}
    @if ($webinars->isNotEmpty())
        <div class="mt-3 rounded-2xl border border-info-200 bg-info-50 px-5 py-4 dark:border-info-500/30 dark:bg-info-500/10">
            <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-info-700 dark:text-info-400">Coming up in the next 7 days</p>
            <ul class="mt-2 grid gap-2 md:grid-cols-2">
                @foreach ($webinars as $webinar)
                    <li>
                        <a href="{{ \App\Filament\Resources\Insights\InsightResource::getUrl('edit', ['record' => $webinar]) }}" class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-info-100 transition hover:ring-info-400 dark:bg-white/5 dark:ring-white/10">
                            <span class="min-w-0 truncate font-medium text-gray-950 dark:text-white">{{ $webinar->title }}</span>
                            <span class="shrink-0 text-xs text-gray-600 dark:text-gray-400">{{ $webinar->start_ist->format('D j M, g:i A') }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">Check the date, the registration link and the page before the day.</p>
        </div>
    @endif
</x-filament-widgets::widget>
