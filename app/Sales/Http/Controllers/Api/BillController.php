<?php

namespace App\Sales\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Sales\Models\SalesExpense;
use App\Sales\Support\ApiError;
use App\Sales\Support\ImageType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Bill photos travel as base64 inside JSON (the same way both ways): that works the same in the phone's native HTTP client
 * and in a browser, with no multipart handling. The picture is judged by its real content, never by the name or type the
 * phone claims, and it is stored on the PRIVATE disk: it can only be seen through an authorised request.
 */
class BillController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $maxBytes = (int) config('sales.bill_max_kb') * 1024;

        $data = $request->validate([
            'expense_uuid' => ['required', 'uuid'],
            // base64 grows the file by a third: refuse an obviously too large text before decoding it
            'data_base64' => ['required', 'string', 'max:' . (int) ceil($maxBytes * 4 / 3) + 16],
        ]);

        $expense = SalesExpense::where('uuid', $data['expense_uuid'])->where('user_id', $request->user()->id)->first();

        if (! $expense) {
            return ApiError::response('NOT_FOUND', 'This expense is not on the server yet.', 404);
        }
        if (! $expense->isPending()) {
            return ApiError::response('LOCKED', 'This expense was already reviewed and its bill can no longer be changed.', 409);
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

        $path = "sales/bills/{$expense->user_id}/{$expense->uuid}.{$type['ext']}";

        if ($expense->bill_path && $expense->bill_path !== $path) {
            Storage::disk('local')->delete($expense->bill_path);
        }

        Storage::disk('local')->put($path, $bytes);
        $expense->forceFill(['bill_path' => $path, 'bill_mime' => $type['mime']])->save();

        return response()->json(['status' => 'stored']);
    }

    /** The phone asks for a bill it does not have (a new phone after a restore): only its own. */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $expense = SalesExpense::where('uuid', $uuid)->where('user_id', $request->user()->id)->first();

        if (! $expense?->bill_path || ! Storage::disk('local')->exists($expense->bill_path)) {
            return ApiError::response('NOT_FOUND', 'No bill photo for this expense.', 404);
        }

        return response()->json([
            'mime' => $expense->bill_mime,
            'data_base64' => base64_encode(Storage::disk('local')->get($expense->bill_path)),
        ]);
    }
}
