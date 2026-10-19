<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Insight;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds the sourced blog posts from database/data/blogs/*.php.
 * Adds only missing posts (matched by slug); never changes or deletes an existing one, so it is safe to run again.
 * Each post becomes a blog made of two parts: the text and a "Sources" list. Pictures are added by hand in the admin.
 */
class LoadBlogDrafts extends Command
{
    protected $signature = 'blogs:load {--publish : Publish the posts (default: saved as drafts, not shown on the site)}';

    protected $description = 'Add the researched, sourced blog posts that ship with the project (adds only missing ones).';

    public function handle(): int
    {
        $files = glob(database_path('data/blogs/*.php')) ?: [];
        $added = 0;

        foreach ($files as $file) {
            foreach ((array) require $file as $post) {
                if (Insight::where('slug', $post['slug'])->exists()) {
                    continue;
                }

                $brand = Brand::whereRaw('LOWER(name) = ?', [Str::lower($post['brand'])])->first();

                $sources = collect($post['sources'] ?? [])->map(fn ($s) => [
                    'label' => $s['label'], 'url' => $s['url'], 'accessed' => $s['accessed'] ?? null,
                ])->values()->all();

                Insight::create([
                    'type' => 'blog',
                    'brand_id' => $brand?->id,
                    'title' => $post['title'],
                    'slug' => $post['slug'],
                    'excerpt' => $post['excerpt'],
                    'seo_description' => $post['seo_description'] ?? null,
                    'event_date' => now()->toDateString(),
                    'is_active' => (bool) $this->option('publish'),
                    'blocks' => array_values(array_filter([
                        ['type' => 'text', 'data' => ['content' => trim($post['html'])]],
                        $sources ? ['type' => 'sources', 'data' => ['items' => $sources]] : null,
                    ])),
                ]);

                $added++;
                $this->line('  + ' . $post['title'] . ($brand ? '' : '  (brand "' . $post['brand'] . '" not found)'));
            }
        }

        $this->info("Added {$added} blog post(s)" . ($this->option('publish') ? ' (published).' : ' as drafts (switch Published on in the admin, or run with --publish).'));

        return self::SUCCESS;
    }
}
