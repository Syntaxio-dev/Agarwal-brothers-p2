<?php

namespace App\Support;

use App\Models\Product;
use App\Models\ProductComparison;
use Illuminate\Support\Collection;

/**
 * "Similar products" and "Customers also compared" for a product page.
 *
 * Similar = alike in purpose and specifications, and it may come from another brand: someone looking at one
 * maker's analytical balance also sees other makers' balances with similar capacity and readability.
 */
class SimilarProducts
{
    /** Needed to be listed at all (same product type is 4, a shared vertical alone is only 2). */
    private const MIN_SCORE = 4.0;

    /** Words in a product line's name that say nothing about what it is. */
    private const FILLER_WORDS = ['and', 'lab', 'laboratory', 'system', 'systems', 'equipment', 'instrument', 'instruments', 'solution', 'solutions',
        'series', 'standard', 'models', 'model', 'basic', 'for', 'the'];

    /**
     * @return Collection<int, array{product: Product, reason: string}>
     */
    public static function for(Product $product, int $limit = 4): Collection
    {
        $product->loadMissing('category.brand', 'category.verticals');

        $myType = self::typeWords($product->category?->name);
        $myVerticals = $product->category?->verticals->pluck('id')->all() ?? [];
        $mySpecs = self::specs($product);

        $scored = self::visible()
            ->where('products.id', '!=', $product->id)
            ->with('category.brand', 'category.verticals')
            ->limit(400)
            ->get()
            ->map(function (Product $other) use ($product, $myType, $myVerticals, $mySpecs) {
                $score = 0.0;
                $reason = null;

                $sameLine = $other->category_id === $product->category_id;
                $sameType = ! $sameLine && $myType && array_intersect($myType, self::typeWords($other->category?->name));
                $otherBrand = $other->category?->brand_id !== $product->category?->brand_id;

                if ($sameType) {
                    $score += 4;
                    $reason = $otherBrand ? 'Same type, other brand' : 'Same type';
                } elseif ($sameLine) {
                    $score += 3;
                    $reason = 'Same product line';
                }

                if ($myVerticals && array_intersect($myVerticals, $other->category?->verticals->pluck('id')->all() ?? [])) {
                    $score += 2;
                }

                // alike specifications count only with two shared spec names (a lone "Capacity" says nothing),
                // or when the product type / line / vertical already matches
                [$overlap, $closeness, $sharedCount] = self::compareSpecs($mySpecs, self::specs($other));
                if ($sharedCount >= 2 || $score > 0) {
                    $specScore = $overlap * $closeness * 6;
                    $score += $specScore;
                    if ($specScore >= 3) {
                        $reason ??= 'Similar specifications';
                    }
                }

                if ($score > 0 && $otherBrand) {
                    $score += 1; // alternatives from another maker are the point
                }

                return ['product' => $other, 'score' => $score, 'reason' => $reason ?? 'Related product'];
            })
            ->filter(fn ($row) => $row['score'] >= self::MIN_SCORE)
            ->sortBy([['score', 'desc'], fn ($a, $b) => strcmp($a['product']->name, $b['product']->name)])
            ->take($limit)
            ->values();

        // not enough alike products: top up with others from the same product line
        if ($scored->count() < $limit) {
            $filler = self::visible()
                ->where('products.id', '!=', $product->id)
                ->whereNotIn('products.id', $scored->pluck('product.id'))
                ->where('products.category_id', $product->category_id)
                ->with('category.brand')
                ->limit($limit - $scored->count())
                ->get()
                ->map(fn (Product $p) => ['product' => $p, 'score' => 0, 'reason' => 'Same product line']);

            $scored = $scored->concat($filler)->values();
        }

        return $scored->map(fn ($row) => ['product' => $row['product'], 'reason' => $row['reason']]);
    }

    /** Products that visitors compared with this one (at least two separate visits), most often first. */
    public static function alsoCompared(Product $product, int $limit = 4): Collection
    {
        $ids = ProductComparison::where('product_id', $product->id)
            ->where('times', '>=', 2)
            ->orderByDesc('times')
            ->limit($limit)
            ->pluck('other_product_id');

        if ($ids->isEmpty()) {
            return collect();
        }

        return self::visible()
            ->whereIn('products.id', $ids)
            ->with('category.brand')
            ->get()
            ->sortBy(fn (Product $p) => $ids->search($p->id))
            ->values();
    }

    /** Remember that two products were compared (once per visitor session, no personal data). */
    public static function recordComparison(Collection $products, array &$seen): void
    {
        $ids = $products->pluck('id')->sort()->values();

        foreach ($ids as $i => $a) {
            foreach ($ids->slice($i + 1) as $b) {
                $key = $a . '-' . $b;

                if (in_array($key, $seen, true)) {
                    continue;
                }
                $seen[] = $key;

                foreach ([[$a, $b], [$b, $a]] as [$x, $y]) {
                    ProductComparison::upsert(
                        [['product_id' => $x, 'other_product_id' => $y, 'times' => 1, 'created_at' => now(), 'updated_at' => now()]],
                        ['product_id', 'other_product_id'],
                        ['times' => \Illuminate\Support\Facades\DB::raw('times + 1'), 'updated_at' => now()]
                    );
                }
            }
        }

        $seen = array_slice($seen, -60);
    }

    private static function visible()
    {
        return Product::query()
            ->where('products.is_active', true)
            ->whereHas('category.brand', fn ($b) => $b->where('brands.is_active', true));
    }

    /** The meaningful words of a product line's name, singular ("Weighing Balances" -> weighing, balance). */
    private static function typeWords(?string $name): array
    {
        $words = [];

        foreach (SearchAssist::tokens((string) $name) as $word) {
            if (mb_strlen($word) < 4 || in_array($word, self::FILLER_WORDS, true)) {
                continue;
            }
            $words[] = mb_strlen($word) > 4 && str_ends_with($word, 's') ? mb_substr($word, 0, -1) : $word;
        }

        return array_values(array_unique($words));
    }

    private static function normal(?string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', mb_strtolower((string) $text)));
    }

    /** @return array<string, string> lower-case spec name => value */
    private static function specs(Product $product): array
    {
        $out = [];

        foreach ((array) ($product->specs ?? []) as $label => $value) {
            if (is_scalar($value) && filled($label) && filled($value)) {
                $out[self::normal((string) $label)] = trim((string) $value);
            }
        }

        return $out;
    }

    /**
     * How much two spec lists have in common.
     *
     * @return array{0: float, 1: float, 2: int} [share of spec names both have (0..1), how close the shared numbers are (0..1), number of shared names]
     */
    private static function compareSpecs(array $a, array $b): array
    {
        if (! $a || ! $b) {
            return [0.0, 0.0, 0];
        }

        $shared = array_intersect_key($a, $b);
        $overlap = count($shared) / max(1, count($a + $b));

        $closeness = [];
        foreach ($shared as $label => $value) {
            $x = self::number($value);
            $y = self::number($b[$label]);

            if ($x !== null && $y !== null && max(abs($x), abs($y)) > 0) {
                $closeness[] = 1 - min(1, abs($x - $y) / max(abs($x), abs($y)));
            } elseif (self::normal($value) === self::normal($b[$label])) {
                $closeness[] = 1.0;
            }
        }

        return [$overlap, $closeness ? array_sum($closeness) / count($closeness) : 0.0, count($shared)];
    }

    private static function number(string $value): ?float
    {
        return preg_match('/-?\d+(?:[.,]\d+)?/', $value, $m) ? (float) str_replace(',', '.', $m[0]) : null;
    }
}
