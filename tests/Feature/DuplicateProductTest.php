<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Support\ProductDuplicator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DuplicateProductTest extends TestCase
{
    use RefreshDatabase;

    private function original(): Product
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances']);

        return Product::create([
            'category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true, 'is_top_pick' => true,
            'heading' => 'Entris II, Sartorius balance in India',
            'model_group' => 'Standard Models', 'short_description' => 'Precise.', 'overview' => 'Long text',
            'specs' => collect(range(1, 9))->mapWithKeys(fn ($i) => ["Spec {$i}" => "Value {$i}"])->all(),
            'features' => [['title' => 'Fast', 'text' => 'Quick']],
            'advantages' => [['title' => 'Cheap', 'text' => 'Low cost']],
            'faqs' => [['question' => 'Why?', 'answer' => 'Because.']],
            'documents' => [['title' => 'Brochure', 'url' => 'https://example.com/b.pdf']],
            'video_url' => 'https://youtu.be/abcdefghijk',
            'seo_title' => 'Custom SEO title',
            'image' => 'products/original.png',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_copy_keeps_the_text_details_but_starts_as_a_draft_without_original_only_fields(): void
    {
        $src = $this->original();
        $brand = Brand::create(['name' => 'Acculab', 'slug' => 'acculab', 'is_active' => true]);
        $other = Category::create(['brand_id' => $brand->id, 'name' => 'Lab Balances', 'slug' => 'lab-balances']);

        $copy = ProductDuplicator::copy($src, 'Acculab ATL-224', $other->id);

        $this->assertSame($other->id, $copy->category_id);
        $this->assertSame('acculab-atl-224', $copy->slug);
        $this->assertSame($src->specs, $copy->specs);
        $this->assertSame($src->features, $copy->features);
        $this->assertSame($src->advantages, $copy->advantages);
        $this->assertSame($src->faqs, $copy->faqs);
        $this->assertSame('Standard Models', $copy->model_group);
        $this->assertSame('Precise.', $copy->short_description);

        $this->assertFalse($copy->is_active);
        $this->assertFalse($copy->is_top_pick);
        $this->assertNull($copy->heading);
        $this->assertNull($copy->documents);
        $this->assertNull($copy->video_url);
        $this->assertNull($copy->seo_title);
        $this->assertNull($copy->image);
    }

    public function test_slug_stays_unique(): void
    {
        $src = $this->original();

        $a = ProductDuplicator::copy($src, 'Entris II', $src->category_id);
        $b = ProductDuplicator::copy($src, 'Entris II', $src->category_id);

        $this->assertSame(['entris-ii-2', 'entris-ii-3'], [$a->slug, $b->slug]);
    }

    public function test_pictures_are_copied_only_when_asked_and_as_separate_files(): void
    {
        Storage::fake('public');
        $src = $this->original();
        Storage::disk('public')->put('products/original.png', 'png-bytes');
        Storage::disk('public')->put('products/g1.png', 'g1');
        $src->update(['gallery' => ['products/g1.png']]);

        $withImages = ProductDuplicator::copy($src->fresh(), 'With pictures', $src->category_id, true);

        $this->assertNotSame('products/original.png', $withImages->image);
        Storage::disk('public')->assertExists($withImages->image);
        Storage::disk('public')->assertExists($withImages->gallery[0]);
        $this->assertNotSame('products/g1.png', $withImages->gallery[0]);
        Storage::disk('public')->assertExists('products/original.png');

        $without = ProductDuplicator::copy($src->fresh(), 'Without pictures', $src->category_id);
        $this->assertNull($without->image);
    }

    public function test_admin_can_duplicate_from_the_list_and_the_copy_has_its_own_number_of_specs(): void
    {
        $src = $this->original();
        $this->actingAs($this->admin());

        Livewire::test(ListProducts::class)
            ->callTableAction('duplicate', $src, data: ['name' => 'Acculab ATL-224', 'category_id' => $src->category_id, 'copy_images' => false])
            ->assertHasNoTableActionErrors();

        $copy = Product::where('name', 'Acculab ATL-224')->firstOrFail();
        $this->assertCount(9, $copy->specs);

        // On the copy's edit page the specifications can be changed freely: 9 become 12, then 6.
        $twelve = collect(range(1, 12))->mapWithKeys(fn ($i) => ["Spec {$i}" => "V{$i}"])->all();
        Livewire::test(EditProduct::class, ['record' => $copy->getRouteKey()])
            ->fillForm(['specs' => $twelve])->call('save')->assertHasNoFormErrors();
        $this->assertCount(12, $copy->fresh()->specs);

        $six = collect(range(1, 6))->mapWithKeys(fn ($i) => ["Spec {$i}" => "V{$i}"])->all();
        Livewire::test(EditProduct::class, ['record' => $copy->getRouteKey()])
            ->fillForm(['specs' => $six])->call('save')->assertHasNoFormErrors();
        $this->assertCount(6, $copy->fresh()->specs);

        $this->assertCount(9, $src->fresh()->specs);   // the original never changes
    }

    public function test_duplicate_button_is_available_on_the_edit_page(): void
    {
        $src = $this->original();
        $this->actingAs($this->admin());

        Livewire::test(EditProduct::class, ['record' => $src->getRouteKey()])
            ->assertActionExists('duplicate')
            ->callAction('duplicate', data: ['name' => 'From edit page', 'category_id' => $src->category_id, 'copy_images' => false]);

        $this->assertDatabaseHas('products', ['name' => 'From edit page', 'is_active' => false]);
    }

    public function test_users_who_cannot_create_products_cannot_duplicate(): void
    {
        $this->original();
        $this->actingAs(User::factory()->create(['role' => 'hr', 'is_active' => true]));

        $this->assertFalse(ProductResource::canCreate());
    }

    public function test_help_guide_explains_duplicating(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/help')->assertOk()
            ->assertSee('Adding a similar product fast')
            ->assertSee('Add specification');
    }
}
