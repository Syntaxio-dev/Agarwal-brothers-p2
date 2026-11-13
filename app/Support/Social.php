<?php

namespace App\Support;

use App\Rules\SafeLink;

/**
 * The company's own profile pages (LinkedIn, YouTube ...), read from config/contact.php.
 * Only real, safe https links are returned, so the footer never shows a dead "#" icon and
 * Google's company data lists only pages that exist.
 */
class Social
{
    /** @return array<string, string> network => address, only the ones that are set and safe */
    public static function links(): array
    {
        $out = [];

        foreach ((array) config('contact.social', []) as $network => $url) {
            $url = trim((string) $url);

            if (preg_match('~^https://~i', $url) && SafeLink::passes($url)) {
                $out[$network] = $url;
            }
        }

        return $out;
    }
}
