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

    public function test_the_home_page_names_the_site_and_the_company_for_google(): void
    {
        $html = $this->get('/')->getContent();
        preg_match('~<script type="application/ld\+json">(.*?)</script>~s', $html, $m);
        $nodes = json_decode($m[1], true);

        $this->assertIsArray($nodes);
        $types = array_map(fn ($n) => $n['@type'], $nodes);
        $this->assertContains('WebSite', $types);
        $this->assertContains(['Organization', 'LocalBusiness'], $types);

        $site = collect($nodes)->firstWhere('@type', 'WebSite');
        $this->assertSame('Agarwal Brothers', $site['name']);
        $this->assertContains('Agarwal Brothers Jaipur', $site['alternateName']);
        $this->assertSame(\App\Support\Seo::absolute('/') . '#organization', $site['publisher']['@id']);
    }

    public function test_long_article_titles_are_cut_at_a_whole_word_within_sixty_characters(): void
    {
        $long = 'How to choose an analytical or semi-micro balance: readability, capacity and calibration';
        $title = \App\Support\Seo::title($long);

        $this->assertLessThanOrEqual(60, mb_strlen($title));
        $this->assertStringNotContainsString('…', $title);
        $this->assertTrue(str_starts_with($long, $title), 'a clean prefix of the headline');
        $this->assertContains(mb_substr($long, mb_strlen($title), 1), [' ', ':', ','], 'cut between words');

        // short titles keep the site name, exactly as before
        $this->assertSame('Contact Us | Agarwal Brothers', \App\Support\Seo::title('Contact Us'));
        $this->assertSame('Home | Agarwal Brothers', \App\Support\Seo::title('Home'));
        // a title that already names the site is left alone
        $this->assertSame('About Agarwal Brothers: Jaipur', \App\Support\Seo::title('About Agarwal Brothers: Jaipur'));
        // never ends on a linking word
        $this->assertStringEndsNotWith(' and', \App\Support\Seo::title('Laser diffraction particle size analysis: how it works and what the numbers mean for you'));
    }

    public function test_a_brand_without_a_description_still_gets_a_full_meta_description(): void
    {
        $brand = \App\Models\Brand::create(['name' => 'Zeal', 'slug' => 'zeal', 'is_active' => true]);

        $html = $this->get('/brands/zeal')->getContent();
        preg_match('~<meta name="description" content="([^"]*)"~', $html, $m);

        $this->assertGreaterThanOrEqual(130, mb_strlen(html_entity_decode($m[1])));
        $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($m[1])));

        // a very short description is topped up too
        $brand->update(['description' => 'Indian maker of lab ovens.']);
        preg_match('~<meta name="description" content="([^"]*)"~', $this->get('/brands/zeal')->getContent(), $m);
        $this->assertGreaterThanOrEqual(130, mb_strlen(html_entity_decode($m[1])));
        $this->assertStringContainsString('Indian maker of lab ovens', html_entity_decode($m[1]));
    }

    public function test_social_icons_show_only_for_real_links_and_feed_the_company_data(): void
    {
        config(['contact.social' => ['linkedin' => null, 'youtube' => '#', 'facebook' => 'javascript:alert(1)', 'instagram' => 'http://insecure.example']]);
        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString('aria-label="LinkedIn"', $html);
        $this->assertStringNotContainsString('aria-label="YouTube"', $html);
        $this->assertStringContainsString('Chat with us', $html);
        $this->assertStringNotContainsString('sameAs', $html);

        config(['contact.social' => ['linkedin' => 'https://www.linkedin.com/company/agarwal-brothers', 'youtube' => '']]);
        $html = $this->get('/')->getContent();
        $this->assertStringContainsString('aria-label="LinkedIn"', $html);
        $this->assertStringContainsString('href="https://www.linkedin.com/company/agarwal-brothers"', $html);
        $this->assertStringNotContainsString('aria-label="YouTube"', $html);
        $this->assertStringContainsString('Follow us', $html);
        $this->assertStringContainsString('"sameAs":["https://www.linkedin.com/company/agarwal-brothers"]', $html);
    }

    public function test_static_pages_have_keyword_titles(): void
    {
        $this->assertStringContainsString('<title>About Agarwal Brothers: Jaipur Lab Equipment Supplier</title>', $this->get('/our-story')->getContent());
        $this->assertStringContainsString('<title>Contact Us: Lab Equipment Quote in Jaipur | Agarwal Brothers</title>', $this->get('/contact-us')->getContent());
        $this->assertStringContainsString('<title>Lab Equipment &amp; Instruments by Category | Agarwal Brothers</title>', $this->get('/verticals')->getContent());
    }
}
