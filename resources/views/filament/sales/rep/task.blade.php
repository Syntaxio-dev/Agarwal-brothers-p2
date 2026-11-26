@php
    use App\Sales\Models\SalesTask;

    $email = $t->send_to_email && filter_var($t->send_to_email, FILTER_VALIDATE_EMAIL) ? $t->send_to_email : null;
    $kb = fn (int $bytes) => $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';
@endphp

<div class="sx-page">
    <div class="sx-card" style="padding:1rem 1.125rem">
        <div style="display:flex;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;align-items:baseline">
            <h2 class="sx-eyebrow">From {{ $t->creator?->name ?? 'the office' }}</h2>
            <span class="mv-chip {{ $t->isOverdue() ? 'mv-missed' : '' }}">{{ SalesTask::STATUSES[$t->status] ?? $t->status }}</span>
        </div>
        <dl class="rp-dl" style="margin-top:0.5rem">
            <div><dt class="sx-eyebrow">Due</dt><dd>{{ $t->due_at ? $t->due_at->timezone($tz)->format('D j M Y, h:i A') : 'No date' }}</dd></div>
            <div><dt class="sx-eyebrow">Priority</dt><dd>{{ SalesTask::PRIORITIES[$t->priority] ?? $t->priority }}</dd></div>
            <div>
                <dt class="sx-eyebrow">Send the result to</dt>
                <dd>
                    @if ($email)
                        <a class="sx-link" href="mailto:{{ $email }}?subject={{ rawurlencode($t->title) }}">{{ $email }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
        @if (filled($t->description))
            <p style="margin-top:0.75rem;white-space:pre-line">{{ $t->description }}</p>
        @endif
        @if ($t->status === 'cancelled')
            <p class="sx-small sx-mute" style="margin-top:0.75rem">The manager cancelled this task. Nothing more to do.</p>
        @endif
    </div>

    @if ($t->status !== 'cancelled')
        <div class="sx-card" style="padding:1rem 1.125rem">
            <h2 class="sx-eyebrow">Files ({{ $t->files->count() }})</h2>
            @forelse ($t->files as $file)
                <a class="ca-row" style="margin-top:0.25rem" href="{{ route('filament.rep.task-file', ['id' => $file->id]) }}">
                    <span class="ca-row-text">
                        <span class="ca-row-name">{{ $file->name }}</span>
                        <span class="ca-row-sub">{{ $kb((int) $file->size) }}</span>
                    </span>
                    <span class="mv-chip">Download</span>
                </a>
            @empty
                <p class="ca-empty">No files with this task.</p>
            @endforelse
        </div>
    @endif

    <div class="sx-card" style="padding:1rem 1.125rem">
        <h2 class="sx-eyebrow">Your note to the manager</h2>
        <p style="margin-top:0.5rem;white-space:pre-line">{{ $t->rep_note ?: 'Nothing written yet. Use "Update".' }}</p>
        @if ($email)
            <p class="sx-small sx-mute" style="margin-top:0.5rem">Finished? Send the file to {{ $email }} from your e-mail, then set the task to Done.</p>
        @endif
    </div>
</div>
