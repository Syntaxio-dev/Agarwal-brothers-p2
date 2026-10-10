<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vertical;
use Illuminate\Support\Facades\Cache;

/**
 * Helps the visitor who mistypes: if a search finds nothing, a word that is close to a real word in our catalogue
 * ("centrifuze" for "centrifuges") is swapped in, so the visitor still reaches the right product.
 */
class SearchAssist
{
    /** Words people can find: product, brand, product line and vertical names. word => how many names use it. */
    public static function vocabulary(): array
    {
        return Cache::remember('search_vocabulary', 600, function () {
            $names = collect()
                ->merge(Product::where('is_active', true)->pluck('name'))
                ->merge(Brand::where('is_active', true)->pluck('name'))
                ->merge(Category::pluck('name'))
                ->merge(Vertical::where('is_active', true)->pluck('name'));

            $words = [];
            foreach ($names as $name) {
                foreach (self::tokens((string) $name) as $word) {
                    if (mb_strlen($word) >= 3) {
                        $words[$word] = ($words[$word] ?? 0) + 1;
                    }
                }
            }

            return $words;
        });
    }

    /** @return string[] lower-case words of a text */
    public static function tokens(string $text): array
    {
        return preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** The query with every misspelt word replaced by its closest real word, or null when nothing needed fixing. */
    public static function correct(string $query, ?array $vocabulary = null): ?string
    {
        $vocabulary ??= self::vocabulary();
        $tokens = self::tokens($query);

        if (! $vocabulary || ! $tokens) {
            return null;
        }

        $changed = false;
        $result = [];

        foreach ($tokens as $token) {
            $fixed = self::closest($token, $vocabulary);
            $changed = $changed || $fixed !== $token;
            $result[] = $fixed;
        }

        return $changed ? implode(' ', $result) : null;
    }

    private static function closest(string $token, array $vocabulary): string
    {
        $length = mb_strlen($token);

        // short words, real words and the beginning of a real word ("centrif") are left alone
        if ($length < 4 || isset($vocabulary[$token])) {
            return $token;
        }
        foreach ($vocabulary as $word => $count) {
            if (str_contains((string) $word, $token)) {
                return $token;
            }
        }

        $allowed = $length <= 5 ? 1 : ($length <= 8 ? 2 : 3);
        $best = null;
        $bestScore = PHP_INT_MAX;

        foreach ($vocabulary as $word => $count) {
            $word = (string) $word;

            if (abs(mb_strlen($word) - $length) > $allowed) {
                continue;
            }

            $distance = levenshtein($token, $word);

            if ($distance > $allowed) {
                continue;
            }

            // closer is better; same first letter is better; a word used by many names is slightly better
            $score = $distance * 10 + (mb_substr($word, 0, 1) === mb_substr($token, 0, 1) ? 0 : 5) - min($count, 3);

            if ($score < $bestScore) {
                $bestScore = $score;
                $best = $word;
            }
        }

        return $best ?? $token;
    }
}
