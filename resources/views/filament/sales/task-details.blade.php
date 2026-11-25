{{-- One task for the manager: what was asked, to whom, where the result goes, and where the rep has got to. Text only: nothing here is ever HTML. --}}
<div style="display:grid;gap:1rem">
    <div>
        <p style="font-weight:600">{{ $task->assignee?->name }} <span style="opacity:.7;font-weight:400">&middot; {{ $task->assignee?->salesProfile?->employee_code }}</span></p>
        <p style="opacity:.7;font-size:.875rem">
            Given by {{ $task->creator?->name ?? 'the office' }} &middot; {{ $task->created_at?->timezone(config('sales.timezone'))->format('d M Y, h:i a') }}
            @if ($task->due_at) &middot; due {{ $task->due_at->timezone(config('sales.timezone'))->format('d M Y, h:i a') }}@if ($task->isOverdue()) (late)@endif @endif
            &middot; {{ \App\Sales\Models\SalesTask::PRIORITIES[$task->priority] ?? $task->priority }} priority
        </p>
    </div>

    @if ($task->description)
        <div><p style="font-weight:600;margin-bottom:.25rem">What to do</p><p style="white-space:pre-line">{{ $task->description }}</p></div>
    @endif

    @if ($task->send_to_email)
        <div><p style="font-weight:600;margin-bottom:.25rem">Send the result to</p><a href="mailto:{{ $task->send_to_email }}" style="text-decoration:underline">{{ $task->send_to_email }}</a></div>
    @endif

    <div>
        <p style="font-weight:600;margin-bottom:.25rem">Status: {{ \App\Sales\Models\SalesTask::STATUSES[$task->status] ?? $task->status }}</p>
        @if ($task->rep_note)
            <p style="white-space:pre-line">{{ $task->rep_note }}</p>
            <p style="opacity:.7;font-size:.875rem">{{ $task->rep_updated_at?->timezone(config('sales.timezone'))->format('d M Y, h:i a') }}</p>
        @else
            <p style="opacity:.7">The rep has not written anything yet.</p>
        @endif
    </div>

    <div>
        <p style="font-weight:600;margin-bottom:.25rem">Files ({{ $task->files->count() }})</p>
        @forelse ($task->files as $file)
            <p><a href="{{ route('sales.task-file', $file->id) }}" style="text-decoration:underline">{{ $file->name }}</a> <span style="opacity:.7">&middot; {{ number_format($file->size / 1024, 0) }} KB</span></p>
        @empty
            <p style="opacity:.7">No files.</p>
        @endforelse
    </div>
</div>
