<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlideProductTest extends TestCase
{
    use RefreshDatabase;

    private function product(bool $active = true, bool $brandActive = true): Product
    {
        $brand = Brand::create(['name' => 'Aczet', 'slug' => 'aczet-' . uniqid(), 'is_active' => $brandActive]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances-' . uniqid()]);

        return Product::create([
            'category_id' => $cat->id, 'name' => 'Analytical Balance CY 224', 'slug' => 'analytical-balance-' . uniqid(),
            'is_active' => $active,
        ]);
    }

    /** Just the hero carousel part of the home page (so nav/footer links do not interfere). */
    private function hero(): string
    {
        $html = $this->get('/')->assertOk()->getContent();
        $start = strpos($html, 'active: 0');

        return substr($html, $start, 8000);
    }

    public function test_a_slide_linked_to_a_product_makes_the_whole_slide_link_to_that_product(): void
    {
        $product = $this->product();
        Slide::create(['title' => 'Top pick', 'image' => 'slides/x.jpg', 'alt_text' => 'x', 'product_id' => $product->id, 'is_active' => true]);

        $hero = $this->hero();
        $url = route('product.show', $product->slug);

        // the slide element itself is an <a> to the product, and the button says "View product"
        $this->assertMatchesRegularExpression('/<a[^>]*href="' . preg_quote($url, '/') . '"[^>]*>/', $hero);
        $this->assertStringContainsString('View product', $hero);
        $this->assertStringContainsString('aria-label="View Analytical Balance CY 224"', $hero);
    }

    public function test_a_slide_with_only_a_product_and_no_title_shows_the_product_name(): void
    {
        $product = $this->product();
        Slide::create(['image' => 'slides/x.jpg', 'alt_text' => 'x', 'product_id' => $product->id, 'is_active' => true]);

        $hero = $this->hero();

        $this->assertStringContainsString('Featured product', $hero);
        $this->assertStringContainsString('Analytical Balance CY 224', $hero);
        $this->assertStringContainsString(route('product.show', $product->slug), $hero);
    }

    public function test_the_product_wins_over_the_button_link(): void
    {
        $product = $this->product();
        Slide::create(['title' => 'Hi', 'product_id' => $product->id, 'link_url' => '/verticals', 'is_active' => true]);

        $hero = $this->hero();

        $this->assertStringContainsString(route('product.show', $product->slug), $hero);
        $this->assertStringNotContainsString('href="/verticals"', $hero);     // the slide links to the product, not the button link
    }

    public function test_a_hidden_product_falls_back_to_the_button_link(): void
    {
        $product = $this->product(active: false);
        Slide::create(['title' => 'Hi', 'product_id' => $product->id, 'link_url' => '/verticals', 'is_active' => true]);

        $hero = $this->hero();

        $this->assertStringNotContainsString(route('product.show', $product->slug), $hero);
        $this->assertStringContainsString('href="/verticals"', $hero);
        $this->assertStringContainsString('Explore', $hero);
    }

    public function test_a_slide_with_no_product_and_no_link_is_a_plain_div(): void
    {
        Slide::create(['title' => 'Just text', 'image' => 'slides/x.jpg', 'alt_text' => 'x', 'is_active' => true]);

        $hero = $this->hero();

        $this->assertStringContainsString('Just text', $hero);
        $this->assertStringNotContainsString('View product', $hero);
        $this->assertStringNotContainsString('btn-glass', $hero);     // no slide button at all
    }

    public function test_deleting_a_featured_product_just_unlinks_the_slide(): void
    {
        $product = $this->product();
        $slide = Slide::create(['title' => 'Hi', 'product_id' => $product->id, 'is_active' => true]);

        $product->delete();

        $this->assertNull($slide->fresh()->product_id);
        $this->assertDatabaseHas('slides', ['id' => $slide->id]);
    }
}
