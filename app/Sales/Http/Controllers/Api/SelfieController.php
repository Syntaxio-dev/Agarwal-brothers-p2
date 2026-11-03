<?php

namespace App\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sales\Models\SalesMeeting;
use App\Sales\Support\ApiError;
use App\Sales\Support\ImageType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The selfie of a check-in travels like a bill photo: base64 inside JSON, judged by its real content, stored on the PRIVATE disk.
 * A rep can only send and read the selfie of their own meeting.
 */
class SelfieController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $maxBytes = (int) config('sales.bill_max_kb') * 1024;

        $data = $request->validate([
            'meeting_uuid' => ['required', 'uuid'],
            'data_base64' => ['required', 'string', 'max:' . (int) ceil($maxBytes * 4 / 3) + 16],
        ]);

        $meeting = SalesMeeting::where('uuid', $data['meeting_uuid'])->where('user_id', $request->user()->id)->first();

        if (! $meeting) {
            return ApiError::response('NOT_FOUND', 'This meeting is not on the server yet.', 404);
        }
        if (! $meeting->checkin_at) {
            return ApiError::response('INVALID', 'This meeting has no check-in.', 422);
        }

        $bytes = base64_decode($data['data_base64'], true);

        if ($bytes === false || $bytes === '') {
            return ApiError::response('INVALID', 'The picture could not be read.', 422);
        }
        if (strlen($bytes) > $maxBytes) {
            return ApiError::response('PAYLOAD_TOO_LARGE', 'The picture is bigger than ' . config('sales.bill_max_kb') / 1024 . ' MB.', 413);
        }

        $type = ImageType::detect($bytes);

        if (! $type) {
            return ApiError::response('INVALID', 'Only JPG, PNG or WebP pictures are accepted.', 422);
        }

        $path = "sales/selfies/{$meeting->user_id}/{$meeting->uuid}.{$type['ext']}";

        if ($meeting->selfie_path && $meeting->selfie_path !== $path) {
            Storage::disk('local')->delete($meeting->selfie_path);
        }

        Storage::disk('local')->put($path, $bytes);
        $meeting->forceFill(['selfie_path' => $path, 'selfie_mime' => $type['mime']])->save();

        return response()->json(['status' => 'stored']);
    }

    /** A new phone asks for a selfie it does not have: only its own. */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $meeting = SalesMeeting::where('uuid', $uuid)->where('user_id', $request->user()->id)->first();

        if (! $meeting?->selfie_path || ! Storage::disk('local')->exists($meeting->selfie_path)) {
            return ApiError::response('NOT_FOUND', 'No selfie for this meeting.', 404);
        }

        return response()->json([
            'mime' => $meeting->selfie_mime,
            'data_base64' => base64_encode(Storage::disk('local')->get($meeting->selfie_path)),
        ]);
    }
}
