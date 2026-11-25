<?php

namespace App\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sales\Models\SalesTask;
use App\Sales\Models\SalesTaskFile;
use App\Sales\Support\ApiError;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * A file of a task, for the rep it was given to (base64 in JSON, like a bill photo: it works the same in the phone's HTTP client). Only the
 * assigned rep gets it, only while the task is not cancelled; the file lives on the private disk.
 */
class TaskFileController extends Controller
{
    public function show(Request $request, string $uuid, int $file): JsonResponse
    {
        $task = SalesTask::where('uuid', $uuid)->where('assigned_to', $request->user()->id)->where('status', '!=', 'cancelled')->first();
        $row = $task ? SalesTaskFile::where('task_id', $task->id)->where('id', $file)->first() : null;

        if (! $row || ! Storage::disk('local')->exists($row->path)) {
            return ApiError::response('NOT_FOUND', 'This file is not available.', 404);
        }

        return response()->json([
            'name' => $row->name,
            'mime' => $row->mime,
            'data_base64' => base64_encode(Storage::disk('local')->get($row->path)),
        ]);
    }
}
