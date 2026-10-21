<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds products from the series files in database/data/products/*.php (one file per brand series, built from the brochure).
 * Every model becomes a HIDDEN draft (is_active = false); photos and video are added by hand in the admin.
 * Adds only missing models (matched by slug or by the "Model" spec in the same category); never changes or deletes a product.
 * Rules: CLAUDE.md section 12.
 */
class LoadProductDrafts extends Command
{
    protected $signature = 'products:load {--batch= : Load only models of this batch number} {--dry-run : Show what would be added without saving}';

    protected $description = 'Add the brochure-based product drafts that ship with the project (hidden drafts, adds only missing ones).';

    public function handle(): int
    {
        $batch = $this->option('batch') !== null ? (int) $this->option('batch') : null;
        $dry = (bool) $this->option('dry-run');
        $added = 0;
        $skipped = 0;

        foreach (glob(database_path('data/products/*.php')) ?: [] as $file) {
            $series = require $file;

            $brand = Brand::whereRaw('LOWER(name) = ?', [Str::lower($series['brand'])])->first();
            $category = $brand
                ? Category::where('brand_id', $brand->id)->where('name', $series['category'])->first()
                : null;

            if (! $category) {
                $this->warn("  ! Category \"{$series['category']}\" of brand \"{$series['brand']}\" not found, skipped " . basename($file));

                continue;
            }

            // Models already in this category (by Model spec or slug), so the owner's own entries are never duplicated.
            $existing = Product::where('category_id', $category->id)->get();
            $taken = $existing->map(fn ($p) => Str::lower((string) ($p->specs['Model'] ?? '')))->filter()->all();

            foreach ($series['models'] as $m) {
                if ($batch !== null && ($m['batch'] ?? 1) !== $batch) {
                    continue;
                }

                $slug = Str::slug($m['model']);

                if (in_array(Str::lower($m['model']), $taken, true) || Product::where('slug', $slug)->exists()) {
                    $skipped++;
                    $this->line("  = {$m['model']} already exists, left as it is");

                    continue;
                }

                if (! $dry) {
                    Product::create([
                        'category_id' => $category->id,
                        'name' => $m['model'],
                        'slug' => $slug,
                        'heading' => $m['model'] . ' ' . $series['product_type'],
                        'model_group' => $series['model_group'],
                        'short_description' => $series['short_description'],
                        'overview' => $series['overview'],
                        'features' => $series['features'],
                        'advantages' => $series['advantages'],
                        'specs' => $m['specs'],
                        'faqs' => $series['faqs'],
                        'documents' => array_map(fn ($d) => $d + ['url' => null], $series['documents']),
                        'is_active' => false,
                        'is_top_pick' => false,
                    ]);
                }

                $added++;
                $this->line('  + ' . $m['model']);
            }
        }

        $this->info(($dry ? '[dry run] Would add ' : 'Added ') . "{$added} product(s) as hidden drafts, {$skipped} already existed.");

        return self::SUCCESS;
    }
}
