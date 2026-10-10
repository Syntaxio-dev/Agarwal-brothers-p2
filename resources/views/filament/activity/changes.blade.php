@php
    $rows = collect($log->details ?? []);
@endphp

<div class="space-y-3">
    <p class="text-sm text-gray-500">
        {{ $log->created_at->format('d M Y, h:i A') }}
        @if ($log->ip_address) &middot; from {{ $log->ip_address }} @endif
    </p>

    <div class="overflow-x-auto rounded-lg ring-1 ring-gray-950/5 dark:ring-white/10">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/5">
                    @foreach (['Field', 'Before', 'After'] as $head)
                        <th class="px-3 py-2 font-mono text-[11px] font-semibold uppercase tracking-wider text-primary-600">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $field => $change)
                    <tr class="border-t border-gray-100 align-top dark:border-white/5">
                        <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ \App\Models\ActivityLog::fieldLabel($field) }}</td>
                        <td class="px-3 py-2 text-gray-500">
                            @if (($change['old'] ?? null) === null)
                                <span class="text-gray-400">(empty)</span>
                            @else
                                <span class="rounded bg-danger-50 px-1.5 py-0.5 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400">{{ $change['old'] }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @if (($change['new'] ?? null) === null)
                                <span class="text-gray-400">(empty)</span>
                            @else
                                <span class="rounded bg-success-50 px-1.5 py-0.5 text-success-700 dark:bg-success-500/10 dark:text-success-400">{{ $change['new'] }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
