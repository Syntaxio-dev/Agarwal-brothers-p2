<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\Vertical;
use App\Support\SimilarProducts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimilarProductsTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $brandName, string $line, string $name, array $specs, bool $active = true, bool $brandActive = true): Product
    {
        $brand = Brand::firstOrCreate(['slug' => str($brandName)->slug()], ['name' => $brandName, 'is_active' => $brandActive]);
        $cat = Category::firstOrCreate(['brand_id' => $brand->id, 'slug' => str($line)->slug()], ['name' => $line]);

        return Product::create([
            'category_id' => $cat->id, 'name' => $name, 'slug' => str($name)->slug(), 'is_active' => $active, 'specs' => $specs,
        ]);
    }

    public function test_a_balance_from_another_brand_with_close_specs_is_suggested_first(): void
    {
        $mine = $this->product('Sartorius', 'Analytical Balances', 'Entris II', ['Capacity' => '220 g', 'Readability' => '0.1 mg']);
        $mettler = $this->product('Mettler Toledo', 'Analytical Balances', 'XPR205', ['Capacity' => '200 g', 'Readability' => '0.01 mg']);
        $this->product('Hettich', 'Centrifuges', 'Rotanta', ['Speed' => '6000 rpm', 'Capacity' => '4 x 750 ml']);

        $similar = SimilarProducts::for($mine);

        $this->assertSame($mettler->id, $similar->first()['product']->id);
        $this->assertSame('Same type, other brand', $similar->first()['reason']);
        $this->assertNotContains('Entris II', $similar->pluck('product.name')->all(), 'the product itself is never suggested');
    }

    public function test_hidden_products_and_unrelated_ones_are_not_suggested(): void
    {
        $mine = $this->product('Sartorius', 'Analytical Balances', 'Entris II', ['Capacity' => '220 g']);
        $this->product('Mettler Toledo', 'Analytical Balances', 'Off', ['Capacity' => '200 g'], active: false);
        $this->product('Ohaus', 'Analytical Balances', 'Hidden brand', ['Capacity' => '200 g'], brandActive: false);
        $this->product('Hettich', 'Centrifuges', 'Rotanta', ['Speed' => '6000 rpm']);

        $this->assertCount(0, SimilarProducts::for($mine));
    }

    public function test_same_product_line_tops_the_list_up_when_few_are_alike(): void
    {
        $mine = $this->product('Sartorius', 'Analytical Balances', 'Entris II', []);
        $sibling = $this->product('Sartorius', 'Analytical Balances', 'Cubis', []);

        $row = SimilarProducts::for($mine)->first();

        $this->assertSame($sibling->id, $row['product']->id);
        $this->assertSame('Same product line', $row['reason']);
    }

    public function test_the_shared_vertical_adds_to_the_score(): void
    {
        $vertical = Vertical::create(['name' => 'Weighing', 'slug' => 'weighing', 'is_active' => true]);
        $mine = $this->product('Sartorius', 'Analytical Balances', 'Entris II', ['Capacity' => '220 g']);
        $other = $this->product('Mettler Toledo', 'Precision Balances', 'XS', ['Capacity' => '210 g']);
        $unrelated = $this->product('Hettich', 'Centrifuges', 'Rotanta', ['Capacity' => '4 x 750 ml']);
        $mine->category->verticals()->attach($vertical);
        $other->category->verticals()->attach($vertical);

        $names = SimilarProducts::for($mine)->pluck('product.name')->all();

        $this->assertContains('XS', $names);
        $this->assertNotContains('Rotanta', $names);
    }

    public function test_product_page_lists_similar_products_with_the_reason(): void
    {
        $mine = $this->product('Sartorius', 'Analytical Balances', 'Entris II', ['Capacity' => '220 g']);
        $this->product('Mettler Toledo', 'Analytical Balances', 'XPR205', ['Capacity' => '200 g']);

        $this->get(route('product.show', $mine->slug))
            ->assertOk()
            ->assertSee('Similar products')
            ->assertSee('XPR205')
            ->assertSee('Same type, other brand')
            ->assertDontSee('Customers also compared');
    }

    public function test_comparing_products_is_counted_once_per_visitor_and_shown_after_two_visitors(): void
    {
        $a = $this->product('Sartorius', 'Analytical Balances', 'Entris II', []);
        $b = $this->product('Mettler Toledo', 'Analytical Balances', 'XPR205', []);
        $url = route('compare', ['p' => $a->slug . ',' . $b->slug]);

        $this->get($url)->assertOk();
        $this->assertSame(1, ProductComparison::where('product_id', $a->id)->where('other_product_id', $b->id)->value('times'));
        $this->assertSame(1, ProductComparison::where('product_id', $b->id)->where('other_product_id', $a->id)->value('times'));

        // the same visitor reloading does not count again
        $this->get($url)->assertOk();
        $this->assertSame(1, ProductComparison::where('product_id', $a->id)->value('times'));

        // one visitor is not enough to say "customers compared"
        $this->get(route('product.show', $a->slug))->assertDontSee('Customers also compared');

        // a second visitor (fresh session)
        $this->flushSession();
        $this->get($url)->assertOk();
        $this->assertSame(2, ProductComparison::where('product_id', $a->id)->value('times'));
        $this->get(route('product.show', $a->slug))->assertSee('Customers also compared')->assertSee('XPR205');
    }

    public function test_comparing_one_product_or_none_records_nothing(): void
    {
        $a = $this->product('Sartorius', 'Analytical Balances', 'Entris II', []);

        $this->get(route('compare', ['p' => $a->slug]))->assertOk();
        $this->get(route('compare'))->assertOk();

        $this->assertSame(0, ProductComparison::count());
    }
}
