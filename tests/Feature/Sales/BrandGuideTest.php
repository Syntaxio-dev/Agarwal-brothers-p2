<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesBrands\Pages\CreateSalesBrand;
use App\Sales\Filament\Resources\SalesBrands\Pages\EditSalesBrand;
use App\Sales\Models\SalesBrand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The brand guide: only a manager writes it, the phones read it with the product guide, and every change makes the phones fetch again.
 */
class BrandGuideTest extends SalesTestCase
{
    private function brand(array $over = []): SalesBrand
    {
        return SalesBrand::create(array_merge([
            'name' => 'Sartorius',
            'origin' => 'Germany · Sartorius AG',
            'about' => 'German maker of balances.',
            'known_for' => ['Precision weighing'],
            'pitch' => ['Audit-ready'],
            'objections' => [['if' => 'Too costly', 'then' => 'Compare the cost over ten years.']],
            'competitors' => [['name' => 'Mettler Toledo', 'note' => 'Premium rival.']],
        ], $over));
    }

    public function test_a_manager_adds_and_edits_a_brand_in_the_admin(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateSalesBrand::class)
            ->fillForm(['name' => 'Ika', 'origin' => 'Germany · IKA-Werke', 'channel' => 'Bishop', 'about' => 'Stirrers.', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $brand = SalesBrand::where('name', 'Ika')->firstOrFail();
        $this->assertTrue(Str::isUuid($brand->uuid));
        $this->assertSame('Bishop', $brand->channel);

        Livewire::test(EditSalesBrand::class, ['record' => $brand->getKey()])
            ->fillForm(['about' => 'Stirrers and mixers.'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('Stirrers and mixers.', $brand->fresh()->about);

        // the name is unique
        Livewire::test(CreateSalesBrand::class)->fillForm(['name' => 'Ika'])->call('create')->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_a_rep_can_not_open_the_brand_guide_in_the_admin(): void
    {
        $this->actingAs($this->rep()->user);

        $this->get('/sales/sales-brands')->assertForbidden();
    }

    public function test_the_phone_gets_the_brands_with_the_guide_and_a_change_makes_it_fetch_again(): void
    {
        $this->brand();
        $this->brand(['name' => 'Hidden', 'is_active' => false]);
        $headers = $this->headers($this->rep());

        $first = $this->getJson('/api/sales/v1/products', $headers)->assertOk()->assertJsonPath('unchanged', false);
        $this->assertCount(1, $first->json('brands'), 'a brand that is switched off is not sent');
        $this->assertSame('Sartorius', $first->json('brands.0.name'));
        $this->assertSame([['if' => 'Too costly', 'then' => 'Compare the cost over ten years.']], $first->json('brands.0.objections'));
        $this->assertSame([], $first->json('brands.0.known_lines'), 'an empty list is sent as a list');
        $version = $first->json('version');

        $this->getJson('/api/sales/v1/products?version=' . $version, $headers)->assertJsonPath('unchanged', true);

        $this->travel(2)->seconds();
        SalesBrand::where('name', 'Sartorius')->first()->update(['about' => 'Changed.']);
        $this->getJson('/api/sales/v1/products?version=' . $version, $headers)->assertJsonPath('unchanged', false)->assertJsonPath('brands.0.about', 'Changed.');
    }

    public function test_the_import_command_loads_the_brands_once_and_keeps_a_managers_edit(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'brands');
        file_put_contents($file, json_encode([
            ['name' => 'Retsch', 'origin' => 'Germany', 'known_for' => ['Mills'], 'competitors' => [['name' => 'Fritsch', 'note' => 'Rival']]],
        ]));

        $this->assertSame(0, Artisan::call('sales:import-brands', ['file' => $file, '--dry-run' => true]));
        $this->assertSame(0, SalesBrand::count());

        $this->assertSame(0, Artisan::call('sales:import-brands', ['file' => $file]));
        $this->assertSame(['Mills'], SalesBrand::where('name', 'Retsch')->first()->known_for);

        SalesBrand::where('name', 'Retsch')->first()->update(['known_for' => ['Mills and sieves']]);
        Artisan::call('sales:import-brands', ['file' => $file]);
        $this->assertSame(['Mills and sieves'], SalesBrand::where('name', 'Retsch')->first()->known_for, 'a second run leaves the manager\'s edit');

        Artisan::call('sales:import-brands', ['file' => $file, '--update' => true]);
        $this->assertSame(['Mills'], SalesBrand::where('name', 'Retsch')->first()->known_for);

        file_put_contents($file, json_encode([['name' => 'X', 'pitch' => array_fill(0, 9, 'too many')]]));
        $this->assertSame(1, Artisan::call('sales:import-brands', ['file' => $file]), 'a bad file imports nothing');
        unlink($file);
    }

    public function test_the_shipped_brand_file_is_valid(): void
    {
        $this->assertSame(0, Artisan::call('sales:import-brands', ['--dry-run' => true]));
        $this->assertStringContainsString('44 added', Artisan::output());
    }
}
