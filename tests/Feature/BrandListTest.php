<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandListTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_list_has_clean_unique_brand_names(): void
    {
        $list = require database_path('data/brands.php');

        $this->assertGreaterThan(50, count($list));
        $this->assertCount(count($list), array_unique(array_map('strtolower', $list)));
        foreach ($list as $name) {
            $this->assertSame($name, trim($name));
            $this->assertStringNotContainsString('(', $name);       // the "bought through" part of the sheet is not part of the name
        }
        foreach (['Anton Paar', 'Sartorius', 'Tempo', 'Labindia', 'Falex', 'TA Instruments'] as $name) {
            $this->assertContains($name, $list);
        }
    }

    public function test_brands_load_adds_the_missing_brands_and_never_touches_existing_ones(): void
    {
        $list = require database_path('data/brands.php');
        Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'description' => 'My own text', 'is_active' => false]);

        $this->artisan('brands:load')->assertSuccessful();

        $this->assertSame(count($list), Brand::count());
        $kept = Brand::where('slug', 'sartorius')->first();
        $this->assertSame('My own text', $kept->description);
        $this->assertFalse($kept->is_active);

        $this->artisan('brands:load')->assertSuccessful();          // second run adds nothing
        $this->assertSame(count($list), Brand::count());
    }
}
