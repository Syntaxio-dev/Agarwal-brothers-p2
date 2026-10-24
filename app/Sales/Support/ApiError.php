<?php

namespace App\Sales\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * The one error shape of the Sales API: {"code": "...", "message": "...", "errors": {...}}.
 * `code` is stable and meant for the app (ACCOUNT_INACTIVE, INVALID_CREDENTIALS ...); `message` is for people.
 */
class ApiError
{
    public static function response(string $code, string $message, int $status, array $errors = [], array $headers = []): JsonResponse
    {
        return response()->json(['code' => $code, 'message' => $message, 'errors' => (object) $errors], $status, $headers);
    }

    public static function isApi(Request $request): bool
    {
        return $request->is('api/sales/*');
    }

    /** Called from bootstrap/app.php. Only requests under /api/sales/ are touched; the website keeps its own error pages. */
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e) => static::isApi($request) || $request->expectsJson());

        $exceptions->render(function (ValidationException $e, Request $request) {
            return static::isApi($request)
                ? static::response('VALIDATION_FAILED', 'Some fields are not valid.', 422, $e->errors())
                : null;
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return static::isApi($request)
                ? static::response('UNAUTHENTICATED', 'Please sign in again.', 401)
                : null;
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            return static::isApi($request)
                ? static::response('FORBIDDEN', 'You are not allowed to do this.', 403)
                : null;
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            return static::isApi($request)
                ? static::response('NOT_FOUND', 'Not found.', 404)
                : null;
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! static::isApi($request)) {
                return null;
            }

            $status = $e->getStatusCode();
            [$code, $message] = match ($status) {
                403 => ['FORBIDDEN', 'You are not allowed to do this.'],
                404 => ['NOT_FOUND', 'Not found.'],
                405 => ['METHOD_NOT_ALLOWED', 'This method is not allowed here.'],
                413 => ['PAYLOAD_TOO_LARGE', 'The request is too large.'],
                429 => ['TOO_MANY_REQUESTS', 'Too many requests. Please wait a moment and try again.'],
                default => ['HTTP_' . $status, $status >= 500 ? 'Server error.' : 'The request could not be processed.'],
            };

            return static::response($code, $message, $status, [], $e->getHeaders());
        });

        // Anything unexpected: reported as usual (log), but the phone only sees a plain message, never a stack trace.
        $exceptions->render(function (Throwable $e, Request $request) {
            return static::isApi($request) && ! config('app.debug')
                ? static::response('SERVER_ERROR', 'Server error. Please try again later.', 500)
                : null;
        });
    }
}
