<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Record who signs in to the admin panel.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, fn ($event) => \App\Support\ActivityLogger::record('login', null, [], $event->user));

        //
    }
}
