<?php

namespace App\Sales\Console;

use App\Sales\Models\SalesBrand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Loads the brand guide from a JSON file (database/data/sales-brands.json). Like sales:import-products: a brand that already exists
 * (same name) is left alone, so a manager's edits are never overwritten, unless --update is given. --dry-run only checks the file.
 */
class ImportBrands extends Command
{
    protected $signature = 'sales:import-brands {file? : JSON file (default database/data/sales-brands.json)} {--update : overwrite brands that already exist} {--dry-run : only check the file}';

    protected $description = 'Load the Sales brand guide from a JSON file';

    private const LISTS = ['known_for' => 6, 'known_lines' => 8, 'best_for' => 6, 'pitch' => 6, 'watch_out' => 4];

    public function handle(): int
    {
        $path = $this->argument('file') ?: database_path('data/sales-brands.json');
        $rows = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        if (! is_array($rows) || $rows === [] || ! array_is_list($rows)) {
            $this->error("The file must hold a list of brands: {$path}");

            return self::FAILURE;
        }

        $problems = [];
        foreach ($rows as $i => $row) {
            foreach ($this->problems((array) $row) as $message) {
                $problems[] = '#' . ($i + 1) . ' ' . ($row['name'] ?? '?') . ': ' . $message;
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
            foreach ($rows as $i => $row) {
                $data = $this->shape($row, $i);
                $existing = SalesBrand::query()->where('name', $data['name'])->first();
                if (! $existing) {
                    $this->option('dry-run') ?: SalesBrand::create($data);
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
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'origin' => ['nullable', 'string', 'max:160'],
            'channel' => ['nullable', 'string', 'max:160'],
            'about' => ['nullable', 'string', 'max:1000'],
            'objections' => ['nullable', 'array', 'max:6'],
            'objections.*.if' => ['required', 'string', 'max:200'],
            'objections.*.then' => ['required', 'string', 'max:400'],
            'competitors' => ['nullable', 'array', 'max:8'],
            'competitors.*.name' => ['required', 'string', 'max:100'],
            'competitors.*.note' => ['nullable', 'string', 'max:400'],
        ];
        foreach (self::LISTS as $list => $max) {
            $rules[$list] = ['nullable', 'array', "max:{$max}"];
            $rules["{$list}.*"] = ['string', 'max:300'];
        }

        return Validator::make($row, $rules)->errors()->all();
    }

    private function shape(array $row, int $i): array
    {
        $list = fn (string $key) => array_values(array_filter(array_map('trim', (array) ($row[$key] ?? [])), 'strlen'));
        $text = fn (string $key) => filled($row[$key] ?? null) ? trim((string) $row[$key]) : null;

        $data = [
            'name' => trim($row['name']),
            'origin' => $text('origin'),
            'channel' => $text('channel'),
            'about' => $text('about'),
            'objections' => array_values(array_map(fn ($o) => ['if' => trim((string) $o['if']), 'then' => trim((string) $o['then'])], (array) ($row['objections'] ?? []))),
            'competitors' => array_values(array_map(fn ($c) => ['name' => trim((string) $c['name']), 'note' => trim((string) ($c['note'] ?? ''))], (array) ($row['competitors'] ?? []))),
            'sort' => (int) ($row['sort'] ?? $i + 1),
            'is_active' => true,
        ];
        foreach (array_keys(self::LISTS) as $key) {
            $data[$key] = $list($key);
        }

        return $data;
    }
}
