<?php

namespace Tests\Feature;

use App\Filament\Pages\ImportProducts;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductImporter;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductAdminFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return tap(User::factory()->create(['role' => 'admin', 'is_active' => true]), fn ($u) => $this->actingAs($u));
    }

    private function catalogue(): Category
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);

        return Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Balances', 'slug' => 'analytical-balances']);
    }

    private function csv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'imp') . '.csv';
        file_put_contents($path, $content);

        return $path;
    }

    // ------------------------------------------------------------------ the form

    public function test_a_product_can_be_saved_with_only_the_category_and_the_name(): void
    {
        $cat = $this->catalogue();
        $this->admin();

        Livewire::test(CreateProduct::class)
            ->fillForm(['category_id' => $cat->id, 'name' => 'Entris II Essential'])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Product saved');

        $p = Product::where('name', 'Entris II Essential')->firstOrFail();
        $this->assertSame('entris-ii-essential', $p->slug);       // made from the name
        $this->assertTrue($p->is_active);                          // the Active switch default is unchanged
    }

    public function test_save_as_hidden_draft_keeps_the_product_off_the_website(): void
    {
        $cat = $this->catalogue();
        $this->admin();

        Livewire::test(CreateProduct::class)
            ->fillForm(['category_id' => $cat->id, 'name' => 'Draft Balance', 'is_active' => true])
            ->call('createAsDraft')
            ->assertHasNoFormErrors();

        $this->assertFalse(Product::where('name', 'Draft Balance')->firstOrFail()->is_active);
        $this->get(route('product.show', 'draft-balance'))->assertNotFound();
    }

    public function test_the_required_fields_are_still_required(): void
    {
        $this->catalogue();
        $this->admin();

        Livewire::test(CreateProduct::class)
            ->fillForm(['name' => ''])
            ->call('create')
            ->assertHasFormErrors(['category_id' => 'required', 'name' => 'required']);
        Livewire::test(CreateProduct::class)->call('createAsDraft')->assertHasFormErrors(['category_id', 'name']);

        $this->assertSame(0, Product::count());
    }

    public function test_the_next_step_notification_changes_with_the_product_state(): void
    {
        $cat = $this->catalogue();
        $this->admin();

        $page = Livewire::test(CreateProduct::class)->fillForm(['category_id' => $cat->id, 'name' => 'Hidden One'])->call('createAsDraft');
        $page->assertNotified('Product saved');

        // the notification the page builds for this (hidden) product
        $notification = (fn () => $this->getCreatedNotification())->call($page->instance());
        $this->assertStringContainsString('hidden from the website', $notification->getBody());
        $actions = $notification->getActions();
        $this->assertSame(['Add photos', 'Add specs', 'Make it live'], array_map(fn ($a) => $a->getLabel(), $actions));
        $this->assertStringContainsString('#images', $actions[0]->getUrl());
        $this->assertStringContainsString('#specs', $actions[1]->getUrl());
        $this->assertStringContainsString('#visibility', $actions[2]->getUrl());

        // and for a live product with a photo
        $live = Product::create(['category_id' => $cat->id, 'name' => 'Live One', 'slug' => 'live-one', 'is_active' => true, 'image' => 'products/x.webp']);
        $page->instance()->record = $live;
        $liveNotification = (fn () => $this->getCreatedNotification())->call($page->instance());
        $this->assertStringContainsString('live on the website', $liveNotification->getBody());
        $this->assertSame(['More photos', 'Add specs', 'Visibility'], array_map(fn ($a) => $a->getLabel(), $liveNotification->getActions()));
    }

    public function test_the_form_starts_with_the_essentials_and_explains_what_is_needed(): void
    {
        $this->catalogue();
        $this->admin();

        Livewire::test(CreateProduct::class)
            ->assertSee('Start here')
            ->assertSee('You only need the Category and the Name to save a product')
            ->assertSee('Save as hidden draft')
            ->assertSee('Good: plain white background')
            ->assertSee('Avoid: busy or dark background')
            ->assertSee('Not in the list? Use the + button');
    }

    public function test_an_edited_product_shows_the_same_essentials_without_the_new_product_hint(): void
    {
        $cat = $this->catalogue();
        $this->admin();
        $p = Product::create(['category_id' => $cat->id, 'name' => 'Existing', 'slug' => 'existing', 'is_active' => true]);

        Livewire::test(EditProduct::class, ['record' => $p->getRouteKey()])
            ->assertSee('Start here')
            ->assertSee('The essentials of this product.')
            ->assertDontSee('You only need the Category and the Name')
            ->assertSee('Good: plain white background');
    }

    public function test_a_missing_category_can_be_added_from_the_product_form_with_its_vertical(): void
    {
        $brand = Brand::create(['name' => 'Anton Paar', 'slug' => 'anton-paar', 'is_active' => true]);
        $vertical = \App\Models\Vertical::create(['name' => 'Density Meters', 'slug' => 'density-meters', 'is_active' => true]);
        $this->admin();
        $open = fn () => TestAction::make('createOption')->schemaComponent('category_id', schema: 'form');

        // the dialog asks for the vertical, and it is required once verticals exist
        Livewire::test(CreateProduct::class)
            ->callAction($open(), ['brand_id' => $brand->id, 'name' => 'Density Meters'])
            ->assertHasActionErrors(['verticals' => 'required']);
        $this->assertSame(0, Category::count());

        $page = Livewire::test(CreateProduct::class)
            ->callAction($open(), ['brand_id' => $brand->id, 'name' => 'Density Meters', 'verticals' => [$vertical->id]]);

        $page->assertHasNoFormErrors();
        $cat = Category::where('name', 'Density Meters')->firstOrFail();
        $this->assertSame($brand->id, $cat->brand_id);
        $this->assertSame('density-meters', $cat->slug);
        $this->assertSame([$vertical->id], $cat->verticals()->pluck('verticals.id')->all());   // linked, so the product shows under that vertical
        $this->assertEquals($cat->id, $page->get('data.category_id'));                         // and it is already chosen in the form
    }

    public function test_the_vertical_is_not_forced_when_no_vertical_exists_yet(): void
    {
        $brand = Brand::create(['name' => 'Anton Paar', 'slug' => 'anton-paar', 'is_active' => true]);
        $this->admin();

        Livewire::test(CreateProduct::class)
            ->callAction(TestAction::make('createOption')->schemaComponent('category_id', schema: 'form'), ['brand_id' => $brand->id, 'name' => 'First Line'])
            ->assertHasNoActionErrors();

        $this->assertSame(0, Category::where('name', 'First Line')->firstOrFail()->verticals()->count());
    }

    // ------------------------------------------------------------------ the CSV check before importing

    public function test_a_dry_run_says_what_would_happen_and_saves_nothing(): void
    {
        $cat = $this->catalogue();
        Product::create(['category_id' => $cat->id, 'name' => 'Old One', 'slug' => 'old-one', 'is_active' => true]);
        $file = $this->csv("name,brand,category\nNew A,Sartorius,Analytical Balances\nNew B,Sartorius,Analytical Balances\nOld One,Sartorius,Analytical Balances\nNew C,Sartorius,Brand New Line\n");

        $r = ProductImporter::import($file, 'csv', createCategories: true, dryRun: true);

        $this->assertSame([], $r['errors']);
        $this->assertSame(['created' => 3, 'updated' => 0, 'skipped' => 1, 'total' => 4, 'newCategories' => 1], array_intersect_key($r, array_flip(['created', 'updated', 'skipped', 'total', 'newCategories'])));
        $this->assertSame(['create', 'create', 'skip', 'create'], array_column($r['preview'], 'action'));
        $this->assertTrue($r['preview'][3]['newCategory']);
        $this->assertSame(1, Product::count());                         // nothing was saved
        $this->assertSame(1, Category::count());

        $update = ProductImporter::import($file, 'csv', updateExisting: true, createCategories: true, dryRun: true);
        $this->assertSame(1, $update['updated']);
    }

    public function test_a_dry_run_with_problems_reports_them_like_a_real_import(): void
    {
        $this->catalogue();
        $r = ProductImporter::import($this->csv("name,brand,category\nOops,Unknown,X\n"), 'csv', dryRun: true);

        $this->assertNotEmpty($r['errors']);
        $this->assertArrayNotHasKey('preview', $r);
    }

    public function test_the_preview_is_limited_to_five_rows(): void
    {
        $this->catalogue();
        $rows = collect(range(1, 12))->map(fn ($i) => "Item {$i},Sartorius,Analytical Balances")->implode("\n");

        $r = ProductImporter::import($this->csv("name,brand,category\n{$rows}\n"), 'csv', dryRun: true);

        $this->assertCount(5, $r['preview']);
        $this->assertSame(12, $r['total']);
    }

    public function test_the_page_checks_first_and_imports_only_after_confirming(): void
    {
        $this->catalogue();
        Storage::fake('local');
        $this->admin();
        $file = UploadedFile::fake()->createWithContent('products.csv', "name,brand,category\nFrom Preview,Sartorius,Analytical Balances\n");

        $page = Livewire::test(ImportProducts::class)
            ->fillForm(['file' => $file])
            ->call('check')
            ->assertSee('File checked: nothing is saved yet')
            ->assertSee('From Preview')
            ->assertSee('Will be created')
            ->assertSee('Confirm and import');

        $this->assertDatabaseMissing('products', ['name' => 'From Preview']);
        $this->assertCount(1, Storage::disk('local')->allFiles('imports'));   // kept only until you decide

        $page->call('import')->assertSee('Import finished')->assertDontSee('File checked: nothing is saved yet');

        $this->assertDatabaseHas('products', ['name' => 'From Preview', 'is_active' => false]);
        $this->assertSame([], Storage::disk('local')->allFiles('imports'));
    }

    public function test_cancelling_the_preview_removes_the_file_and_saves_nothing(): void
    {
        $this->catalogue();
        Storage::fake('local');
        $this->admin();

        Livewire::test(ImportProducts::class)
            ->fillForm(['file' => UploadedFile::fake()->createWithContent('p.csv', "name,brand,category\nNever Saved,Sartorius,Analytical Balances\n")])
            ->call('check')
            ->call('cancelPreview')
            ->assertDontSee('File checked: nothing is saved yet');

        $this->assertDatabaseMissing('products', ['name' => 'Never Saved']);
        $this->assertSame([], Storage::disk('local')->allFiles('imports'));
    }

    public function test_a_file_with_problems_shows_the_errors_at_the_check_step_and_is_not_kept(): void
    {
        $this->catalogue();
        Storage::fake('local');
        $this->admin();

        Livewire::test(ImportProducts::class)
            ->fillForm(['file' => UploadedFile::fake()->createWithContent('bad.csv', "name,brand,category\nOops,Unknown Brand,X\n")])
            ->call('check')
            ->assertSee('Nothing was imported')
            ->assertSee('Unknown Brand')
            ->assertDontSee('Confirm and import');

        $this->assertSame([], Storage::disk('local')->allFiles('imports'));
    }

    public function test_changing_the_file_or_a_switch_clears_an_old_preview(): void
    {
        $this->catalogue();
        Storage::fake('local');
        $this->admin();

        Livewire::test(ImportProducts::class)
            ->fillForm(['file' => UploadedFile::fake()->createWithContent('p.csv', "name,brand,category\nX,Sartorius,Analytical Balances\n")])
            ->call('check')
            ->assertSee('File checked: nothing is saved yet')
            ->set('data.update_existing', true)
            ->assertDontSee('File checked: nothing is saved yet');
    }
}
