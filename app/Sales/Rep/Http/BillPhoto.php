<?php

namespace App\Sales\Rep\Http;

use App\Sales\Models\SalesExpense;
use App\Sales\Rep\Support\RepDesk;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The bill photo of one of the signed-in rep's OWN expenses, for the web desk (Expenses open). Another rep's bill does not exist here (404).
 * Only a picture type is ever served inline; nothing is cached by a shared cache.
 */
class BillPhoto
{
    private const IMAGES = ['image/jpeg', 'image/png', 'image/webp'];

    public function __invoke(string $uuid): StreamedResponse
    {
        $user = auth()->user();
        RepDesk::mustAllow($user, 'expenses');

        $expense = SalesExpense::query()->where('uuid', $uuid)->where('user_id', $user->id)->first();
        abort_unless($expense && $expense->bill_path && Storage::disk('local')->exists($expense->bill_path), 404);

        $mime = in_array($expense->bill_mime, self::IMAGES, true) ? $expense->bill_mime : 'application/octet-stream';

        return Storage::disk('local')->response($expense->bill_path, null, [
            'Content-Type' => $mime,
            'Content-Disposition' => $mime === 'application/octet-stream' ? 'attachment' : 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
