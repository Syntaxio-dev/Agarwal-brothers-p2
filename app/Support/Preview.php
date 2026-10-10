<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * "View on site" from the admin panel: signed-in staff can open pages that are not live yet
 * (inactive products, drafts, hidden brands) by adding ?preview=1. Visitors never can.
 */
class Preview
{
    public static function active(?Request $request = null): bool
    {
        $request ??= request();

        return $request->boolean('preview') && (bool) $request->user()?->isStaff();
    }

    /** Public URL for the admin "View on site" button: previews are only requested when the page is not live. */
    public static function url(string $url, bool $live): string
    {
        return $live ? $url : $url . (str_contains($url, '?') ? '&' : '?') . 'preview=1';
    }
}
