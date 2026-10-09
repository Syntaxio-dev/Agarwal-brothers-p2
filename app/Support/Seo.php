<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

class Seo
{
    public const SITE = 'Agarwal Brothers';

    public const DEFAULT_TITLE = 'Laboratory Equipment Supplier in India';

    public const DEFAULT_DESCRIPTION = 'Agarwal Brothers: 43+ years supplying laboratory equipment, analytical instruments and chemicals to labs across India. Partner of 50+ global brands.';

    /** "{Page name} | Agarwal Brothers", keeping the page part a sensible length. */
    public static function title(?string $page): string
    {
        $page = trim((string) $page);

        if ($page === '') {
            return self::DEFAULT_TITLE . ' | ' . self::SITE;
        }

        if (Str::contains($page, self::SITE)) {
            return $page;
        }

        return Str::limit($page, 60, '…') . ' | ' . self::SITE;
    }

    /** Plain text, whitespace collapsed, cut on a word boundary to 160 characters at most. */
    public static function description(?string $text, ?string $fallback = null): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8'))));

        if ($clean === '') {
            $clean = $fallback ?? self::DEFAULT_DESCRIPTION;
        }

        if (mb_strlen($clean) <= 160) {
            return $clean;
        }

        $cut = mb_substr($clean, 0, 157);
        $space = mb_strrpos($cut, ' ');

        if ($space !== false && $space > 100) {
            $cut = mb_substr($cut, 0, $space);
        }

        return rtrim($cut, " ,.;:-") . '…';
    }

    /** Absolute URL based on APP_URL (never hardcoded). */
    public static function absolute(?string $pathOrUrl): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $pathOrUrl = (string) $pathOrUrl;

        if (preg_match('~^https?://~i', $pathOrUrl)) {
            return $pathOrUrl;
        }

        return $base . '/' . ltrim($pathOrUrl, '/');
    }

    /** Absolute URL for a file on the public disk (storage/...). */
    public static function storage(?string $path): ?string
    {
        return $path ? self::absolute('storage/' . ltrim($path, '/')) : null;
    }

    /** Share image: the page's own image, else the admin default, else the logo. */
    public static function image(?string $pageImage = null): string
    {
        if ($pageImage) {
            return self::absolute($pageImage);
        }

        $default = SiteSetting::current()?->default_og_image;

        return $default ? self::storage($default) : self::absolute('sidebar-logo.png');
    }

    /** Canonical URL: APP_URL + current path, no query string (so ?v=... pages don't duplicate). */
    public static function canonical(): string
    {
        $path = '/' . trim(request()->getPathInfo(), '/');
        $base = rtrim((string) config('app.url'), '/');

        return $path === '/' ? $base . '/' : $base . $path;
    }

    public static function indexable(): bool
    {
        return app()->isProduction();
    }
}
