<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Short-lived cache for the dashboard counts, so the dashboard does not run ~25 COUNT queries on every visit.
 *
 * Keys look like "dash.enquiries.v3.new". Every area has its own version number: when a record of that
 * area is saved or deleted (see AppServiceProvider), the version goes up and the old keys are simply never
 * read again, so an edit shows on the dashboard at once. The TTL is only a safety net.
 */
class DashboardCache
{
    public const TTL = 90;   // seconds

    /** Which model belongs to which area (the same area keys as config/roles.php). */
    public const AREAS = [
        \App\Models\Enquiry::class => 'enquiries',
        \App\Models\ContactMessage::class => 'contact-messages',
        \App\Models\JobApplication::class => 'job-applications',
        \App\Models\Insight::class => 'insights',
        \App\Models\Product::class => 'products',
        \App\Models\Brand::class => 'brands',
        \App\Models\Slide::class => 'slides',
    ];

    public static function remember(string $area, string $name, Closure $count): int
    {
        return (int) Cache::remember(static::key($area, $name), static::TTL, $count);
    }

    public static function key(string $area, string $name): string
    {
        return 'dash.' . $area . '.v' . static::version($area) . '.' . $name;
    }

    /** Forget every cached count of an area (called when one of its records changes). */
    public static function flush(string $area): void
    {
        Cache::forever(static::versionKey($area), static::version($area) + 1);
    }

    private static function version(string $area): int
    {
        return (int) Cache::get(static::versionKey($area), 1);
    }

    private static function versionKey(string $area): string
    {
        return 'dash.' . $area . '.version';
    }
}
