<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vertical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Titles and descriptions use the words buyers search for (product + brand + Jaipur / India). */
class SeoTitlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_brand_vertical_category_and_product_titles_carry_search_words(): void
    {
        $vertical = Vertical::create(['name' => 'Lab Weighing Solutions', 'slug' => 'lab-weighing', 'is_active' => true]);
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Balances', 'slug' => 'analytical']);
        $cat->verticals()->attach($vertical->id);
        $product = Product::create([
            'category_id' => $cat->id, 'name' => 'BSA224S-CW', 'slug' => 'bsa224s-cw', 'is_active' => true,
            'heading' => 'BSA224S Standard Balance',
        ]);

        $this->assertStringContainsString('<title>Sartorius Supplier in Jaipur, India | Agarwal Brothers</title>', $this->get('/brands/sartorius')->getContent());
        $this->assertStringContainsString('<title>Lab Weighing Solutions Supplier, Jaipur | Agarwal Brothers</title>', $this->get('/verticals/lab-weighing')->getContent());
        $this->assertStringContainsString('<title>Analytical Balances by Sartorius | Agarwal Brothers</title>', $this->get('/brands/sartorius/analytical')->getContent());
        $this->assertStringContainsString('<title>BSA224S Standard Balance by Sartorius | Agarwal Brothers</title>', $this->get('/products/' . $product->slug)->getContent());
    }

    public function test_an_admin_written_seo_title_still_wins(): void
    {
        Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true, 'seo_title' => 'My own title']);

        $this->assertStringContainsString('<title>My own title | Agarwal Brothers</title>', $this->get('/brands/sartorius')->getContent());
    }

    public function test_a_vertical_description_is_a_clean_sentence_not_a_cut_paragraph(): void
    {
        Vertical::create([
            'name' => 'Lab Weighing Solutions', 'slug' => 'lab-weighing', 'is_active' => true,
            'description' => str_repeat('Laboratory balances are precision instruments used in research. ', 6),
        ]);

        $html = $this->get('/verticals/lab-weighing')->getContent();

        $this->assertStringContainsString('<meta name="description" content="Lab Weighing Solutions: models, specifications and quotes', $html);
        $this->assertStringNotContainsString('…"', substr($html, strpos($html, 'name="description"'), 300));
    }

    public function test_the_home_page_offers_our_own_brand_image_not_a_client_logo(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('<meta property="og:image" content="' . \App\Support\Seo::absolute('images/share-card.png') . '">', $html);
        $this->assertStringContainsString('"image":["' . \App\Support\Seo::absolute('images/share-square.png') . '","' . \App\Support\Seo::absolute('images/share-card.png') . '"]', $html);
        $this->assertStringContainsString('"logo":"' . \App\Support\Seo::absolute('images/share-square.png') . '"', $html);

        [$w, $h] = getimagesize(public_path('images/share-card.png'));
        $this->assertSame([1200, 630], [$w, $h]);
        [$w, $h] = getimagesize(public_path('images/share-square.png'));
        $this->assertSame([1200, 1200], [$w, $h]);
    }

    public function test_static_pages_have_keyword_titles(): void
    {
        $this->assertStringContainsString('<title>About Agarwal Brothers: Jaipur Lab Equipment Supplier</title>', $this->get('/our-story')->getContent());
        $this->assertStringContainsString('<title>Contact Us: Lab Equipment Quote in Jaipur | Agarwal Brothers</title>', $this->get('/contact-us')->getContent());
        $this->assertStringContainsString('<title>Lab Equipment &amp; Instruments by Category | Agarwal Brothers</title>', $this->get('/verticals')->getContent());
    }
}
