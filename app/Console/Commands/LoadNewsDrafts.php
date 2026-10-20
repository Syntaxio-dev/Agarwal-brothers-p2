<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Insight;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds the sourced News & Events articles from database/data/news/*.php.
 * Adds only missing posts (matched by slug); never changes or deletes an existing one, so it is safe to run again.
 * Each post becomes a news article made of two parts: the text and a "Sources" list. Pictures are added by hand in the admin.
 */
class LoadNewsDrafts extends Command
{
    protected $signature = 'news:load {--publish : Publish the articles (default: saved as drafts, not shown on the site)}';

    protected $description = 'Add the researched, sourced News & Events articles that ship with the project (adds only missing ones).';

    public function handle(): int
    {
        $files = glob(database_path('data/news/*.php')) ?: [];
        $added = 0;

        foreach ($files as $file) {
            foreach ((array) require $file as $post) {
                if (Insight::where('slug', $post['slug'])->exists()) {
                    continue;
                }

                $brand = filled($post['brand'] ?? null)
                    ? Brand::whereRaw('LOWER(name) = ?', [Str::lower($post['brand'])])->first()
                    : null;

                $sources = collect($post['sources'] ?? [])->map(fn ($s) => [
                    'label' => $s['label'], 'url' => $s['url'], 'accessed' => $s['accessed'] ?? null,
                ])->values()->all();

                Insight::create([
                    'type' => 'news',
                    'brand_id' => $brand?->id,
                    'title' => $post['title'],
                    'slug' => $post['slug'],
                    'excerpt' => $post['excerpt'],
                    'seo_description' => $post['seo_description'] ?? null,
                    'event_date' => $post['event_date'] ?? now()->toDateString(),
                    'is_active' => (bool) $this->option('publish'),
                    'blocks' => array_values(array_filter([
                        ['type' => 'text', 'data' => ['content' => trim($post['html'])]],
                        $sources ? ['type' => 'sources', 'data' => ['items' => $sources]] : null,
                    ])),
                ]);

                $added++;
                $this->line('  + ' . $post['title'] . (filled($post['brand'] ?? null) && ! $brand ? '  (brand "' . $post['brand'] . '" not found)' : ''));
            }
        }

        $this->info("Added {$added} news article(s)" . ($this->option('publish') ? ' (published).' : ' as drafts (switch Published on in the admin, or run with --publish).'));

        return self::SUCCESS;
    }
}
