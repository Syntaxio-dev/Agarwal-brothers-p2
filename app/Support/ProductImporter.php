<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * Bulk product upload from a CSV or Excel (.xlsx) sheet.
 *
 * All-or-nothing: every row is checked first; if anything is wrong nothing is saved and the problems are
 * listed by row, so a half-imported file never has to be cleaned up. New products are created as drafts
 * (inactive) unless the sheet says otherwise, because pictures cannot be uploaded this way.
 */
class ProductImporter
{
    public const MAX_ROWS = 1000;

    /** Column name => accepted header spellings (after lower-casing and turning spaces/dashes into underscores). */
    public const ALIASES = [
        'name' => ['name', 'product', 'product_name'],
        'brand' => ['brand', 'brand_name'],
        'category' => ['category', 'product_line', 'line', 'category_name'],
        'model_group' => ['model_group', 'group'],
        'short_description' => ['short_description', 'description', 'short_desc'],
        'overview' => ['overview'],
        'specs' => ['specs', 'specifications'],
        'features' => ['features', 'key_features'],
        'advantages' => ['advantages', 'key_advantages'],
        'is_active' => ['is_active', 'active', 'status', 'live'],
        'slug' => ['slug'],
    ];

    /**
     * Read the first sheet.
     *
     * @return array{headers: list<string>, rows: list<array{line: int, cells: list<string>}>}
     */
    public static function read(string $path, string $extension): array
    {
        $extension = strtolower($extension);

        if ($extension === 'xlsx') {
            $reader = new XlsxReader;
        } else {
            $options = new CsvOptions;
            $options->FIELD_DELIMITER = static::delimiter($path);
            $reader = new CsvReader($options);
        }

        $headers = null;
        $rows = [];

        $reader->open($path);

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                $line = 0;
                foreach ($sheet->getRowIterator() as $row) {
                    $line++;
                    $cells = array_map(fn ($c) => static::cell($c), $row->toArray());

                    if (collect($cells)->every(fn ($c) => $c === '')) {
                        continue;
                    }

                    if ($headers === null) {
                        $headers = $cells;
                        $headers[0] = ltrim($headers[0] ?? '', "\xEF\xBB\xBF");   // BOM Excel adds to CSV files
                        continue;
                    }

                    $rows[] = ['line' => $line, 'cells' => $cells];

                    if (count($rows) > self::MAX_ROWS) {
                        break 2;
                    }
                }
                break;   // first sheet only
            }
        } finally {
            $reader->close();
        }

        return ['headers' => $headers ?? [], 'rows' => $rows];
    }

    /**
     * Check every row and, when all are fine, save them.
     *
     * @return array{created: int, updated: int, skipped: int, errors: list<array{line: int, name: string, message: string}>, fatal: ?string}
     */
    public static function import(string $path, string $extension, bool $updateExisting = false, bool $createCategories = false): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [], 'fatal' => null];

        try {
            $sheet = static::read($path, $extension);
        } catch (\Throwable $e) {
            $result['fatal'] = 'This file could not be read. Save it again as CSV (comma delimited) or Excel (.xlsx) and try once more.';

            return $result;
        }

        if (count($sheet['rows']) > self::MAX_ROWS) {
            $result['fatal'] = 'The file has more than ' . self::MAX_ROWS . ' product rows. Please split it into smaller files.';

            return $result;
        }

        $map = static::mapHeaders($sheet['headers']);
        foreach (['name', 'brand', 'category'] as $required) {
            if (! isset($map['fields'][$required])) {
                $result['fatal'] = 'The first row must contain the columns: name, brand, category. Missing: ' . $required . '. See the format below.';

                return $result;
            }
        }
        if (! $sheet['rows']) {
            $result['fatal'] = 'The file has no product rows under the column names.';

            return $result;
        }

        $brands = Brand::all()->keyBy(fn ($b) => static::key($b->name));
        $plan = [];          // validated rows to save
        $seen = [];          // name + category already used in this file
        $newCategories = []; // "brand|category" => true (created once)

        foreach ($sheet['rows'] as ['line' => $line, 'cells' => $cells]) {
            $value = fn (string $field) => trim($cells[$map['fields'][$field] ?? -1] ?? '');
            $name = $value('name');
            $fail = function (string $message) use (&$result, $line, $name) {
                $result['errors'][] = ['line' => $line, 'name' => $name !== '' ? $name : '(no name)', 'message' => $message];
            };

            if ($name === '') {
                $fail('The name is empty.');
                continue;
            }
            if (mb_strlen($name) > 255) {
                $fail('The name is longer than 255 characters.');
                continue;
            }

            $brand = $brands->get(static::key($value('brand')));
            if (! $brand) {
                $fail($value('brand') === '' ? 'The brand is empty.' : 'Brand "' . $value('brand') . '" does not exist. Add it under Catalogue, Brands first, or fix the spelling.');
                continue;
            }

            $categoryName = $value('category');
            if ($categoryName === '') {
                $fail('The category is empty.');
                continue;
            }
            $category = Category::where('brand_id', $brand->id)->get()->first(fn ($c) => static::key($c->name) === static::key($categoryName));
            $needsNewCategory = false;
            if (! $category) {
                if (! $createCategories) {
                    $fail('Category "' . $categoryName . '" does not exist under ' . $brand->name . '. Create it first, fix the spelling, or switch on "Create missing categories".');
                    continue;
                }
                $needsNewCategory = true;
            }

            $duplicateKey = $brand->id . '|' . static::key($categoryName) . '|' . static::key($name);
            if (isset($seen[$duplicateKey])) {
                $fail('This product appears twice in the file (same name, brand and category).');
                continue;
            }
            $seen[$duplicateKey] = true;

            $active = static::boolean($value('is_active'));
            if ($active === null) {
                $fail('The active column must be yes or no.');
                continue;
            }

            $slug = $value('slug');
            if ($slug !== '' && ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                $fail('The slug may only contain lowercase letters, numbers and dashes.');
                continue;
            }

            // Specifications: one "specs" cell and/or one column per specification ("spec: Capacity").
            $specs = [];
            $specError = null;
            foreach (static::pairs($value('specs'), $specError) as $k => $v) {
                $specs[$k] = $v;
            }
            foreach ($map['specColumns'] as $index => $label) {
                $cell = trim($cells[$index] ?? '');
                if ($cell !== '') {
                    $specs[$label] = $cell;
                }
            }
            if ($specError) {
                $fail($specError);
                continue;
            }

            $featureError = $advantageError = null;
            $features = static::items($value('features'), $featureError);
            $advantages = static::items($value('advantages'), $advantageError);
            if ($featureError || $advantageError) {
                $fail($featureError ?: $advantageError);
                continue;
            }

            $text = [
                'model_group' => $value('model_group'),
                'short_description' => $value('short_description'),
                'overview' => $value('overview'),
            ];
            if (mb_strlen($text['model_group']) > 255) {
                $fail('The model group is longer than 255 characters.');
                continue;
            }

            $plan[] = compact('line', 'name', 'brand', 'category', 'categoryName', 'needsNewCategory', 'slug', 'active', 'specs', 'features', 'advantages', 'text') + [
                'activeGiven' => $value('is_active') !== '',
            ];
        }

        if ($result['errors']) {
            return $result;   // nothing is saved when any row is wrong
        }

        DB::transaction(function () use ($plan, $updateExisting, &$result, &$newCategories) {
            foreach ($plan as $row) {
                $category = $row['category'];

                if ($row['needsNewCategory']) {
                    $key = $row['brand']->id . '|' . static::key($row['categoryName']);
                    $category = $newCategories[$key] ??= Category::create([
                        'brand_id' => $row['brand']->id,
                        'name' => $row['categoryName'],
                        'slug' => static::uniqueSlug(Category::class, $row['categoryName']),
                    ]);
                }

                $existing = Product::where('category_id', $category->id)->get()->first(fn ($p) => static::key($p->name) === static::key($row['name']));

                if ($existing) {
                    if (! $updateExisting) {
                        $result['skipped']++;

                        continue;
                    }

                    $changes = array_filter($row['text'], fn ($v) => $v !== '');
                    if ($row['specs']) {
                        $changes['specs'] = $row['specs'];
                    }
                    if ($row['features']) {
                        $changes['features'] = $row['features'];
                    }
                    if ($row['advantages']) {
                        $changes['advantages'] = $row['advantages'];
                    }
                    if ($row['activeGiven']) {
                        $changes['is_active'] = $row['active'];
                    }

                    $existing->update($changes);
                    $result['updated']++;

                    continue;
                }

                Product::create([
                    'category_id' => $category->id,
                    'name' => $row['name'],
                    'slug' => $row['slug'] !== '' && ! Product::where('slug', $row['slug'])->exists() ? $row['slug'] : static::uniqueSlug(Product::class, $row['name']),
                    'model_group' => $row['text']['model_group'] ?: null,
                    'short_description' => $row['text']['short_description'] ?: null,
                    'overview' => $row['text']['overview'] ?: null,
                    'specs' => $row['specs'] ?: null,
                    'features' => $row['features'] ?: null,
                    'advantages' => $row['advantages'] ?: null,
                    'is_active' => $row['active'],
                ]);
                $result['created']++;
            }
        });

        return $result;
    }

    /** Header names -> where each field lives. */
    private static function mapHeaders(array $headers): array
    {
        $fields = [];
        $specColumns = [];

        foreach ($headers as $index => $header) {
            $header = trim($header);
            $normal = str_replace([' ', '-'], '_', mb_strtolower($header));

            if (preg_match('/^spec(?:ification)?s?\s*:\s*(.+)$/iu', $header, $m)) {
                $specColumns[$index] = trim($m[1]);

                continue;
            }
            if (preg_match('/^spec(?:ification)?s?_(.+)$/iu', $normal, $m)) {
                $specColumns[$index] = ucfirst(str_replace('_', ' ', $m[1]));

                continue;
            }

            foreach (self::ALIASES as $field => $names) {
                if (in_array($normal, $names, true) && ! isset($fields[$field])) {
                    $fields[$field] = $index;

                    continue 2;
                }
            }
        }

        return ['fields' => $fields, 'specColumns' => $specColumns];
    }

    /** "Capacity: 220 g | Readability: 0.1 mg" -> ['Capacity' => '220 g', ...] */
    private static function pairs(string $text, ?string &$error): array
    {
        $out = [];

        foreach (static::parts($text) as $part) {
            if (! str_contains($part, ':') || trim(Str::before($part, ':')) === '' || trim(Str::after($part, ':')) === '') {
                $error = 'Could not read "' . Str::limit($part, 40) . '". Write specifications as Name: Value, separated by | (for example Capacity: 220 g | Readability: 0.1 mg).';

                return [];
            }
            $out[trim(Str::before($part, ':'))] = trim(Str::after($part, ':'));
        }

        return $out;
    }

    /** "Fast: Quick results | Quiet" -> [['title' => 'Fast', 'text' => 'Quick results'], ['title' => 'Quiet', 'text' => '']] */
    private static function items(string $text, ?string &$error): array
    {
        $out = [];

        foreach (static::parts($text) as $part) {
            $title = trim(str_contains($part, ':') ? Str::before($part, ':') : $part);
            $body = str_contains($part, ':') ? trim(Str::after($part, ':')) : '';

            if ($title === '') {
                $error = 'A feature or advantage has no title. Write them as Title: Description, separated by |.';

                return [];
            }
            $out[] = ['title' => $title, 'text' => $body];
        }

        return $out;
    }

    /** Pieces separated by | or line breaks. */
    private static function parts(string $text): array
    {
        return collect(preg_split('/\s*[|\r\n]+\s*/u', trim($text)))
            ->map(fn ($p) => trim($p))->filter(fn ($p) => $p !== '')->values()->all();
    }

    private static function boolean(string $value): ?bool
    {
        return match (mb_strtolower(trim($value))) {
            '', 'no', 'n', '0', 'false', 'inactive', 'draft', 'off' => false,
            'yes', 'y', '1', 'true', 'active', 'live', 'on' => true,
            default => null,
        };
    }

    private static function key(string $text): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
    }

    private static function uniqueSlug(string $model, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $n = 2;

        while ($model::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    private static function cell(mixed $cell): string
    {
        if ($cell instanceof \DateTimeInterface) {
            return $cell->format('Y-m-d');
        }
        if (is_float($cell) && floor($cell) === $cell && abs($cell) < 1e15) {
            return (string) (int) $cell;
        }

        return trim((string) $cell);
    }

    /** Comma, semicolon or tab: whichever the header line uses most. */
    private static function delimiter(string $path): string
    {
        $handle = fopen($path, 'rb');
        $line = $handle ? (string) fgets($handle) : '';
        if ($handle) {
            fclose($handle);
        }
        $counts = [',' => substr_count($line, ','), ';' => substr_count($line, ';'), "\t" => substr_count($line, "\t")];
        arsort($counts);

        return array_key_first($counts) ?: ',';
    }

    /** Header + example rows for the downloadable template. @return list<list<string>> */
    public static function sample(): array
    {
        $category = Category::with('brand')->whereHas('brand')->orderBy('id')->first();
        $brand = $category?->brand?->name ?? 'Sartorius';
        $line = $category?->name ?? 'Analytical Balances';

        return [
            ['name', 'brand', 'category', 'model_group', 'short_description', 'specs', 'features', 'advantages', 'is_active', 'spec: Capacity', 'spec: Readability'],
            ['Entris II Essential', $brand, $line, 'Standard Models', 'Compact precision balance for everyday weighing.', 'Pan size: 90 mm | Display: LCD', 'Fast: Stable reading in 2 seconds | Compact: Small footprint', 'Easy to clean: Smooth, sealed housing', 'no', '220 g', '0.1 mg'],
            ['Entris II Advanced', $brand, $line, 'Advanced Models', 'Higher accuracy with built-in calibration.', 'Pan size: 120 mm | Display: Touch', 'Built-in calibration: Adjusts itself', '', 'yes', '620 g', '1 mg'],
            ['Entris II Basic', $brand, $line, '', 'Entry-level balance.', '', '', '', '', '', ''],
        ];
    }
}
