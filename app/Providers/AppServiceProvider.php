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
        // The live site only runs on https: build every link and asset address with https, even behind a proxy/CDN.
        if ($this->app->isProduction()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Rate limits for the public site. Every route has its own counter (a visitor who types a search
        // must still be able to send an enquiry), and the forms also have a daily cap so one address
        // cannot flood the office inbox or fill the disk with uploads.
        $per = fn (string $name, int $minute, ?int $day = null) => \Illuminate\Support\Facades\RateLimiter::for($name, fn ($request) => array_values(array_filter([
            \Illuminate\Cache\RateLimiting\Limit::perMinute($minute)->by($name . '|' . $request->ip()),
            $day ? \Illuminate\Cache\RateLimiting\Limit::perDay($day)->by($name . '-day|' . $request->ip()) : null,
        ])));
        $per('enquiry', 5, 30);
        $per('enquiry-list', 5, 30);
        $per('contact', 5, 30);
        $per('careers', 3, 10);
        $per('suggest', 90);
        $per('search', 60);
        $per('compare', 30);

        // The sitemap is cached for an hour; anything that can appear in it clears it, so a hidden page drops out at once.
        foreach ([\App\Models\Product::class, \App\Models\Brand::class, \App\Models\Category::class, \App\Models\Vertical::class, \App\Models\Insight::class, \App\Models\JobOpening::class] as $model) {
            // a block body on purpose: a model listener that returns false stops the other listeners (e.g. the activity log)
            $model::saved(function () { \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::cacheKey()); });
            $model::deleted(function () { \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\SeoController::cacheKey()); });
        }

        // Admin uploads get a random name, and on the public disk the extension comes from the file's real content
        // (a PDF renamed to .html is stored as .pdf). Private uploads (product import) keep a cleaned extension.
        \Filament\Forms\Components\FileUpload::configureUsing(fn (\Filament\Forms\Components\FileUpload $upload) => $upload->getUploadedFileNameForStorageUsing(
            fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file, \Filament\Forms\Components\FileUpload $component): string => \App\Filament\Support\Uploads::storageName($file, (string) $component->getDiskName())
        ));

        // Record who signs in to the admin panel.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, fn ($event) => \App\Support\ActivityLogger::record('login', null, [], $event->user));

        // Dashboard counts are cached for a short time (App\Support\DashboardCache); a change to a record clears its area at once.
        foreach (\App\Support\DashboardCache::AREAS as $model => $area) {
            $model::saved(function () use ($area) { \App\Support\DashboardCache::flush($area); });
            $model::deleted(function () use ($area) { \App\Support\DashboardCache::flush($area); });
        }
    }
}
