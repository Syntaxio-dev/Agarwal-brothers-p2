<?php

namespace App\Sales\Support;

use App\Sales\Models\SalesClient;
use Illuminate\Support\Facades\Http;

/**
 * Turns what a rep copies from Google Maps ("Share > Copy link", or the whole shared text with a short link in it) into the exact spot of
 * the place: the office opens the link and reads the coordinates out of the address Google sends it on to.
 *
 * Safety: the office only ever opens Google Maps addresses (a fixed list of hosts, https only), follows at most 5 redirects itself and checks
 * the host of EVERY step, so nobody can use this to make the server open another address. Nothing is stored.
 */
class MapLinkResolver
{
    /** The only hosts that are ever opened. */
    private const HOSTS = ['maps.app.goo.gl', 'goo.gl', 'g.co', 'maps.google.com', 'google.com', 'www.google.com', 'www.google.co.in', 'google.co.in'];

    private const MAX_HOPS = 5;

    /** @return array{lat: float, lng: float, name: ?string}|null */
    public static function resolve(string $text): ?array
    {
        $text = trim($text);

        // numbers typed or copied as they are need no trip to Google
        if ($spot = SalesClient::parseSpot($text)) {
            return $spot + ['name' => self::nameFrom($text)];
        }

        $url = self::firstUrl($text);

        for ($hop = 0; $url !== null && $hop <= self::MAX_HOPS; $hop++) {
            if (! self::allowed($url)) {
                return null;
            }

            if ($spot = SalesClient::parseSpot($url)) {
                return $spot + ['name' => self::nameFrom($url)];
            }

            try {
                $response = Http::withOptions(['allow_redirects' => false])
                    ->withHeaders(['Accept-Language' => 'en', 'Accept' => 'text/html'])
                    ->withUserAgent('Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Mobile Safari/537.36')
                    ->timeout(8)
                    ->get($url);
            } catch (\Throwable) {
                return null;
            }

            $next = $response->header('Location');

            if ($response->redirect() && $next) {
                $url = self::absolute($url, $next);

                continue;
            }

            // The page of a business: Google's share link carries its name and address and an internal id (0x...:0x...), but no numbers.
            // The same name asked of Google's key-free embed page returns the place with its position; the internal id must match, so the
            // pin is never put on a different shop that has a similar name.
            if ($place = self::placeNamedInUrl($url)) {
                return self::lookUp($place['query'], $place['ftid']);
            }

            // a page, not a redirect: Google puts the place in the page address it served or in the page itself
            $body = substr((string) $response->body(), 0, 400_000);

            foreach ([$body, html_entity_decode($body)] as $candidate) {
                if (preg_match('/!3d(-?\d{1,3}\.\d{3,})!4d(-?\d{1,3}\.\d{3,})/', $candidate, $m)
                    || preg_match('/[?&;]center=(-?\d{1,3}\.\d{3,})(?:%2C|,)(-?\d{1,3}\.\d{3,})/', $candidate, $m)
                    || preg_match('/@(-?\d{1,3}\.\d{3,}),(-?\d{1,3}\.\d{3,})/', $candidate, $m)) {
                    $spot = SalesClient::parseSpot($m[1] . ', ' . $m[2]);

                    return $spot ? $spot + ['name' => self::nameFrom($url)] : null;
                }
            }

            return null;
        }

        return null;
    }

    /** "/maps/place/NAME+AND+ADDRESS/data=!4m2!3m1!1s0x...:0x..." -> the words and the internal id of the place. */
    private static function placeNamedInUrl(string $url): ?array
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        if (! preg_match('~/maps/place/([^/]+)~', $path, $m)) {
            return null;
        }

        $query = trim(preg_replace('/\s+/', ' ', urldecode($m[1])));   // urldecode turns "+" into a space and %2B into a real plus

        if (mb_strlen($query) < 3 || preg_match('/^-?\d+(\.\d+)?\s*,\s*-?\d/', $query)) {
            return null;
        }

        $ftid = preg_match('/!1s(0x[0-9a-f]+:0x[0-9a-f]+)/i', urldecode($path), $f) ? strtolower($f[1]) : null;

        return ['query' => mb_substr($query, 0, 250), 'ftid' => $ftid];
    }

    /**
     * Asks Google's embed page (no key) for a place by its words. Its answer holds the place's internal id and its position; when the share
     * link told us the id it must be the same one. -> { lat, lng, name } or null.
     */
    private static function lookUp(string $query, ?string $ftid): ?array
    {
        try {
            $response = Http::withOptions(['allow_redirects' => false])
                ->withHeaders(['Accept-Language' => 'en', 'Accept' => 'text/html'])
                ->withUserAgent('Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Mobile Safari/537.36')
                ->timeout(8)
                ->get('https://www.google.com/maps/embed?origin=mfe&pb=!1m2!2m1!1s' . urlencode($query));   // where Google's own ?output=embed address leads to
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = substr((string) $response->body(), 0, 400_000);

        // [[<distance>,<longitude>,<latitude>],[0,0,0], ... the place: ["0x...:0x...","<name and address>"
        if (! preg_match('/\[\[-?\d+(?:\.\d+)?,(-?\d{1,3}\.\d{3,}),(-?\d{1,3}\.\d{3,})\],\[0,0,0\]/', $body, $m)) {
            return null;
        }

        $seen = preg_match('/"(0x[0-9a-f]+:0x[0-9a-f]+)","([^"]{0,200})/i', $body, $p) ? strtolower($p[1]) : null;

        if ($ftid !== null && $seen !== null && $ftid !== $seen) {
            return null;
        }

        $spot = SalesClient::parseSpot($m[2] . ', ' . $m[1]);   // the page writes longitude first

        if (! $spot) {
            return null;
        }

        $name = isset($p[2]) ? trim(explode(',', html_entity_decode($p[2]))[0]) : null;
        $name = $name !== null && preg_match('/^[A-Z0-9]{4,8}\+[A-Z0-9]{2,}\s+(.+)$/', $name, $n) ? $n[1] : $name;

        return $spot + ['name' => $name !== '' ? mb_substr((string) $name, 0, 120) : null];
    }

    /** The first https address in a text ("Agarwal Brothers\nSitapura\nhttps://maps.app.goo.gl/abc" -> the link). */
    public static function firstUrl(string $text): ?string
    {
        return preg_match('~https://[^\s<>"\']+~i', $text, $m) ? rtrim($m[0], '.,);') : null;
    }

    private static function allowed(string $url): bool
    {
        $parts = parse_url($url);

        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['port'])) {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');

        if (! in_array($host, self::HOSTS, true)) {
            return false;
        }

        // goo.gl, g.co and google.com carry many things: only the Maps part of them
        if (in_array($host, ['goo.gl', 'g.co'], true)) {
            return str_starts_with($parts['path'] ?? '', '/maps') || $host === 'g.co';
        }

        return $host === 'maps.app.goo.gl' || $host === 'maps.google.com' || str_starts_with($parts['path'] ?? '', '/maps');
    }

    private static function absolute(string $base, string $location): string
    {
        if (preg_match('~^https?://~i', $location)) {
            return $location;
        }

        $parts = parse_url($base);

        return 'https://' . ($parts['host'] ?? '') . '/' . ltrim($location, '/');
    }

    /** "…/maps/place/Agarwal+Brothers/@26.9,75.8…" or the first line of a shared text -> "Agarwal Brothers". */
    private static function nameFrom(string $text): ?string
    {
        if (preg_match('~/maps/place/([^/@?]+)~', $text, $m)) {
            $name = trim(str_replace('+', ' ', urldecode($m[1])));

            return $name !== '' ? mb_substr($name, 0, 120) : null;
        }

        $first = trim(strtok($text, "\n") ?: '');

        return $first !== '' && ! str_starts_with($first, 'http') && ! preg_match('/^-?\d/', $first) ? mb_substr($first, 0, 120) : null;
    }
}
