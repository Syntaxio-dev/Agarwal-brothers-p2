<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vertical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances']);

        return Product::create(['category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true]);
    }

    public function test_catalogue_pages_have_breadcrumbs_with_structured_data(): void
    {
        $p = $this->product();
        $v = Vertical::create(['name' => 'Weighing', 'slug' => 'weighing', 'is_active' => true]);
        $p->category->verticals()->attach($v);

        $urls = [
            route('verticals.index'),
            route('vertical.show', $v->slug),
            route('brand.show', $p->category->brand->slug),
            route('category.show', [$p->category->brand->slug, $p->category->slug]),
            route('product.show', $p->slug),
        ];

        foreach ($urls as $url) {
            $res = $this->get($url)->assertOk()->assertSee('aria-label="Breadcrumb"', false)->assertSee('BreadcrumbList', false);
            $this->assertStringContainsString('"position":1', $res->getContent());
        }
    }

    public function test_breadcrumb_data_lists_the_trail_in_order(): void
    {
        $p = $this->product();
        $html = $this->get(route('product.show', $p->slug))->getContent();

        preg_match('~<script type="application/ld\+json">(\{"@context":"https://schema.org","@type":"BreadcrumbList".*?\})</script>~s', $html, $m);
        $data = json_decode($m[1] ?? '{}', true);

        $names = collect($data['itemListElement'] ?? [])->pluck('name')->all();
        $this->assertSame(['Home', 'Sartorius', 'Balances', 'Entris II'], $names);
        $this->assertArrayNotHasKey('item', end($data['itemListElement']));  // current page has no link
    }

    public function test_product_page_has_back_link_and_records_recently_viewed(): void
    {
        $p = $this->product();

        $this->get(route('product.show', $p->slug))->assertOk()
            ->assertSee('backLink', false)
            ->assertSee('Back to Balances')
            ->assertSee('$store.recent.add', false);
    }

    public function test_recently_viewed_card_is_on_pages_but_not_on_compare(): void
    {
        $this->get('/')->assertOk()->assertSee('Recently viewed');
        $this->get(route('compare'))->assertOk()->assertDontSee('Recently viewed');
    }
}
