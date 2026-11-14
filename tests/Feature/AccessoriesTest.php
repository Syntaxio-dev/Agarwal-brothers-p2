<?php

namespace Tests\Feature;

use App\Filament\Resources\Accessories\Pages\CreateAccessory;
use App\Filament\Resources\Accessories\Pages\ListAccessories;
use App\Models\Accessory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\User;
use App\Models\Vertical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccessoriesTest extends TestCase
{
    use RefreshDatabase;

    private function brand(string $name = 'Sartorius', bool $active = true): Brand
    {
        return Brand::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'is_active' => $active]);
    }

    private function product(Brand $brand): Product
    {
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances-' . $brand->id]);

        return Product::create(['category_id' => $cat->id, 'name' => 'Cubis II', 'slug' => 'cubis-ii-' . $brand->id, 'is_active' => true]);
    }

    private function accessory(string $name, ?Brand $brand = null, array $extra = []): Accessory
    {
        return Accessory::create($extra + ['name' => $name, 'brand_id' => $brand?->id, 'is_active' => true]);
    }

    public function test_the_slug_is_made_from_the_name_and_kept_unique(): void
    {
        $a = $this->accessory('Calibration Test Kit');
        $b = $this->accessory('Calibration Test Kit');

        $this->assertSame('calibration-test-kit', $a->slug);
        $this->assertSame('calibration-test-kit-2', $b->slug);
        $this->assertSame('accessory--calibration-test-kit', $a->listKey());
    }

    public function test_a_brand_gets_its_own_accessories_first_then_the_general_ones(): void
    {
        $sartorius = $this->brand();
        $other = $this->brand('Ohaus');
        $this->accessory('General cloth');                       // general
        $this->accessory('Ohaus weight set', $other);            // another brand: never shown
        $this->accessory('Sartorius draft ring', $sartorius);
        $this->accessory('Hidden kit', $sartorius, ['is_active' => false]);

        $names = Accessory::forBrand($sartorius)->pluck('name')->all();

        $this->assertSame(['Sartorius draft ring', 'General cloth'], $names);
    }

    public function test_accessories_of_a_switched_off_brand_are_not_shown(): void
    {
        $off = $this->brand('Old Brand', false);
        $this->accessory('Old kit', $off);

        $this->assertCount(0, Accessory::visible()->get());
    }

    public function test_the_brand_accessories_page_lists_both_groups_with_add_buttons(): void
    {
        $brand = $this->brand();
        $this->accessory('Sartorius draft ring', $brand, ['short_description' => 'Keeps air away']);
        $this->accessory('General cloth');

        $html = $this->get(route('brand.accessories', $brand->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Sartorius Accessories', $html);
        $this->assertStringContainsString('For Sartorius instruments', $html);
        $this->assertStringContainsString('General accessories for any brand', $html);
        $this->assertStringContainsString('Sartorius draft ring', $html);
        $this->assertStringContainsString('Keeps air away', $html);
        $this->assertStringContainsString('accessory--sartorius-draft-ring', $html);
        $this->assertStringContainsString('+ Add to list', $html);
    }

    public function test_the_accessories_page_is_not_mistaken_for_a_product_line(): void
    {
        $brand = $this->brand();
        Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances']);

        $this->get(route('brand.accessories', $brand->slug))->assertOk();
        $this->get('/brands/' . $brand->slug . '/balances')->assertOk();
    }

    public function test_a_switched_off_brand_has_no_accessories_page(): void
    {
        $off = $this->brand('Old Brand', false);

        $this->get(route('brand.accessories', $off->slug))->assertNotFound();
    }

    public function test_the_product_page_offers_add_accessories_only_when_there_are_some(): void
    {
        $brand = $this->brand();
        $product = $this->product($brand);

        $this->assertStringNotContainsString('Add accessories', $this->get(route('product.show', $product->slug))->getContent());

        $this->accessory('Sartorius draft ring', $brand);
        $this->accessory('General cloth');
        $html = $this->get(route('product.show', $product->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Add accessories', $html);
        $this->assertStringContainsString('Accessories for Cubis II', $html);
        $this->assertStringContainsString('Sartorius draft ring', $html);
        $this->assertStringContainsString('Suits any brand', $html);
        $this->assertLessThan(strpos($html, 'General cloth'), strpos($html, 'Sartorius draft ring'), 'the brand accessory comes first');
        $this->assertStringContainsString(route('brand.accessories', $brand->slug), $html);
    }

    public function test_the_vertical_page_links_to_the_accessories_of_each_brand(): void
    {
        $brand = $this->brand();
        $other = $this->brand('Ohaus');
        $vertical = Vertical::create(['name' => 'Weighing', 'slug' => 'weighing', 'is_active' => true]);
        foreach ([$brand, $other] as $b) {
            $cat = Category::create(['brand_id' => $b->id, 'name' => 'Lines ' . $b->name, 'slug' => 'lines-' . $b->id]);
            $vertical->categories()->attach($cat);
        }

        // nothing yet: no button
        $html = $this->get(route('vertical.show', $vertical->slug))->assertOk()->getContent();
        $this->assertStringNotContainsString('/accessories', $html);

        // only Sartorius has an accessory: only its row gets the button
        $this->accessory('Sartorius draft ring', $brand);
        $html = $this->get(route('vertical.show', $vertical->slug))->getContent();
        $this->assertStringContainsString(route('brand.accessories', $brand->slug), $html);
        $this->assertStringNotContainsString(route('brand.accessories', $other->slug), $html);

        // a general accessory suits every brand
        $this->accessory('General cloth');
        $html = $this->get(route('vertical.show', $vertical->slug))->getContent();
        $this->assertStringContainsString(route('brand.accessories', $other->slug), $html);
    }

    public function test_the_brand_page_has_an_accessories_button_when_there_are_accessories(): void
    {
        $brand = $this->brand();

        $this->assertStringNotContainsString('Sartorius accessories', $this->get(route('brand.show', $brand->slug))->getContent());

        $this->accessory('Sartorius draft ring', $brand);
        $this->get(route('brand.show', $brand->slug))->assertSee('Sartorius accessories');
    }

    public function test_accessories_travel_in_the_enquiry_list_and_are_saved_as_items(): void
    {
        $brand = $this->brand();
        $product = $this->product($brand);
        $kit = $this->accessory('Calibration Test Kit', $brand);
        $general = $this->accessory('General cloth');

        $this->post(route('enquiry-list.store'), [
            'name' => 'Ravi Sharma', 'email' => 'ravi@example.com', 'phone' => '9999999999',
            'items' => [
                ['slug' => $product->slug, 'qty' => 1],
                ['slug' => $kit->listKey(), 'qty' => 3],
                ['slug' => $general->listKey(), 'qty' => 2],
            ],
        ])->assertRedirect(route('enquiry-list'))->assertSessionHas('enquiry_sent', 3);

        $items = Enquiry::firstOrFail()->items;
        $this->assertCount(3, $items);

        $acc = $items->firstWhere('product_name', 'Calibration Test Kit');
        $this->assertNull($acc->product_id);
        $this->assertSame('Accessory', $acc->category_name);
        $this->assertSame('Sartorius', $acc->brand_name);
        $this->assertSame(3, $acc->quantity);
        $this->assertNull($items->firstWhere('product_name', 'General cloth')->brand_name);
        $this->assertSame($product->id, $items->firstWhere('product_name', 'Cubis II')->product_id);
    }

    public function test_a_list_with_only_accessories_can_be_sent_and_unavailable_ones_are_ignored(): void
    {
        $brand = $this->brand();
        $kit = $this->accessory('Calibration Test Kit', $brand);
        $off = $this->accessory('Old kit', $brand, ['is_active' => false]);

        $this->post(route('enquiry-list.store'), [
            'name' => 'Ravi Sharma', 'email' => 'ravi@example.com', 'phone' => '9999999999',
            'items' => [['slug' => $kit->listKey(), 'qty' => 1], ['slug' => $off->listKey(), 'qty' => 1], ['slug' => 'accessory--nothing-here', 'qty' => 1]],
        ])->assertSessionHas('enquiry_sent', 1);

        $this->assertSame(['Calibration Test Kit'], Enquiry::firstOrFail()->items->pluck('product_name')->all());
    }

    public function test_a_list_of_unavailable_accessories_only_is_refused(): void
    {
        $off = $this->accessory('Old kit', $this->brand(), ['is_active' => false]);

        $this->post(route('enquiry-list.store'), [
            'name' => 'Ravi Sharma', 'email' => 'ravi@example.com', 'phone' => '9999999999',
            'items' => [['slug' => $off->listKey(), 'qty' => 1]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Enquiry::count());
    }

    public function test_the_list_page_does_not_link_an_accessory_to_a_product_page(): void
    {
        $html = $this->get(route('enquiry-list'))->assertOk()->getContent();

        $this->assertStringContainsString("p.type === 'accessory'", $html);
    }

    public function test_admins_and_editors_manage_accessories_and_hr_does_not(): void
    {
        $brand = $this->brand();

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        Livewire::test(CreateAccessory::class)
            ->fillForm(['name' => 'Calibration Test Kit', 'brand_id' => $brand->id, 'short_description' => 'Checks the balance', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('accessories', ['name' => 'Calibration Test Kit', 'brand_id' => $brand->id, 'slug' => 'calibration-test-kit']);
        Livewire::test(ListAccessories::class)->assertSuccessful();

        $this->assertTrue(User::factory()->make(['role' => 'editor', 'is_active' => true])->canManage('accessories'));
        $this->assertTrue(User::factory()->make(['role' => 'sales', 'is_active' => true])->canManage('accessories'));
        $this->assertFalse(User::factory()->make(['role' => 'hr', 'is_active' => true])->canManage('accessories'));
    }

    public function test_a_general_accessory_needs_no_brand(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateAccessory::class)
            ->fillForm(['name' => 'Lint-free cloth', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('accessories', ['name' => 'Lint-free cloth', 'brand_id' => null]);
    }
}
