<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vertical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ImageFitTest extends TestCase
{
    use RefreshDatabase;

    private function catalogue(): array
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'logo' => 'brands/sartorius.svg', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'BSA Series', 'slug' => 'bsa-series']);
        $vertical = Vertical::create(['name' => 'Balances', 'slug' => 'balances', 'is_active' => true]);
        $vertical->categories()->attach($cat);
        $product = Product::create(['category_id' => $cat->id, 'name' => 'Standard Balance', 'slug' => 'standard-balance', 'is_active' => true, 'image' => 'products/a.png']);

        return [$brand, $cat, $vertical, $product];
    }

    public function test_the_brand_logo_on_a_vertical_page_has_a_box_with_a_real_width(): void
    {
        [, , $vertical] = $this->catalogue();

        $html = $this->get(route('vertical.show', $vertical->slug))->assertOk()->getContent();

        // a width-less box collapsed to 0 px and hid the logo (SVG logos have no fixed size)
        $this->assertStringContainsString('class="flex h-12 w-40 min-w-0 items-center"', $html);
        $this->assertStringContainsString('storage/brands/sartorius.svg', $html);
        $this->assertStringContainsString('max-h-full max-w-full object-contain object-left', $html);
    }

    public function test_product_photos_blend_into_their_box_and_fill_it_on_every_listing(): void
    {
        [$brand, $cat, , $product] = $this->catalogue();

        $category = $this->get(route('category.show', [$brand->slug, $cat->slug]))->assertOk()->getContent();
        $this->assertStringContainsString('flex h-48 items-center justify-center bg-ice p-2', $category);
        $this->assertStringContainsString('max-h-full max-w-full mix-blend-multiply object-contain', $category);
        $this->assertStringNotContainsString('bg-ice p-5', $category);

        $brandPage = $this->get(route('brand.show', $brand->slug))->assertOk()->getContent();
        $this->assertStringContainsString('h-40 bg-ice flex items-center justify-center p-2', $brandPage);
        $this->assertStringContainsString('mix-blend-multiply', $brandPage);

        $search = $this->get('/search?q=Standard')->assertOk()->getContent();
        $this->assertStringContainsString('relative flex h-48 items-center justify-center bg-ice p-2', $search);
        $this->assertStringContainsString('mix-blend-multiply', $search);

        $page = $this->get(route('product.show', $product->slug))->assertOk()->getContent();
        $this->assertStringContainsString('mix-blend-multiply object-contain drop-shadow-xl', $page);   // the big photo too
    }

    public function test_the_category_form_and_list_no_longer_have_the_unused_image(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateCategory::class)
            ->assertFormFieldExists('name')
            ->assertFormFieldExists('verticals')
            ->assertFormFieldExists('sort_order')
            ->assertFormFieldDoesNotExist('image');

        Livewire::test(ListCategories::class)->assertTableColumnDoesNotExist('image');
    }
}
