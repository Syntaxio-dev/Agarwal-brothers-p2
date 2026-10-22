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

        // Dashboard counts are cached for a short time (App\Support\DashboardCache); a change to a record clears its area at once.
        foreach (\App\Support\DashboardCache::AREAS as $model => $area) {
            $model::saved(fn () => \App\Support\DashboardCache::flush($area));
            $model::deleted(fn () => \App\Support\DashboardCache::flush($area));
        }
    }
}
