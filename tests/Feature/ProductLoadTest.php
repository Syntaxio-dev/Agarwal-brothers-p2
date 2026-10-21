<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductLoadTest extends TestCase
{
    use RefreshDatabase;

    private function category(): Category
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);

        return Category::create(['brand_id' => $brand->id, 'name' => 'General Laboratory Weighing [BSA Series]', 'slug' => 'bsa', 'is_active' => true]);
    }

    public function test_it_adds_hidden_drafts_following_the_upload_rules(): void
    {
        $category = $this->category();

        $this->artisan('products:load')->assertSuccessful();

        $this->assertSame(28, Product::count());
        $this->assertSame(0, Product::where('is_active', true)->count());

        $p = Product::where('slug', 'bsa224s-cw')->firstOrFail();
        $this->assertSame($category->id, $p->category_id);
        $this->assertSame('BSA224S-CW', $p->name);
        $this->assertSame('BSA224S-CW Standard Laboratory Balance', $p->heading);
        $this->assertSame('BSA Series', $p->model_group);
        $this->assertSame('Internal', $p->specs['Calibration and adjustment']);
        $this->assertSame('∅ 90', $p->specs['Pan size (mm)']);
        $this->assertCount(8, $p->specs);
        $this->assertCount(5, $p->features);
        $this->assertCount(3, $p->advantages);
        $this->assertCount(4, $p->faqs);
        $this->assertSame('Brochure', $p->documents[0]['type']);
        $this->assertEmpty($p->image);
        $this->assertSame('180 × 180', Product::where('slug', 'bsa2202s')->first()->specs['Pan size (mm)']);
    }

    public function test_second_run_changes_nothing_and_the_owners_example_is_not_duplicated(): void
    {
        $category = $this->category();
        $example = Product::create([
            'category_id' => $category->id, 'name' => 'Standard Laboratory Balances', 'slug' => 'standard-laboratory-balances',
            'specs' => ['Model' => 'BSA224S'], 'is_active' => true,
        ]);

        $this->artisan('products:load')->assertSuccessful();
        $this->artisan('products:load')->assertSuccessful();

        $this->assertSame(28, Product::count());
        $this->assertSame(1, Product::whereJsonContains('specs->Model', 'BSA224S')->count());
        $this->assertTrue($example->fresh()->is_active);
        $this->assertSame('Standard Laboratory Balances', $example->fresh()->name);
    }

    public function test_it_skips_when_the_category_is_missing(): void
    {
        $this->artisan('products:load')->assertSuccessful();
        $this->assertSame(0, Product::count());
    }
}
