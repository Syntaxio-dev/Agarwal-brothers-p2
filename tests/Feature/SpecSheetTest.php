<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpecSheetTest extends TestCase
{
    use RefreshDatabase;

    private function product(bool $active = true, bool $brandActive = true): Product
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius-' . uniqid(), 'is_active' => $brandActive]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances-' . uniqid()]);

        return Product::create([
            'category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii-' . uniqid(), 'is_active' => $active,
            'short_description' => 'Precision balance',
            'specs' => ['Capacity' => '220 g', 'Readability' => '0.1 mg'],
            'features' => [['title' => 'Touch screen', 'text' => 'Easy to read']],
        ]);
    }

    public function test_the_sheet_lists_the_product_specs_with_branding_and_is_not_indexed(): void
    {
        $p = $this->product();

        $this->get(route('product.spec-sheet', $p->slug))
            ->assertOk()
            ->assertSee('Entris II')
            ->assertSee('Capacity')
            ->assertSee('220 g')
            ->assertSee('Touch screen')
            ->assertSee('Agarwal Brothers')
            ->assertSee('Print / Save as PDF')
            ->assertSee('Copy specs')
            ->assertSee('noindex', false);
    }

    public function test_the_copy_text_contains_the_specs_as_plain_lines(): void
    {
        $p = $this->product();

        $html = $this->get(route('product.spec-sheet', $p->slug))->getContent();

        $this->assertStringContainsString('Capacity: 220 g', $html);
        $this->assertStringContainsString('Readability: 0.1 mg', $html);
    }

    public function test_hidden_products_have_no_sheet(): void
    {
        $this->get(route('product.spec-sheet', $this->product(false)->slug))->assertNotFound();
        $this->get(route('product.spec-sheet', $this->product(true, false)->slug))->assertNotFound();
        $this->get('/products/nothing-here/spec-sheet')->assertNotFound();
    }

    public function test_the_product_page_links_to_the_sheet_and_opens_the_print_dialog(): void
    {
        $p = $this->product();

        $this->get(route('product.show', $p->slug))
            ->assertOk()
            ->assertSee(route('product.spec-sheet', $p->slug) . '?print=1', false)
            ->assertSee('Spec sheet');

        $this->get(route('product.spec-sheet', $p->slug) . '?print=1')->assertOk()->assertSee('window.print()', false);
    }
}
