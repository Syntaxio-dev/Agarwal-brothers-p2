@php($files = $this->getFiles())

<x-filament-panels::page>
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <p class="font-mono text-[11px] font-semibold uppercase tracking-widest text-primary-600">Saved backups</p>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            A full backup is made automatically every night at 2:30 AM and the newest {{ \App\Support\BackupService::KEEP }} of each kind are kept.
            Download one regularly and store it somewhere other than this server. Backup files contain customer data, so keep them private.
        </p>

        @if (empty($files))
            <p class="mt-6 text-sm text-gray-500">No backups yet. Press "Create backup now".</p>
        @else
            <div class="mt-4 divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($files as $f)
                    <div class="flex flex-wrap items-center gap-3 py-3">
                        <span class="w-28 shrink-0 rounded-md bg-info-50 px-2 py-1 text-center text-xs font-semibold text-primary-700 dark:bg-white/10 dark:text-info-300">{{ $f['type'] }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-mono text-sm text-gray-900 dark:text-white">{{ $f['name'] }}</span>
                            <span class="block text-xs text-gray-500">{{ \Illuminate\Support\Number::fileSize($f['size']) }} &middot; {{ \Illuminate\Support\Carbon::createFromTimestamp($f['at'])->diffForHumans() }}</span>
                        </span>
                        <x-filament::button size="sm" wire:click="download('{{ $f['name'] }}')">Download</x-filament::button>
                        <x-filament::button size="sm" color="danger" outlined wire:click="remove('{{ $f['name'] }}')" wire:confirm="Delete this backup file?">Delete</x-filament::button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
