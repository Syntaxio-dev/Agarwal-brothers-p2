<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Filament\Resources\SalesProductRequests\Pages\ListSalesProductRequests;
use App\Sales\Filament\Resources\SalesProducts\Pages\CreateSalesProduct;
use App\Sales\Filament\Resources\SalesProducts\Pages\EditSalesProduct;
use App\Sales\Filament\Resources\SalesProducts\Pages\ListSalesProducts;
use App\Sales\Models\SalesProduct;
use App\Sales\Models\SalesProductRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The product guide: only a manager writes it, every rep reads it on the phone, and a rep can ask for a note to be added.
 */
class ProductGuideTest extends SalesTestCase
{
    private function product(array $over = []): SalesProduct
    {
        return SalesProduct::create(array_merge([
            'name' => 'Lab Weighing Balance',
            'group' => 'Balances and weighing',
            'sort' => 34,
            'premium_brands' => ['Sartorius'],
            'economy_brands' => ['Aczet'],
            'lite_brands' => [],
            'summary' => 'A balance weighs samples.',
            'variants' => [['name' => 'Analytical', 'text' => '0.1 mg readability']],
            'key_parameters' => ['What capacity is needed?'],
            'short_term_problems' => ['Drafts cause drift'],
            'long_term_problems' => ['Load cell fatigue'],
            'top_brands' => [['name' => 'Sartorius', 'note' => 'Premium German make']],
        ], $over));
    }

    public function test_a_manager_adds_edits_and_removes_a_product_in_the_admin(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateSalesProduct::class)
            ->fillForm([
                'name' => 'Vortex Mixer',
                'group' => 'Mixing',
                'sort' => 24,
                'premium_brands' => ['Ika'],
                'economy_brands' => ['DLAB'],
                'summary' => 'Mixes small tubes.',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = SalesProduct::where('name', 'Vortex Mixer')->firstOrFail();
        $this->assertSame('vortex-mixer', $product->slug);
        $this->assertTrue(Str::isUuid($product->uuid));
        $this->assertSame(['Ika'], $product->premium_brands);

        Livewire::test(EditSalesProduct::class, ['record' => $product->getKey()])
            ->fillForm(['lite_brands' => ['Supertek'], 'name' => ''])
            ->call('save')
            ->assertHasFormErrors(['name' => 'required']);

        Livewire::test(EditSalesProduct::class, ['record' => $product->getKey()])
            ->fillForm(['lite_brands' => ['Supertek']])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame(['Supertek'], $product->fresh()->lite_brands);

        Livewire::test(ListSalesProducts::class)->assertCanSeeTableRecords([$product]);
        $product->delete();
        $this->assertSame(0, SalesProduct::count());
    }

    public function test_a_rep_can_not_open_or_change_the_guide_in_the_admin(): void
    {
        $this->product();
        $rep = $this->rep()->user;

        $this->actingAs($rep);
        $this->get('/admin/sales-products')->assertForbidden();
        $this->get('/admin/sales-products/create')->assertForbidden();
        $this->get('/admin/sales-product-requests')->assertForbidden();
    }

    public function test_the_phone_reads_the_guide_and_gets_nothing_new_when_it_already_has_it(): void
    {
        $this->product();
        $this->product(['name' => 'Hidden product', 'sort' => 99, 'is_active' => false]);
        $headers = $this->headers($this->rep());

        $first = $this->getJson('/api/sales/v1/products', $headers)->assertOk()->assertJsonPath('unchanged', false);
        $this->assertCount(1, $first->json('products'), 'a product that is switched off is not sent');
        $this->assertSame('Lab Weighing Balance', $first->json('products.0.name'));
        $this->assertSame(['Sartorius'], $first->json('products.0.premium_brands'));
        $this->assertSame(['name' => 'Analytical', 'text' => '0.1 mg readability'], $first->json('products.0.variants.0'));
        $this->assertArrayNotHasKey('id', $first->json('products.0'));
        $this->assertArrayNotHasKey('updated_by', $first->json('products.0'));

        $version = $first->json('version');
        $this->getJson('/api/sales/v1/products?version=' . $version, $headers)->assertOk()->assertJsonPath('unchanged', true)->assertJsonMissingPath('products');

        // an edit changes the stamp, so every phone gets the new text at its next sync
        $this->travel(2)->seconds();
        SalesProduct::first()->update(['summary' => 'A better text.']);
        $second = $this->getJson('/api/sales/v1/products?version=' . $version, $headers)->assertOk()->assertJsonPath('unchanged', false);
        $this->assertNotSame($version, $second->json('version'));
        $this->assertSame('A better text.', $second->json('products.0.summary'));
    }

    public function test_the_guide_needs_a_signed_in_rep(): void
    {
        $this->getJson('/api/sales/v1/products')->assertStatus(401);

        $off = $this->rep('REP002', '9000000002', active: false);
        $this->assertNotNull($off);
        $this->getJson('/api/sales/v1/products', ['Authorization' => 'Bearer nothing'])->assertStatus(401);
    }

    public function test_a_rep_asks_for_a_note_once_and_the_manager_answers(): void
    {
        $product = $this->product();
        $profile = $this->rep();
        $headers = $this->headers($profile);

        $ask = [
            'uuid' => (string) Str::uuid(),
            'product_uuid' => $product->uuid,
            'product_name' => $product->name,
            'body' => 'Please add Mettler Toledo under the premium range.',
            'updated_at' => $this->moment(),
        ];

        $this->assertSame('accepted', $this->answer($this->push($headers, ['product_requests' => [$ask]])->assertOk(), 'product_requests', $ask['uuid'])['status']);
        $this->assertSame('duplicate', $this->answer($this->push($headers, ['product_requests' => [$ask]])->assertOk(), 'product_requests', $ask['uuid'])['status']);
        $this->assertSame(1, SalesProductRequest::count());

        // another rep can not take over the same id
        $other = $this->headers($this->rep('REP002', '9000000002'));
        $this->assertSame('UUID_TAKEN', $this->answer($this->push($other, ['product_requests' => [$ask]])->assertOk(), 'product_requests', $ask['uuid'])['code']);

        // too short, or about nothing: refused on their own
        $bad = ['uuid' => (string) Str::uuid(), 'product_name' => 'X', 'body' => 'hi', 'updated_at' => $this->moment()];
        $this->assertSame('INVALID', $this->answer($this->push($headers, ['product_requests' => [$bad]])->assertOk(), 'product_requests', $bad['uuid'])['code']);

        $this->actingAs($this->manager());
        $request = SalesProductRequest::first();

        Livewire::test(ListSalesProductRequests::class)
            ->callTableAction('done', $request, data: ['reply' => 'Added, thank you.'])
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $this->assertSame('done', $request->status);
        $this->assertSame('Added, thank you.', $request->reply);
        $this->assertNotNull($request->handled_at);
    }

    public function test_a_rep_can_not_pile_up_requests(): void
    {
        $headers = $this->headers($this->rep());
        $asks = collect(range(1, 31))->map(fn () => ['uuid' => (string) Str::uuid(), 'product_name' => 'Pipettes', 'body' => 'Add a note please', 'updated_at' => $this->moment()]);

        foreach ($asks->chunk(10) as $chunk) {
            $this->push($headers, ['product_requests' => $chunk->values()->all()])->assertOk();
        }

        $this->assertSame(30, SalesProductRequest::count());
    }

    public function test_the_import_command_loads_the_sheet_once_and_never_overwrites_a_managers_edit(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'prod') . '.json';
        file_put_contents($file, json_encode([
            ['sort' => 1, 'name' => 'Pipettes', 'group' => 'Liquid handling', 'premium_brands' => ['Brand GmbH'], 'economy_brands' => ['DLAB'], 'lite_brands' => [],
                'summary' => 'Moves liquid.', 'variants' => [['name' => 'Air displacement', 'text' => 'Standard']], 'industries' => '', 'key_parameters' => ['What volume?'],
                'short_term_problems' => ['Leaks'], 'long_term_problems' => ['Wear'], 'top_brands' => [['name' => 'Eppendorf', 'note' => 'Durable']]],
            ['sort' => 2, 'name' => 'Vacuum Pump', 'group' => 'Pumps', 'premium_brands' => [], 'economy_brands' => [], 'lite_brands' => [], 'summary' => '', 'variants' => [],
                'industries' => '', 'key_parameters' => [], 'short_term_problems' => [], 'long_term_problems' => [], 'top_brands' => []],
        ]));

        $this->assertSame(0, Artisan::call('sales:import-products', ['file' => $file, '--dry-run' => true]));
        $this->assertSame(0, SalesProduct::count(), 'a dry run writes nothing');

        $this->assertSame(0, Artisan::call('sales:import-products', ['file' => $file]));
        $this->assertSame(2, SalesProduct::count());
        $this->assertSame(['DLAB'], SalesProduct::where('name', 'Pipettes')->first()->economy_brands);

        SalesProduct::where('name', 'Pipettes')->update(['summary' => 'Edited by the manager.']);
        Artisan::call('sales:import-products', ['file' => $file]);
        $this->assertSame(2, SalesProduct::count(), 'a second run adds nothing');
        $this->assertSame('Edited by the manager.', SalesProduct::where('name', 'Pipettes')->value('summary'));

        Artisan::call('sales:import-products', ['file' => $file, '--update' => true]);
        $this->assertSame('Moves liquid.', SalesProduct::where('name', 'Pipettes')->value('summary'));
    }

    public function test_a_bad_sheet_imports_nothing(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'prod') . '.json';
        file_put_contents($file, json_encode([
            ['sort' => 1, 'name' => 'Fine', 'variants' => [], 'top_brands' => []],
            ['sort' => 2, 'name' => '', 'variants' => [['text' => '']], 'premium_brands' => 'not a list'],
        ]));

        $this->assertSame(1, Artisan::call('sales:import-products', ['file' => $file]));
        $this->assertSame(0, SalesProduct::count());
    }

    public function test_the_managers_role_has_both_guide_screens_and_a_plain_editor_has_neither(): void
    {
        $editor = User::factory()->create(['role' => 'editor', 'is_active' => true]);
        $this->assertFalse($editor->canManage('sales-products'));
        $this->assertFalse($editor->canManage('sales-product-requests'));
        $this->assertTrue($this->manager()->canManage('sales-products'));
        $this->assertTrue($this->manager()->canManage('sales-product-requests'));
    }
}
