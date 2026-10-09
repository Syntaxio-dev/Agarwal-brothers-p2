<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Insight;
use App\Models\JobOpening;
use App\Models\Product;
use App\Models\Vertical;
use App\Support\Seo;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $xml = Cache::remember('sitemap.xml.' . md5((string) config('app.url')), 3600, fn () => $this->buildSitemap());

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *'];

        if (Seo::indexable()) {
            $lines[] = 'Disallow: /admin';
            $lines[] = 'Disallow: /search';
            $lines[] = '';
            $lines[] = 'Sitemap: ' . Seo::absolute('sitemap.xml');
        } else {
            // Local / staging: keep crawlers out entirely.
            $lines[] = 'Disallow: /';
        }

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function buildSitemap(): string
    {
        $urls = [];

        $add = function (string $path, $lastmod = null, string $priority = '0.6') use (&$urls) {
            $urls[] = [
                'loc' => Seo::absolute($path),
                'lastmod' => $lastmod ? $lastmod->toAtomString() : null,
                'priority' => $priority,
            ];
        };

        foreach ([
            ['/', '1.0'], ['/our-story', '0.7'], ['/verticals', '0.9'],
            ['/insights/blogs', '0.7'], ['/insights/news-events', '0.7'], ['/insights/webinars', '0.7'],
            ['/application-resources', '0.6'], ['/careers', '0.5'], ['/contact-us', '0.6'],
        ] as [$path, $priority]) {
            $add($path, null, $priority);
        }

        Vertical::where('is_active', true)->orderBy('sort_order')->get()
            ->each(fn ($v) => $add(route('vertical.show', $v->slug, false), $v->updated_at, '0.8'));

        Brand::where('is_active', true)->orderBy('name')->get()
            ->each(fn ($b) => $add(route('brand.show', $b->slug, false), $b->updated_at, '0.7'));

        Category::with('brand')->whereHas('brand', fn ($q) => $q->where('is_active', true))->get()
            ->each(fn ($c) => $add(route('category.show', [$c->brand->slug, $c->slug], false), $c->updated_at, '0.7'));

        Product::with('category.brand')->where('is_active', true)
            ->whereHas('category.brand', fn ($q) => $q->where('is_active', true))->get()
            ->each(fn ($p) => $add(route('product.show', $p->slug, false), $p->updated_at, '0.8'));

        Insight::where('is_active', true)->get()
            ->each(fn ($i) => $add(route('insights.show', $i->slug, false), $i->updated_at, '0.5'));

        JobOpening::where('is_active', true)->get()
            ->each(fn ($j) => $add(route('careers.show', $j->slug, false), $j->updated_at, '0.4'));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . htmlspecialchars($u['loc'], ENT_XML1) . "</loc>\n";
            if ($u['lastmod']) {
                $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n  </url>\n";
        }

        return $xml . '</urlset>' . "\n";
    }
}
