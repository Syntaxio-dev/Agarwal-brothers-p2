<?php

namespace App\Sales\Console;

use App\Sales\Models\SalesProduct;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Loads the product guide from a JSON file (the owner's sheet, converted: database/data/sales-products.json).
 * Safe to run again: a product that already exists (same name) is left alone, so a manager's edits are never overwritten, unless
 * --update is given. --dry-run checks the file and says what it would do without writing anything.
 */
class ImportProducts extends Command
{
    protected $signature = 'sales:import-products {file? : JSON file (default database/data/sales-products.json)} {--update : overwrite products that already exist} {--dry-run : only check the file}';

    protected $description = 'Load the Sales product guide from a JSON file';

    public function handle(): int
    {
        $path = $this->argument('file') ?: database_path('data/sales-products.json');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($path), true);

        if (! is_array($rows) || $rows === [] || ! array_is_list($rows)) {
            $this->error('The file must hold a list of products.');

            return self::FAILURE;
        }

        $problems = [];
        foreach ($rows as $i => $row) {
            foreach ($this->problems((array) $row) as $message) {
                $problems[] = '#' . ($row['sort'] ?? $i + 1) . ' ' . ($row['name'] ?? '?') . ': ' . $message;
            }
        }

        if ($problems) {
            $this->error('The file has problems, nothing was imported:');
            foreach ($problems as $line) {
                $this->line(' - ' . $line);
            }

            return self::FAILURE;
        }

        $created = $updated = $skipped = 0;

        $run = function () use ($rows, &$created, &$updated, &$skipped) {
            foreach ($rows as $row) {
                $data = $this->shape($row);
                $existing = SalesProduct::query()->where('name', $data['name'])->first();

                if (! $existing) {
                    $this->option('dry-run') ?: SalesProduct::create($data);
                    $created++;
                } elseif ($this->option('update')) {
                    $this->option('dry-run') ?: $existing->update($data);
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        };

        $this->option('dry-run') ? $run() : DB::transaction($run);

        $this->info(($this->option('dry-run') ? 'Dry run: ' : '') . "{$created} added, {$updated} updated, {$skipped} left as they were.");

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function problems(array $row): array
    {
        $lists = ['premium_brands', 'economy_brands', 'lite_brands', 'key_parameters', 'short_term_problems', 'long_term_problems'];

        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:200'],
            'group' => ['nullable', 'string', 'max:100'],
            'sort' => ['required', 'integer', 'min:0', 'max:65000'],
            'summary' => ['nullable', 'string', 'max:6000'],
            'industries' => ['nullable', 'string', 'max:2000'],
            'variants' => ['nullable', 'array', 'max:20'],
            'variants.*.name' => ['nullable', 'string', 'max:200'],
            'variants.*.text' => ['required', 'string', 'max:2000'],
            'top_brands' => ['nullable', 'array', 'max:20'],
            'top_brands.*.name' => ['required', 'string', 'max:150'],
            'top_brands.*.note' => ['nullable', 'string', 'max:1000'],
        ];
        foreach ($lists as $list) {
            $rules[$list] = ['nullable', 'array', 'max:40'];
            $rules["{$list}.*"] = ['string', 'max:1000'];
        }

        return Validator::make($row, $rules)->errors()->all();
    }

    private function shape(array $row): array
    {
        $list = fn (string $key) => array_values(array_filter(array_map('trim', (array) ($row[$key] ?? [])), 'strlen'));

        return [
            'name' => trim($row['name']),
            'group' => filled($row['group'] ?? null) ? trim($row['group']) : null,
            'sort' => (int) $row['sort'],
            'premium_brands' => $list('premium_brands'),
            'economy_brands' => $list('economy_brands'),
            'lite_brands' => $list('lite_brands'),
            'summary' => trim((string) ($row['summary'] ?? '')),
            'variants' => array_values(array_map(fn ($v) => ['name' => trim((string) ($v['name'] ?? '')), 'text' => trim((string) $v['text'])], (array) ($row['variants'] ?? []))),
            'industries' => trim((string) ($row['industries'] ?? '')),
            'key_parameters' => $list('key_parameters'),
            'short_term_problems' => $list('short_term_problems'),
            'long_term_problems' => $list('long_term_problems'),
            'top_brands' => array_values(array_map(fn ($b) => ['name' => trim((string) $b['name']), 'note' => trim((string) ($b['note'] ?? ''))], (array) ($row['top_brands'] ?? []))),
            'is_active' => true,
        ];
    }
}
