<?php

namespace App\Console\Commands;

use App\Models\Brand;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LoadBrandList extends Command
{
    protected $signature = 'brands:load';

    protected $description = 'Add the brands of the owner\'s Product Master sheet (database/data/brands.php). Only adds missing ones, never changes or deletes existing brands.';

    public function handle(): int
    {
        $added = 0;

        foreach (require database_path('data/brands.php') as $name) {
            $slug = Str::slug($name);

            if (Brand::where('slug', $slug)->orWhere('name', $name)->exists()) {
                continue;
            }

            Brand::create(['name' => $name, 'slug' => $slug, 'is_active' => true]);
            $added++;
        }

        $this->info("Added {$added} brand(s). Brands in total: " . Brand::count());

        return self::SUCCESS;
    }
}
