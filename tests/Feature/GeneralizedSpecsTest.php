<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductDuplicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GeneralizedSpecsTest extends TestCase
{
    use RefreshDatabase;

    private function product(bool $general): Product
    {
        $brand = Brand::firstOrCreate(['slug' => 'sartorius'], ['name' => 'Sartorius', 'is_active' => true]);
        $cat = Category::firstOrCreate(['brand_id' => $brand->id, 'slug' => 'analytical'], ['name' => 'Analytical']);

        return Product::create([
            'category_id' => $cat->id, 'name' => 'Cubis', 'slug' => 'cubis' . ($general ? '-g' : '-s'), 'is_active' => true,
            'specs_general' => $general,
            'specs' => ['Readability' => '0.1 mg to 0.001 mg'],
        ]);
    }

    public function test_a_normal_product_shows_the_plain_specs_heading_and_no_range_note(): void
    {
        $p = $this->product(false);
        $html = $this->get(route('product.show', $p->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Technical specifications', $html);
        $this->assertStringNotContainsString('Specifications across the series', $html);
        $this->assertStringNotContainsString('ranges for the whole', $html);
    }

    public function test_a_generalized_product_says_the_values_are_ranges_for_the_line(): void
    {
        $p = $this->product(true);
        $html = $this->get(route('product.show', $p->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Specifications across the series', $html);
        $this->assertStringContainsString('ranges for the whole Cubis line', $html);
        $this->assertStringContainsString('0.1 mg to 0.001 mg', $html);
        $this->assertStringContainsString('href="#enquiry-form"', $html);
    }

    public function test_the_spec_sheet_and_the_compare_page_carry_the_note(): void
    {
        $g = $this->product(true);
        $n = $this->product(false);

        $this->get(route('product.spec-sheet', $g->slug))->assertOk()
            ->assertSee('Specifications across the series')
            ->assertSee('Exact values depend on the model');
        $this->get(route('product.spec-sheet', $n->slug))->assertOk()
            ->assertDontSee('Exact values depend on the model');

        $this->get(route('compare', ['p' => $g->slug . ',' . $n->slug]))->assertOk()
            ->assertSee('values are ranges across the series');
    }

    public function test_the_switch_is_saved_from_the_form_and_copied_by_duplicate(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $p = $this->product(false);

        Livewire::test(CreateProduct::class)
            ->fillForm(['category_id' => $p->category_id, 'name' => 'Cubis II', 'specs_general' => true, 'specs' => ['Capacity' => '120 g to 6,200 g']])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(Product::where('name', 'Cubis II')->firstOrFail()->specs_general);

        $copy = ProductDuplicator::copy(Product::where('name', 'Cubis II')->first(), 'Cubis III', $p->category_id);
        $this->assertTrue($copy->specs_general);
    }
}
