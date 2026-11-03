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
