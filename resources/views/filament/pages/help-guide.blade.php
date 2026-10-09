@php($topics = $this->getTopics())

<x-filament-panels::page>
    <div x-data="{ open: window.location.hash.slice(1) || '{{ $topics[0]['id'] ?? '' }}' }" class="grid gap-6 lg:grid-cols-[16rem_1fr]">
        <nav class="lg:sticky lg:top-24 lg:self-start">
            <p class="mb-2 font-mono text-[11px] font-semibold uppercase tracking-widest text-gray-500">Topics</p>
            <ul class="space-y-1">
                @foreach ($topics as $t)
                    <li>
                        <a href="#{{ $t['id'] }}"
                           x-on:click="open = '{{ $t['id'] }}'"
                           x-bind:class="open === '{{ $t['id'] }}' ? 'bg-primary-600 text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5'"
                           class="block rounded-lg px-3 py-2 text-sm font-medium transition">
                            {{ $t['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="space-y-6">
            @foreach ($topics as $t)
                <section id="{{ $t['id'] }}" x-show="open === '{{ $t['id'] }}'" x-cloak
                         class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $t['title'] }}</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $t['intro'] }}</p>

                    <p class="mt-5 font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">Steps</p>
                    <ol class="mt-2 space-y-2">
                        @foreach ($t['steps'] as $i => $step)
                            <li class="flex gap-3 text-sm text-gray-800 dark:text-gray-200">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white">{{ $i + 1 }}</span>
                                <span>{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>

                    @if (! empty($t['tips']))
                        <div class="mt-5 rounded-lg bg-info-50 p-4 ring-1 ring-info-200/60 dark:bg-white/5 dark:ring-white/10">
                            <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-gray-600 dark:text-gray-400">Good to know</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-gray-300">
                                @foreach ($t['tips'] as $tip)
                                    <li>{{ $tip }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
