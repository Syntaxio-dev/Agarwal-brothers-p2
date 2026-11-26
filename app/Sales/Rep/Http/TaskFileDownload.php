<?php

namespace App\Sales\Rep\Http;

use App\Sales\Models\SalesTask;
use App\Sales\Models\SalesTaskFile;
use App\Sales\Rep\Support\RepDesk;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A file of a task, for the web desk: only the rep the task was given to, only while Tasks is open for him, only while the task is not
 * cancelled (the same rule as the phone's download). Always a download (never shown inside the page), and never cached by a shared cache.
 */
class TaskFileDownload
{
    public function __invoke(int $id): StreamedResponse
    {
        $user = auth()->user();
        RepDesk::mustAllow($user, 'tasks');

        $file = SalesTaskFile::query()->whereKey($id)
            ->whereIn('task_id', SalesTask::query()->select('id')->where('assigned_to', $user->id)->where('status', '!=', 'cancelled'))
            ->first();

        abort_unless($file && Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->name, [
            'Content-Type' => $file->mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
