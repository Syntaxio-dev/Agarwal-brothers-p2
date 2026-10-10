<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, array $specs, bool $brandActive = true): Product
    {
        $brand = Brand::create(['name' => 'B ' . $name, 'slug' => 'b-' . uniqid(), 'is_active' => $brandActive]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'bal-' . uniqid()]);

        return Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => 'p-' . uniqid(), 'is_active' => true, 'specs' => $specs]);
    }

    public function test_two_products_are_compared_with_differences_marked(): void
    {
        $a = $this->product('Quintix 224', ['Capacity' => '220 g', 'Readability' => '0.1 mg']);
        $b = $this->product('Pioneer PX224', ['capacity' => '220 g', 'Readability' => '0.2 mg', 'Pan size' => '90 mm']);

        $res = $this->get(route('compare', ['p' => $a->slug . ',' . $b->slug]))->assertOk();

        $res->assertSee('Quintix 224')->assertSee('Pioneer PX224')
            ->assertSee('0.1 mg')->assertSee('0.2 mg')->assertSee('90 mm');
        // "Capacity"/"capacity" are merged into one row, so the label appears once.
        $this->assertSame(1, substr_count(strtolower($res->getContent()), '>capacity<') + substr_count(strtolower($res->getContent()), 'capacity' . "\n"));
        $res->assertSee('noindex', false);
    }

    public function test_single_or_unknown_products_show_the_prompt(): void
    {
        $a = $this->product('Solo', ['X' => '1']);

        $this->get(route('compare', ['p' => $a->slug]))->assertOk()->assertSee('Pick at least two products');
        $this->get(route('compare', ['p' => 'nope,also-nope']))->assertOk()->assertSee('Pick at least two products');
        $this->get(route('compare'))->assertOk();
    }

    public function test_hidden_brand_products_and_bad_input_are_ignored(): void
    {
        $a = $this->product('Visible A', ['X' => '1']);
        $b = $this->product('Visible B', ['X' => '2']);
        $hidden = $this->product('Secret Model', ['X' => '3'], brandActive: false);

        $this->get(route('compare', ['p' => "{$a->slug},{$b->slug},{$hidden->slug},<script>"]))
            ->assertOk()->assertDontSee('Secret Model')->assertSee('Visible A');
    }

    public function test_at_most_four_products_are_shown(): void
    {
        $ps = collect(range(1, 5))->map(fn ($i) => $this->product("Model {$i}", ['X' => (string) $i]));

        $this->get(route('compare', ['p' => $ps->pluck('slug')->implode(',')]))
            ->assertOk()->assertSee('Model 4')->assertDontSee('Model 5');
    }

    public function test_cards_offer_the_compare_button(): void
    {
        $a = $this->product('Quintix 224', ['X' => '1']);

        $this->get(route('brand.show', $a->category->brand->slug))->assertOk()->assertSee('data-compare', false);
        $this->get(route('product.show', $a->slug))->assertOk()->assertSee('data-compare', false);
        $this->get(route('category.show', [$a->category->brand->slug, $a->category->slug]))->assertOk()->assertSee('data-compare', false);
        $this->get('/search?q=Quintix')->assertOk()->assertSee('data-compare', false);
    }

    public function test_live_suggestions_return_matching_rows_only(): void
    {
        $a = $this->product('Quintix 224', ['X' => '1']);
        $hidden = $this->product('Quintix Secret', ['X' => '1'], brandActive: false);

        $res = $this->getJson(route('search.suggest', ['q' => 'Quintix']))->assertOk();
        $labels = collect($res->json('rows'))->pluck('label');

        $this->assertTrue($labels->contains('Quintix 224'));
        $this->assertFalse($labels->contains('Quintix Secret'));
        $this->assertSame(route('product.show', $a->slug), collect($res->json('rows'))->firstWhere('label', 'Quintix 224')['url']);

        // Too short or empty queries return nothing; wildcards are treated literally.
        $this->getJson(route('search.suggest', ['q' => 'Q']))->assertOk()->assertJson(['rows' => []]);
        $this->getJson(route('search.suggest', ['q' => '%%']))->assertOk()->assertJson(['rows' => []]);
    }
}
