<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vertical;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The site search (results page and live suggestions).
 *
 * - Every word may sit anywhere: name, page heading, model group, brand, product line, specifications, texts
 *   ("aczet analytical balance", "balance aczet" and "analytical balances" all work).
 * - Model numbers match with or without spaces and dashes: "CY224C" = "CY 224C" = "cy-224c"; a model listed in a
 *   series' "Model" row ("BSA224S-CW") finds that series. An unknown model ("BSA-2240ion") falls back to the closest
 *   real model prefix ("bsa224").
 * - Specifications: "readability 0.001" finds the products whose READABILITY row says 0.001 (a range such as
 *   "0.001 to 0.01" counts too, ranked lower). A unit is understood: "readability 1 mg" also finds "Readability (g): 0.001".
 * - Nothing found: misspelt words are fixed (SearchAssist), and as a last step the products that match most of the
 *   words are shown, marked as partial.
 *
 * The index of all visible products is cached and cleared whenever a product, product line, brand or vertical changes.
 */
class CatalogueSearch
{
    public const CACHE_KEY = 'catalogue_search_index';

    /** Words that only add noise to a catalogue search ("aczet balance price in jaipur"). */
    private const STOP = ['the', 'a', 'an', 'and', 'or', 'of', 'for', 'with', 'in', 'on', 'to', 'by', 'at', 'is', 'best', 'price',
        'prices', 'pricing', 'buy', 'online', 'india', 'indian', 'jaipur', 'rajasthan', 'supplier', 'suppliers', 'dealer', 'dealers',
        'distributor', 'distributors', 'near', 'me', 'cost', 'rate', 'rates', 'quote', 'lab', 'labs', 'laboratory', 'model', 'models',
        'specs', 'specification', 'specifications', 'details', 'what', 'which', 'having', 'has', 'have', 'need', 'want'];

    /** Mass units => grams. */
    private const MASS = ['µg' => 1e-6, 'ug' => 1e-6, 'mcg' => 1e-6, 'mg' => 1e-3, 'g' => 1.0, 'gm' => 1.0, 'gms' => 1.0,
        'gram' => 1.0, 'grams' => 1.0, 'kg' => 1000.0, 'kgs' => 1000.0];

    /** Spec words people use for another spec name ("accuracy 0.1 mg" usually means readability). */
    private const SYNONYMS = [
        'accuracy' => ['readability', 'accuracy'],
        'resolution' => ['readability', 'resolution'],
        'least' => ['readability'],
        'max' => ['capacity', 'max'],
        'maximum' => ['capacity', 'maximum'],
        'weighing' => ['capacity', 'weighing'],
    ];

    /** Spec words that pair with a number even though they also appear in product or line names ("High Capacity Balance"). */
    private const CORE_SPEC_WORDS = ['readability', 'capacity', 'repeatability', 'linearity', 'pan', 'resolution', 'accuracy',
        'temperature', 'speed', 'rpm', 'volume', 'calibration', 'stabilization', 'response', 'sensitivity', 'tare', 'minimum',
        'max', 'maximum', 'least', 'weighing', 'power', 'range'];

    private static ?array $memo = null;

    /** Results of one search are kept this long (seconds); any catalogue change makes them stale at once (version key). */
    public const RESULT_TTL = 600;

    private const VERSION_KEY = 'catalogue_search_version';

    private static bool $warmQueued = false;

    /**
     * @return array{products: Collection, brands: Collection, lines: Collection, verticals: Collection,
     *               corrected: ?string, partial: bool, matches: array<int, string[]>}
     */
    public static function run(string $query, int $limitProducts, int $limitBrands, int $limitLines, int $limitVerticals, bool $allowFix = true): array
    {
        // The same search again (every key press of the live box, the results page after the box, other visitors):
        // only ids are cached, so the answer comes without loading the index or matching anything.
        $key = 'catalogue_search_q:' . self::version() . ':' . md5(json_encode([
            trim(mb_strtolower($query)), $limitProducts, $limitBrands, $limitLines, $limitVerticals, $allowFix,
        ]));
        $found = Cache::remember($key, self::RESULT_TTL, fn () => self::find($query, $limitProducts, $limitBrands, $limitLines, $limitVerticals, $allowFix));

        $ids = array_keys($found['hits']);
        $products = $ids
            ? Product::with('category.brand')->whereIn('id', $ids)->where('is_active', true)->get()
                ->sortBy(fn ($p) => array_search($p->id, $ids))->values()
            : collect();

        return [
            'products' => $products,
            'brands' => self::load(Brand::class, $found['brands'], $limitBrands, fn ($q) => $q->withCount('categories')),
            'lines' => self::load(Category::class, $found['lines'], $limitLines, fn ($q) => $q->with('brand')),
            'verticals' => self::load(Vertical::class, $found['verticals'], $limitVerticals),
            'corrected' => $found['corrected'],
            'partial' => $found['partial'],
            'matches' => $found['hits'],
        ];
    }

    /** The matching itself: ids only (product id => matched spec names), so the result is small enough to cache. */
    private static function find(string $query, int $limitProducts, int $limitBrands, int $limitLines, int $limitVerticals, bool $allowFix): array
    {
        $index = self::index();
        $parsed = self::parse($query, $index['specVocab'], $index['titleVocab']);

        $hits = self::matchProducts($parsed, $index['products'], false);
        $corrected = null;
        $partial = false;

        // An unknown model number: try the closest real one ("bsa2240ion" -> "bsa224").
        if ($allowFix && ! $hits && $parsed['modelCompact'] !== null) {
            foreach (self::modelPrefixes($parsed['modelCompact']) as $shorter) {
                $again = self::matchProducts(self::parse($shorter, $index['specVocab'], $index['titleVocab']), $index['products'], false);
                if ($again) {
                    [$hits, $corrected] = [$again, $shorter];
                    break;
                }
            }
        }

        $others = self::matchOthers($parsed, $index);

        // A misspelt word: the same search with the closest catalogue word ("centrifuze" -> "centrifuges").
        if ($allowFix && ! $hits && ! array_filter($others)) {
            $fixed = SearchAssist::correct($query);

            if ($fixed !== null && $fixed !== mb_strtolower($query)) {
                $fixedParsed = self::parse($fixed, $index['specVocab'], $index['titleVocab']);
                $fixedHits = self::matchProducts($fixedParsed, $index['products'], false);
                $fixedOthers = self::matchOthers($fixedParsed, $index);

                if ($fixedHits || array_filter($fixedOthers)) {
                    [$hits, $others, $corrected, $parsed] = [$fixedHits, $fixedOthers, $fixed, $fixedParsed];
                }
            }
        }

        // Last step: the products that match most of what was typed.
        if ($allowFix && ! $hits && $parsed['termCount'] >= 2) {
            $hits = self::matchProducts($parsed, $index['products'], true);
            $partial = (bool) $hits;
        }

        uasort($hits, fn ($a, $b) => [$b['score'], $a['name']] <=> [$a['score'], $b['name']]);
        $hits = array_slice($hits, 0, $limitProducts, true);

        return [
            'hits' => array_map(fn ($h) => $h['specs'], $hits),
            'brands' => array_slice($others['brands'], 0, $limitBrands),
            'lines' => array_slice($others['lines'], 0, $limitLines),
            'verticals' => array_slice($others['verticals'], 0, $limitVerticals),
            'corrected' => $corrected,
            'partial' => $partial,
        ];
    }

    /** Changes whenever the catalogue changes, so every cached search result is stale at once. */
    private static function version(): string
    {
        return (string) Cache::rememberForever(self::VERSION_KEY, fn () => bin2hex(random_bytes(4)));
    }

    public static function forget(): void
    {
        self::$memo = null;
        self::store()->forget(self::CACHE_KEY);
        Cache::forget(self::VERSION_KEY);
        Cache::forget('search_vocabulary');

        // Build the new index right after the page that changed the catalogue has been sent (once per request,
        // even when an import saves 100 products), so no visitor waits for it.
        if (! self::$warmQueued && ! app()->runningUnitTests() && ! app()->runningInConsole()) {
            self::$warmQueued = true;
            app()->terminating(function () {
                self::$warmQueued = false;
                try {
                    self::$memo = null;
                    self::index();
                    SearchAssist::vocabulary();
                } catch (\Throwable $e) {
                    report($e);
                }
            });
        }
    }

    /* ------------------------------------------------------------------ text helpers */

    public static function normalize(?string $text): string
    {
        $t = mb_strtolower(html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $t = str_replace(['μ', '×', '–', '—', '±', '⁻', 'Ø', 'ø'], ['µ', ' x ', '-', '-', ' ', '-', ' ', ' '], $t);

        return preg_replace('/(?<=\d),(?=\d{3}(?!\d))/u', '', $t);   // 2,200 -> 2200
    }

    /** @return string[] words and numbers ("0.001" stays one token, "cy 224c" -> cy, 224c) */
    public static function tokens(string $normalized): array
    {
        $t = preg_replace('/[^\p{L}\p{N}.]+/u', ' ', $normalized);
        $t = preg_replace('/(?<!\d)\.|\.(?!\d)/u', ' ', $t);

        return array_values(array_filter(explode(' ', $t), fn ($w) => $w !== ''));
    }

    public static function compact(string $normalized): string
    {
        return preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized);
    }

    private static function stem(string $word): string
    {
        $len = mb_strlen($word);

        if ($len > 4 && str_ends_with($word, 'ies')) {
            return mb_substr($word, 0, -3) . 'y';
        }
        if ($len > 4 && preg_match('/(ss|x|ch|sh)es$/u', $word)) {
            return mb_substr($word, 0, -2);
        }
        if ($len > 3 && str_ends_with($word, 's') && ! str_ends_with($word, 'ss')) {
            return mb_substr($word, 0, -1);
        }

        return $word;
    }

    private static function isNumber(string $token): bool
    {
        return (bool) preg_match('/^\d+(\.\d+)?$/', $token);
    }

    private static function isModelToken(string $token): bool
    {
        return (bool) (preg_match('/\p{L}/u', $token) && preg_match('/\d/', $token));
    }

    private static function same(float $a, float $b): bool
    {
        return abs($a - $b) <= 1e-6 * max(abs($a), abs($b)) + 1e-15;
    }

    /**
     * One field kept small: its words and their stems as space-wrapped strings (" cy 224c analytical balance "),
     * so "balance" finds "Balances" and the cached index stays a fraction of the size of PHP arrays.
     */
    private static function field(array $tokens): array
    {
        $tokens = array_values(array_unique($tokens));

        return [
            't' => ' ' . implode(' ', $tokens) . ' ',
            's' => ' ' . implode(' ', array_unique(array_map([self::class, 'stem'], $tokens))) . ' ',
        ];
    }

    /** @return string[] */
    private static function words(array $field): array
    {
        return array_values(array_filter(explode(' ', $field['t']), fn ($w) => $w !== ''));
    }

    private static function hasWord(array $field, string $word): bool
    {
        return str_contains($field['t'], ' ' . $word . ' ');
    }

    /** 2 = the word itself (or its plural), 1 = the beginning of a word, 0 = no match. */
    private static function wordScore(string $word, string $stem, array $field): int
    {
        if (str_contains($field['s'], ' ' . $stem . ' ')) {
            return 2;
        }

        return mb_strlen($word) >= 3 && str_contains($field['t'], ' ' . $word) ? 1 : 0;
    }

    /* ------------------------------------------------------------------ index */

    public static function index(): array
    {
        return self::$memo ??= self::store()->remember(self::CACHE_KEY, 3600, fn () => self::build());
    }

    /** The file store when the site caches in the database: the index can be larger than a database cache row comfortably holds. */
    private static function store(): \Illuminate\Contracts\Cache\Repository
    {
        return Cache::store(config('cache.default') === 'database' ? 'file' : null);
    }

    private static function build(): array
    {
        $products = [];
        $specVocab = [];
        $titleVocab = [];

        $visible = Product::with('category.brand')
            ->where('is_active', true)
            ->whereHas('category.brand', fn ($b) => $b->where('is_active', true))
            ->get();

        foreach ($visible as $p) {
            $brand = (string) $p->category?->brand?->name;
            $line = (string) $p->category?->name;

            $specs = [];
            $specKeyTokens = [];
            $specValueTokens = [];
            $models = [self::compact(self::normalize($p->name))];
            $specCompact = '';

            foreach ((array) ($p->specs ?? []) as $key => $value) {
                if (! is_scalar($value) || trim((string) $value) === '') {
                    continue;
                }
                $row = self::specRow((string) $key, (string) $value);
                $specs[] = $row;
                array_push($specKeyTokens, ...self::words($row['keyTokens']));
                array_push($specValueTokens, ...self::tokens(self::normalize((string) $value)));
                $specCompact .= '|' . self::compact(self::normalize((string) $value));

                if (preg_match('/^(model|models|model no|weighing module|order code)/iu', trim((string) $key))) {
                    foreach (preg_split('/[,;\/]|\bto\b|\band\b/iu', (string) $value) as $code) {
                        $code = self::compact(self::normalize(preg_replace('/^[^:]*:/u', '', $code)));
                        if ($code !== '' && preg_match('/\d/', $code)) {
                            $models[] = $code;
                        }
                    }
                }
            }

            $text = collect([$p->overview])
                ->merge(collect($p->features ?? [])->map(fn ($f) => ($f['title'] ?? '') . ' ' . ($f['text'] ?? $f['description'] ?? '')))
                ->merge(collect($p->advantages ?? [])->map(fn ($f) => ($f['title'] ?? '') . ' ' . ($f['text'] ?? $f['description'] ?? '')))
                ->merge(collect($p->faqs ?? [])->map(fn ($f) => ($f['question'] ?? '') . ' ' . ($f['answer'] ?? '')))
                ->filter(fn ($v) => is_string($v))
                ->implode(' ');

            $fields = [
                'name' => self::tokens(self::normalize($p->name)),
                'heading' => self::tokens(self::normalize($p->heading)),
                'group' => self::tokens(self::normalize($p->model_group)),
                'brand' => self::tokens(self::normalize($brand)),
                'line' => self::tokens(self::normalize($line)),
                'short' => self::tokens(self::normalize($p->short_description)),
                'specKeys' => $specKeyTokens,
                'specValues' => $specValueTokens,
                'body' => self::tokens(self::normalize($text)),
            ];
            $fields = array_map(fn ($t) => self::field($t), $fields);

            foreach (array_unique($specKeyTokens) as $t) {
                $specVocab[$t] = true;
            }
            foreach ([...self::words($fields['name']), ...self::words($fields['heading']), ...self::words($fields['line']), ...self::words($fields['brand'])] as $t) {
                $titleVocab[$t] = true;
            }

            $products[$p->id] = [
                'name' => mb_strtolower($p->name),
                'fields' => $fields,
                'titles' => array_values(array_filter([
                    self::compact(self::normalize($p->name)),
                    self::compact(self::normalize($p->heading)),
                    self::compact(self::normalize($p->model_group)),
                ])),
                'models' => array_values(array_unique($models)),
                'specCompact' => $specCompact,
                'specs' => $specs,
                // numbers written in the name, heading and short text, with their units ("220 g × 0.1 mg")
                'textRow' => self::specRow('', $p->name . ' ; ' . $p->heading . ' ; ' . $p->short_description),
            ];
        }

        $others = [
            'brands' => Brand::where('is_active', true)->get(['id', 'name'])
                ->mapWithKeys(fn ($b) => [$b->id => ['name' => self::field(self::tokens(self::normalize($b->name))), 'extra' => self::field([]), 'sort' => mb_strtolower($b->name)]])->all(),
            'lines' => Category::with('brand:id,name,is_active')->get(['id', 'brand_id', 'name', 'heading'])
                ->filter(fn ($c) => $c->brand?->is_active)
                ->mapWithKeys(fn ($c) => [$c->id => [
                    'name' => self::field(self::tokens(self::normalize($c->name))),
                    'extra' => self::field(self::tokens(self::normalize($c->brand->name . ' ' . $c->heading))),
                    'sort' => mb_strtolower($c->name),
                ]])->all(),
            'verticals' => Vertical::where('is_active', true)->get(['id', 'name', 'description', 'sort_order'])
                ->mapWithKeys(fn ($v) => [$v->id => [
                    'name' => self::field(self::tokens(self::normalize($v->name))),
                    'extra' => self::field(self::tokens(self::normalize($v->description))),
                    'sort' => sprintf('%05d', (int) $v->sort_order),
                ]])->all(),
        ];

        return ['products' => $products, 'specVocab' => $specVocab, 'titleVocab' => $titleVocab] + $others;
    }

    /** One specification row: its key words, the mass unit of the key ("Readability (mg)"), numbers and ranges of the value. */
    private static function specRow(string $key, string $value): array
    {
        $keyNorm = self::normalize($key);
        $rowFactor = null;

        if (preg_match('/\(\s*([^)\s,\/]+)/u', $keyNorm, $m) && isset(self::MASS[$m[1]])) {
            $rowFactor = self::MASS[$m[1]];
        }

        $valueNorm = self::normalize($value);
        $unit = '(µg|ug|mcg|mg|kg|gm|g)';
        $numbers = [];
        $ranges = [];

        preg_match_all('/(?<![\p{L}\d.])(\d+(?:\.\d+)?)\s*' . $unit . '?(?![\p{L}\d])/u', $valueNorm, $mm, PREG_SET_ORDER);
        foreach ($mm as $m) {
            $factor = ! empty($m[2]) ? self::MASS[$m[2]] : $rowFactor;
            $numbers[] = [(float) $m[1], $factor];
        }

        preg_match_all('/(?<![\p{L}\d.])(\d+(?:\.\d+)?)\s*' . $unit . '?\s*(?:\([^)]*\))?\s*(?:to|-)\s*(\d+(?:\.\d+)?)\s*' . $unit . '?(?![\p{L}\d])/u', $valueNorm, $rr, PREG_SET_ORDER);
        foreach ($rr as $r) {
            $f1 = ! empty($r[2]) ? self::MASS[$r[2]] : null;
            $f2 = ! empty($r[4]) ? self::MASS[$r[4]] : null;
            $factor = $f2 ?? $f1 ?? $rowFactor;
            $ranges[] = [min((float) $r[1], (float) $r[3]), max((float) $r[1], (float) $r[3]), $f1 ?? $factor, $f2 ?? $factor];
        }

        return [
            'key' => trim($key),
            'keyTokens' => self::field(self::tokens($keyNorm)),
            'numbers' => $numbers,
            'ranges' => $ranges,
        ];
    }

    /* ------------------------------------------------------------------ query */

    /**
     * Words, numbers (with unit and the spec word they belong to) and model codes of the query.
     */
    public static function parse(string $query, array $specVocab = [], array $titleVocab = []): array
    {
        $norm = self::normalize($query);
        $norm = preg_replace('/least\s+count/u', 'least', $norm);
        // "0.001g" -> "0.001 g"
        $norm = preg_replace('/(\d)(µg|ug|mcg|mg|kg|kgs|gms|gm|grams|gram|g)(?![\p{L}\d])/u', '$1 $2', $norm);
        $tokens = self::tokens($norm);

        $terms = [];
        foreach ($tokens as $i => $tok) {
            if (isset(self::MASS[$tok]) && $i > 0 && self::isNumber($tokens[$i - 1])) {
                $terms[count($terms) - 1]['factor'] = self::MASS[$tok];   // unit of the number before it

                continue;
            }
            if (self::isNumber($tok)) {
                $terms[] = ['type' => 'number', 'value' => (float) $tok, 'raw' => $tok, 'factor' => null, 'spec' => null];
            } elseif (self::isModelToken($tok)) {
                $terms[] = ['type' => 'model', 'raw' => $tok];
            } elseif (! in_array($tok, self::STOP, true) && (mb_strlen($tok) >= 2 || self::isNumber($tok))) {
                $isSpec = isset(self::SYNONYMS[$tok])
                    || (self::vocabHas($specVocab, $tok) && (in_array($tok, self::CORE_SPEC_WORDS, true) || ! self::vocabHas($titleVocab, $tok)));
                $terms[] = ['type' => 'word', 'raw' => $tok, 'stem' => self::stem($tok), 'spec' => $isSpec];
            }
        }

        // Pair each number with the spec word just before it (or, if none, the next one): "readability 0.001", "0.1 mg readability".
        $paired = [];
        foreach ($terms as $i => $term) {
            if ($term['type'] !== 'number') {
                continue;
            }
            $spec = null;
            for ($j = $i - 1; $j >= 0 && $spec === null; $j--) {
                if ($terms[$j]['type'] === 'number') {
                    break;
                }
                if ($terms[$j]['type'] === 'word' && $terms[$j]['spec']) {
                    $spec = $j;
                }
            }
            for ($j = $i + 1; $spec === null && $j < count($terms); $j++) {
                if ($terms[$j]['type'] === 'number') {
                    break;
                }
                if ($terms[$j]['type'] === 'word' && $terms[$j]['spec']) {
                    $spec = $j;
                }
            }
            if ($spec !== null) {
                $terms[$i]['spec'] = self::SYNONYMS[$terms[$spec]['raw']] ?? [$terms[$spec]['raw']];
                $paired[$spec] = true;
            }
        }
        $terms = array_values(array_filter($terms, fn ($t, $i) => ! isset($paired[$i]), ARRAY_FILTER_USE_BOTH));

        // The whole query read as one model number ("CY 224C", "bsa-224s"), not when it is a spec search or a long sentence.
        $hasSpec = (bool) array_filter($terms, fn ($t) => ($t['type'] === 'number' && ($t['spec'] !== null || $t['factor'] !== null)) || ($t['type'] === 'word' && $t['spec']));
        $compact = self::compact(implode('', array_filter($tokens, fn ($t) => ! in_array($t, self::STOP, true))));
        $modelCompact = (! $hasSpec && count($tokens) <= 4 && preg_match('/\p{L}/u', $compact) && preg_match('/\d/', $compact) && mb_strlen($compact) >= 4) ? $compact : null;

        return [
            'terms' => $terms,
            'termCount' => count($terms),
            'modelCompact' => $modelCompact,
            'phrase' => trim(implode(' ', $tokens)),
        ];
    }

    private static function vocabHas(array $vocab, string $word): bool
    {
        if (isset($vocab[$word]) || isset($vocab[self::stem($word)])) {
            return true;
        }
        $stem = self::stem($word);
        foreach ($vocab as $t => $_) {
            if (self::stem((string) $t) === $stem) {
                return true;
            }
        }

        return false;
    }

    /** "bsa2240ion" -> bsa2240io, bsa2240i, bsa2240, bsa224 (never shorter than letters + 3 digits). */
    private static function modelPrefixes(string $compact): array
    {
        if (! preg_match('/^(\p{L}+)(\d{3})/u', $compact, $m)) {
            return [];
        }
        $min = mb_strlen($m[1]) + 3;
        $out = [];
        for ($len = mb_strlen($compact) - 1; $len >= $min; $len--) {
            $out[] = mb_substr($compact, 0, $len);
        }

        return $out;
    }

    /* ------------------------------------------------------------------ matching */

    private const WEIGHTS = ['name' => 12, 'heading' => 9, 'group' => 7, 'brand' => 7, 'line' => 6, 'short' => 3,
        'specKeys' => 3, 'specValues' => 3, 'body' => 1];

    /** @return array<int, array{score: float, name: string, specs: string[]}> */
    private static function matchProducts(array $parsed, array $products, bool $partial): array
    {
        $terms = $parsed['terms'];
        if (! $terms && $parsed['modelCompact'] === null) {
            return [];
        }

        $results = [];
        $bestCount = 0;

        foreach ($products as $id => $p) {
            $score = 0.0;
            $matched = 0;
            $specs = [];

            // The whole query is a model number ("cy224c", "CY 224C", "bsa224s").
            $modelHit = false;
            if ($parsed['modelCompact'] !== null) {
                $mc = $parsed['modelCompact'];
                if (in_array($mc, $p['titles'], true) || in_array($mc, $p['models'], true)) {
                    $score += 150;
                    $modelHit = true;
                } elseif (self::containsAny($p['titles'], $mc)) {
                    $score += 60;
                    $modelHit = true;
                } elseif (self::containsAny($p['models'], $mc)) {
                    $score += 50;
                    $modelHit = true;
                } elseif (str_contains($p['specCompact'], $mc)) {
                    $score += 25;
                    $modelHit = true;
                }
                if ($modelHit) {
                    foreach ($p['specs'] as $row) {
                        if (preg_match('/^(model|weighing module)/iu', $row['key'])) {
                            $specs[] = $row['key'];
                            break;
                        }
                    }
                }
            }

            foreach ($terms as $term) {
                $s = match ($term['type']) {
                    'word' => self::scoreWord($term, $p, $specs),
                    'model' => self::scoreModel($term, $p),
                    'number' => self::scoreNumber($term, $p, $specs),
                };
                if ($s > 0) {
                    $matched++;
                    $score += $s;
                }
            }

            // A model search shows the Model row on the card, not whatever row happened to hold the same digits.
            if ($modelHit) {
                $specs = array_values(array_filter($specs, fn ($k) => (bool) preg_match('/^(model|weighing module)/iu', $k)));
            }

            $all = $matched === count($terms);

            if ($partial) {
                if ($matched === 0 || $matched * 2 < count($terms)) {
                    continue;
                }
                $bestCount = max($bestCount, $matched);
            } elseif (! $all && ! $modelHit) {
                continue;
            }

            // The exact name or a name that starts with the query comes first.
            if ($parsed['phrase'] !== '') {
                if ($p['name'] === $parsed['phrase']) {
                    $score += 40;
                } elseif (str_starts_with($p['name'], $parsed['phrase'])) {
                    $score += 20;
                }
            }

            $results[$id] = ['score' => $score, 'name' => $p['name'], 'specs' => array_values(array_unique($specs)), 'count' => $matched];
        }

        if ($partial) {
            $results = array_filter($results, fn ($r) => $r['count'] === $bestCount);
        }

        return $results;
    }

    private static function containsAny(array $haystacks, string $needle): bool
    {
        foreach ($haystacks as $h) {
            if ($h !== '' && str_contains($h, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function scoreWord(array $term, array $p, array &$specs): float
    {
        $best = 0.0;
        foreach (self::WEIGHTS as $field => $weight) {
            $w = self::wordScore($term['raw'], $term['stem'], $p['fields'][$field]);
            if ($w) {
                $best = max($best, $weight * ($w === 2 ? 1 : 0.6));
            }
        }

        // A word written together that the catalogue writes apart ("semimicro", "toppan").
        if ($best === 0.0 && mb_strlen($term['raw']) >= 5 && self::containsAny($p['titles'], $term['raw'])) {
            $best = 6.0;
        }

        // A spec word on its own ("readability"): show that row on the card.
        if ($best > 0 && $term['spec']) {
            foreach ($p['specs'] as $row) {
                if (self::wordScore($term['raw'], $term['stem'], $row['keyTokens'])) {
                    $specs[] = $row['key'];
                    break;
                }
            }
        }

        return $best;
    }

    private static function scoreModel(array $term, array $p): float
    {
        $code = self::compact($term['raw']);

        foreach (['name', 'heading', 'group'] as $field) {
            if (self::hasWord($p['fields'][$field], $term['raw'])) {
                return 30;
            }
        }
        if (in_array($code, $p['models'], true)) {
            return 25;
        }
        if (self::containsAny($p['titles'], $code) || self::containsAny($p['models'], $code)) {
            return 15;
        }
        if (self::hasWord($p['fields']['specValues'], $term['raw']) || str_contains($p['specCompact'], $code)) {
            return 8;
        }

        return self::hasWord($p['fields']['body'], $term['raw']) ? 2 : 0;
    }

    private static function scoreNumber(array $term, array $p, array &$specs): float
    {
        $best = 0.0;
        $bestKey = null;

        foreach ($p['specs'] as $row) {
            if ($term['spec'] !== null && ! self::keyMatches($row['keyTokens'], $term['spec'])) {
                continue;
            }
            $s = self::numberInRow($term, $row);
            // "0.01 g balance" usually means the readability, "220 g balance" the capacity: those rows win a tie.
            if ($s > 0 && $term['spec'] === null && preg_match('/readability|capacity|max/iu', $row['key'])) {
                $s += 3;
            }
            if ($s > $best) {
                [$best, $bestKey] = [$s, $row['key']];
            }
        }

        // Paired with a spec word: only that spec counts.
        if ($term['spec'] !== null) {
            if ($bestKey !== null) {
                $specs[] = $bestKey;
            }

            return $best * 2;
        }

        // Only a number with a unit is worth showing on the card ("0.1 mg"); a bare number is often part of a model.
        if ($bestKey !== null && $term['factor'] !== null) {
            $specs[] = $bestKey;
        }

        return max($best, self::numberInRow($term, $p['textRow']));
    }

    private static function between(float $x, float $a, float $b): bool
    {
        return $x >= min($a, $b) * (1 - 1e-9) && $x <= max($a, $b) * (1 + 1e-9);
    }

    private static function keyMatches(array $keyTokens, array $specWords): bool
    {
        foreach ($specWords as $w) {
            if (self::wordScore($w, self::stem($w), $keyTokens)) {
                return true;
            }
        }

        return false;
    }

    /** 15 = the exact number, 6 = inside a range ("0.001 to 0.01"), 0 = no. A unit given in the query is converted. */
    private static function numberInRow(array $term, array $row): float
    {
        $v = $term['value'];
        $f = $term['factor'];

        // A number typed without a unit is read both as written and as grams ("readability 0.001" = 0.001 g = "Readability (mg): 1").
        foreach ($row['numbers'] as [$n, $nf]) {
            if ($f !== null && $nf === null) {
                continue;   // "220 g" never matches a number with no weight unit (a pan size in mm, a model number)
            }
            if ($f !== null) {
                if (self::same($v * $f, $n * $nf)) {
                    return 15;
                }
            } elseif (self::same($v, $n)) {
                return $nf === 1.0 ? 16 : 15;   // written the same, in grams first
            } elseif ($nf !== null && self::same($v, $n * $nf)) {
                return 14;                      // the same weight written in another unit
            }
        }

        // A bare number ("cy 224") is not looked for inside ranges ("100 to 240 V"): only with a spec word or a unit.
        if ($term['spec'] === null && $f === null) {
            return 0;
        }

        foreach ($row['ranges'] as [$lo, $hi, $f1, $f2]) {
            $known = $f1 !== null && $f2 !== null;
            if ($f !== null && $known) {
                if (self::between($v * $f, $lo * $f1, $hi * $f2)) {
                    return 6;
                }
            } elseif ($f === null && (self::between($v, $lo, $hi) || ($known && self::between($v, $lo * $f1, $hi * $f2)))) {
                return 6;
            }
        }

        return 0;
    }

    /** Brands, product lines and verticals whose words cover every plain word of the query. */
    private static function matchOthers(array $parsed, array $index): array
    {
        $words = array_values(array_filter($parsed['terms'], fn ($t) => $t['type'] === 'word' && ! $t['spec']));
        $out = ['brands' => [], 'lines' => [], 'verticals' => []];

        if (! $words) {
            return $out;
        }

        foreach (['brands', 'lines', 'verticals'] as $kind) {
            $found = [];
            foreach ($index[$kind] as $id => $item) {
                $score = 0;
                foreach ($words as $w) {
                    $s = self::wordScore($w['raw'], $w['stem'], $item['name']) * 3
                        ?: self::wordScore($w['raw'], $w['stem'], $item['extra']);
                    if (! $s) {
                        continue 2;
                    }
                    $score += $s;
                }
                $found[$id] = [$score, $item['sort']];
            }
            uasort($found, fn ($a, $b) => [$b[0], $a[1]] <=> [$a[0], $b[1]]);
            $out[$kind] = array_keys($found);
        }

        return $out;
    }

    private static function load(string $model, array $ids, int $limit, ?\Closure $with = null): Collection
    {
        $ids = array_slice($ids, 0, $limit);
        if (! $ids) {
            return collect();
        }
        $query = $model::whereIn('id', $ids);
        if ($with) {
            $with($query);
        }

        return $query->get()->sortBy(fn ($m) => array_search($m->id, $ids))->values();
    }
}
