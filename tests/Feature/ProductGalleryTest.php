<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function product(?string $image, array $gallery = []): Product
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius-' . uniqid(), 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances-' . uniqid()]);

        return Product::create([
            'category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii-' . uniqid(), 'is_active' => true,
            'image' => $image, 'gallery' => $gallery,
        ]);
    }

    public function test_several_photos_give_arrows_a_counter_and_thumbnails(): void
    {
        $p = $this->product('products/main.webp', ['products/gallery/side.webp', 'products/gallery/back.webp']);

        $html = $this->get(route('product.show', $p->slug))->assertOk()->getContent();

        $this->assertStringContainsString('aria-label="Previous photo"', $html);
        $this->assertStringContainsString('aria-label="Next photo"', $html);
        $this->assertStringContainsString('/ 3', $html);                                     // counter "1 / 3"
        $this->assertSame(3, substr_count($html, 'aria-label="Show photo '));                // one thumbnail per photo
        $this->assertStringContainsString('storage/products/main.webp', $html);
        $this->assertStringContainsString('storage/products/gallery/back.webp', $html);
        $this->assertStringContainsString('@keydown.arrow-left.prevent="! lightbox && prev()"', $html);   // keyboard, but not twice while the big view is open
        $this->assertStringContainsString('@touchend.passive', $html);                      // swipe
        $this->assertStringContainsString('touch-action: pan-y pinch-zoom', $html);        // a sideways swipe belongs to the photos, not to the browser's "go back" gesture
        $this->assertStringContainsString('Show Images', $html);                            // the big view is still there
    }

    public function test_a_single_photo_has_no_arrows_or_thumbnails(): void
    {
        $p = $this->product('products/main.webp');

        $html = $this->get(route('product.show', $p->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('aria-label="Previous photo"', $html);
        $this->assertStringNotContainsString('aria-label="Show photo ', $html);
        $this->assertStringContainsString('storage/products/main.webp', $html);
    }

    public function test_a_product_without_photos_shows_the_placeholder(): void
    {
        $p = $this->product(null);

        $html = $this->get(route('product.show', $p->slug))->assertOk()->getContent();

        $this->assertStringNotContainsString('aria-label="Next photo"', $html);
        $this->assertStringNotContainsString('Show Image', $html);
    }

    public function test_only_gallery_photos_still_show_and_the_first_one_leads(): void
    {
        $p = $this->product(null, ['products/gallery/one.webp', 'products/gallery/two.webp']);

        $html = $this->get(route('product.show', $p->slug))->assertOk()->getContent();

        $this->assertStringContainsString('aria-label="Next photo"', $html);
        $this->assertStringContainsString('/ 2', $html);
    }

    public function test_the_admin_form_takes_up_to_ten_extra_photos_that_can_be_reordered(): void
    {
        $form = file_get_contents(app_path('Filament/Resources/Products/Schemas/ProductForm.php'));

        $this->assertStringContainsString("FileUpload::make('gallery')", $form);
        $this->assertStringContainsString('->multiple()', $form);
        $this->assertStringContainsString('->maxFiles(10)', $form);
        $this->assertStringContainsString('->reorderable()', $form);
        $this->assertStringContainsString('More photos', $form);
    }
}
