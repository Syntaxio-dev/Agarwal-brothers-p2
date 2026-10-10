<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Livewire\GlobalSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSearchUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_box_is_in_the_admin_top_bar_and_uses_our_results(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances']);
        Product::create(['category_id' => $cat->id, 'name' => 'Quintix 224', 'slug' => 'quintix-224', 'is_active' => true]);

        $this->get('/admin')->assertOk()->assertSee('fi-global-search', false);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'quintix')
            ->assertSee('Products')
            ->assertSee('Quintix 224');

        Livewire::test(GlobalSearch::class)
            ->set('search', 'how to add a brand')
            ->assertSee('How-to guide')
            ->assertSee('/admin/help#catalogue', false);
    }
}
