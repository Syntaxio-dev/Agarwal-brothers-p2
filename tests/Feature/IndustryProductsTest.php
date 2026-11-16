<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndustryProductsTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, bool $active = true, bool $brandActive = true): Product
    {
        $brand = Brand::create(['name' => 'B ' . $name, 'slug' => 'b-' . str($name)->slug(), 'is_active' => $brandActive]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Line ' . $name, 'slug' => 'line-' . str($name)->slug()]);

        return Product::create(['category_id' => $cat->id, 'name' => $name, 'slug' => str($name)->slug(), 'is_active' => $active]);
    }

    public function test_an_industry_gets_a_web_address_from_its_name(): void
    {
        $a = ClientCategory::create(['name' => 'Dairy & Milk', 'is_active' => true]);
        $b = ClientCategory::create(['name' => 'Dairy Milk', 'is_active' => true]);

        $this->assertSame('dairy-milk', $a->slug);
        $this->assertSame('dairy-milk-2', $b->slug);
    }

    public function test_the_industry_page_lists_only_live_products_of_that_industry(): void
    {
        $industry = ClientCategory::create(['name' => 'Pharma', 'is_active' => true]);
        $other = ClientCategory::create(['name' => 'Cement', 'is_active' => true]);
        $shown = $this->product('Balance One');
        $hidden = $this->product('Balance Hidden', active: false);
        $deadBrand = $this->product('Balance Brandless', brandActive: false);
        $elsewhere = $this->product('Balance Cement');
        foreach ([$shown, $hidden, $deadBrand] as $p) {
            $industry->products()->attach($p->id);
        }
        $other->products()->attach($elsewhere->id);

        $this->get(route('industry.show', 'pharma'))
            ->assertOk()
            ->assertSee('Instruments used in Pharma')
            ->assertSee('Balance One')
            ->assertDontSee('Balance Hidden')
            ->assertDontSee('Balance Brandless')
            ->assertDontSee('Balance Cement');
    }

    public function test_an_industry_without_products_or_a_switched_off_one_is_handled(): void
    {
        ClientCategory::create(['name' => 'Mining', 'is_active' => true]);
        ClientCategory::create(['name' => 'Hidden Sector', 'is_active' => false]);

        $this->get(route('industry.show', 'mining'))->assertOk()->assertSee('Need instruments for Mining');
        $this->get(route('industry.show', 'hidden-sector'))->assertNotFound();
    }

    public function test_the_home_wheel_links_to_the_industry_page_and_lists_industries_with_only_products(): void
    {
        $withClients = ClientCategory::create(['name' => 'Dairy', 'is_active' => true]);
        Client::create(['name' => 'Some Dairy', 'client_category_id' => $withClients->id, 'is_active' => true]);
        $onlyProducts = ClientCategory::create(['name' => 'Textiles', 'is_active' => true]);
        $onlyProducts->products()->attach($this->product('Textile Tester')->id);
        ClientCategory::create(['name' => 'Empty Sector', 'is_active' => true]);

        $html = str_replace('\\', '',$this->get('/')->assertOk()->getContent()); // the wheel list is JSON, the page escapes the slashes

        $this->assertStringContainsString(route('industry.show', 'dairy'), $html);
        $this->assertStringContainsString(route('industry.show', 'textiles'), $html);
        $this->assertStringNotContainsString('Empty Sector', $html);
        $this->assertStringContainsString('wheelHover', $html);
    }

    public function test_the_product_form_saves_industries_and_can_add_a_new_one(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $brand = Brand::create(['name' => 'Acme', 'slug' => 'acme', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Line', 'slug' => 'line']);
        $old = ClientCategory::create(['name' => 'Pharma', 'is_active' => true]);

        Livewire::test(CreateProduct::class)
            ->callAction(TestAction::make('createOption')->schemaComponent('industries', schema: 'form'), data: ['name' => 'Brand New Industry'])
            ->assertHasNoFormErrors();

        $new = ClientCategory::where('name', 'Brand New Industry')->firstOrFail();
        $this->assertTrue($new->is_active);
        $this->assertSame('brand-new-industry', $new->slug);

        Livewire::test(CreateProduct::class)
            ->fillForm(['category_id' => $cat->id, 'name' => 'Tester', 'slug' => 'tester', 'industries' => [$old->id, $new->id]])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertEqualsCanonicalizing([$old->id, $new->id], Product::where('slug', 'tester')->firstOrFail()->industries()->pluck('client_categories.id')->all());
    }
}
