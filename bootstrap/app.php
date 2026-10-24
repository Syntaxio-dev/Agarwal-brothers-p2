<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global, so the Filament panel and error pages get the headers too.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sales API (/api/sales/*): errors as {code, message, errors}. The website's own error pages are not touched.
        \App\Sales\Support\ApiError::register($exceptions);
    })->create();

// Hosting layout "public_html/laravel_app" (see tools/make-release.php): the web files (index.php, build, css ...)
// sit one folder above the application folder. Then that folder is the public path, for the web AND for artisan
// (storage:link, filament:assets). A normal checkout has its own public/ folder and is not touched.
if (basename(dirname(__DIR__)) === 'laravel_app' && is_file(dirname(__DIR__, 2).'/index.php')) {
    $app->usePublicPath(dirname(__DIR__, 2));
}

return $app;
