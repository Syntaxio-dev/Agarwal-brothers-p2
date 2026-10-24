<?php

namespace App\Sales\Http\Middleware;

use App\Sales\Support\ApiError;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs on every signed-in API call. A rep who was switched off (or whose account is gone) is blocked at once:
 * 403 ACCOUNT_INACTIVE, all their tokens are revoked, and the app signs out of the office with a clear message.
 */
class EnsureActiveRep
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $profile = $user?->salesProfile;

        if (! $user || ! $profile?->canUseApp() || ! $user->tokenCan('sales:rep')) {
            $user?->tokens()->delete();

            return ApiError::response('ACCOUNT_INACTIVE', 'Your account is switched off. Please contact your manager.', 403);
        }

        return $next($request);
    }
}
