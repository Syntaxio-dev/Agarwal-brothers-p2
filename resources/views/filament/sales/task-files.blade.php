{{-- The files already on a task, with a download link and a Remove button (the upload box below adds more). Text only. --}}
@if ($files->isNotEmpty())
    <div style="display:grid;gap:.5rem">
        <p style="font-weight:600">Files on this task ({{ $files->count() }})</p>
        @foreach ($files as $file)
            <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.4rem 0;border-top:1px solid rgba(128,128,128,.25)">
                <a href="{{ route('sales.task-file', $file->id) }}" style="text-decoration:underline;overflow-wrap:anywhere">{{ $file->name }}</a>
                <span style="opacity:.7;font-size:.85rem;white-space:nowrap">{{ number_format($file->size / 1024, 0) }} KB</span>
                <button type="button" wire:click="removeFile({{ $file->id }})" wire:confirm="Remove {{ $file->name }} from this task?" style="color:#b42318;font-size:.85rem">Remove</button>
            </div>
        @endforeach
    </div>
@endif
