<?php

use App\Sales\Http\Controllers\Api\AuthController;
use App\Sales\Http\Controllers\Api\BillController;
use App\Sales\Http\Controllers\Api\SyncController;
use App\Sales\Http\Middleware\EnsureActiveRep;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sales API, served under /api/sales/v1 (see App\Sales\SalesServiceProvider)
|--------------------------------------------------------------------------
| JSON only. Errors always look like {"code", "message", "errors"}. The contract is written down in docs/sales-api.md.
*/

Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:sales-login');

Route::middleware(['auth:sanctum', EnsureActiveRep::class, 'throttle:sales-api'])->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::post('sync/push', [SyncController::class, 'push']);
    Route::get('sync/pull', [SyncController::class, 'pull']);

    Route::get('expenses/{uuid}/bill', [BillController::class, 'show'])->whereUuid('uuid');
    Route::post('uploads/bill', [BillController::class, 'store'])->middleware('throttle:sales-upload');
});
