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

        // brand + what it makes (from its product lines) + where
        $this->assertStringContainsString('<title>Sartorius Balances Supplier, Jaipur | Agarwal Brothers</title>', $this->get('/brands/sartorius')->getContent());
        $this->assertStringContainsString('<title>Lab Weighing Solutions Supplier, Jaipur | Agarwal Brothers</title>', $this->get('/verticals/lab-weighing')->getContent());
        // brand first, the way people search a range ("sartorius analytical balances")
        $this->assertStringContainsString('<title>Sartorius Analytical Balances | Agarwal Brothers</title>', $this->get('/brands/sartorius/analytical')->getContent());
        // the heading lacks the exact model (-CW), so the title is brand + model + product type
        $this->assertStringContainsString('<title>Sartorius BSA224S-CW Analytical Balance | Agarwal Brothers</title>', $this->get('/products/' . $product->slug)->getContent());
    }

    public function test_product_title_is_brand_model_type_and_short_ranges_get_a_suffix(): void
    {
        $brand = Brand::create(['name' => 'Aczet', 'slug' => 'aczet', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Balance', 'slug' => 'analytical-balance']);
        $withHeading = Product::create(['category_id' => $cat->id, 'name' => 'CY 224C', 'slug' => 'cy-224c', 'is_active' => true,
            'heading' => 'Aczet CY 224C Analytical Balance 220 g × 0.1 mg']);
        $series = Product::create(['category_id' => $cat->id, 'name' => 'CY 224FC', 'slug' => 'cy-224fc', 'is_active' => true,
            'heading' => 'CY-FC Series Flameproof Balance (Internal Calibration)']);

        // the owner's heading already has brand and model: used as it is
        $this->assertSame('Aczet CY 224C Analytical Balance 220 g × 0.1 mg', $withHeading->seoHeading());
        // the heading does not name the model: brand + model + product type
        $this->assertSame('Aczet CY 224FC Analytical Balance', $series->seoHeading());
        $this->assertStringContainsString('<title>Aczet Analytical Balance: Models &amp; Specs | Agarwal Brothers</title>', $this->get('/brands/aczet/analytical-balance')->getContent());

        // the range description names its models
        $html = $this->get('/brands/aczet/analytical-balance')->getContent();
        preg_match('/<meta name="description" content="([^"]*)"/', $html, $m);
        $this->assertStringContainsString('Aczet Analytical Balance: 2 models, CY 224C and CY 224FC.', html_entity_decode($m[1]));
    }

    public function test_product_description_is_whole_sentences_with_brand_and_model(): void
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Lab Balances', 'slug' => 'analytical']);
        $product = Product::create(['category_id' => $cat->id, 'name' => 'Cubis II Analytical', 'slug' => 'cubis-ii-analytical',
            'heading' => 'Cubis II Analytical Balance', 'is_active' => true,
            'short_description' => 'One modular premium platform for demanding weighing. It reads to 0.1 mg.']);

        $d = $product->seoDescription();
        $this->assertStringStartsWith('Sartorius Cubis II Analytical Balance. One modular premium platform', $d);
        $this->assertLessThanOrEqual(160, mb_strlen($d));
        $this->assertStringEndsWith('.', $d);
        $this->assertSame(1, substr_count($d, 'Agarwal Brothers'), 'one call to action, not two');
    }

    public function test_product_page_tells_google_the_model_specs_and_faqs(): void
    {
        $brand = Brand::create(['name' => 'Aczet', 'slug' => 'aczet', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Balance', 'slug' => 'analytical-balance']);
        $product = Product::create(['category_id' => $cat->id, 'name' => 'CY 224C', 'slug' => 'cy-224c', 'is_active' => true,
            'heading' => 'CY 224C Analytical Balance', 'image' => 'products/a.webp',
            'specs' => ['Model' => 'CY 224C', 'Readability (mg)' => '0.1'],
            'faqs' => [['question' => 'Is it calibrated?', 'answer' => '<p>Yes, internally.</p>']]]);

        $html = $this->get('/products/' . $product->slug)->assertOk()->getContent();

        $this->assertStringContainsString('>Aczet CY 224C Analytical Balance</h1>', $html, 'the H1 carries the brand');
        $this->assertStringContainsString('alt="Aczet CY 224C Analytical Balance"', $html, 'the main photo says brand + model + type');

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);
        $nodes = collect($m[1])->map(fn ($j) => json_decode($j, true))->flatMap(fn ($n) => isset($n['@type']) ? [$n] : $n);
        $productNode = $nodes->firstWhere('@type', 'Product');
        $this->assertSame('Aczet CY 224C Analytical Balance', $productNode['name']);
        $this->assertSame('CY 224C', $productNode['mpn']);
        $this->assertContains('CY224C', $productNode['alternateName']);
        $this->assertContains(['@type' => 'PropertyValue', 'name' => 'Readability (mg)', 'value' => '0.1'], $productNode['additionalProperty']);
        $faq = $nodes->firstWhere('@type', 'FAQPage');
        $this->assertSame('Yes, internally.', $faq['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_the_sitemap_lists_product_photos(): void
    {
        $brand = Brand::create(['name' => 'Aczet', 'slug' => 'aczet', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Balance', 'slug' => 'analytical-balance']);
        Product::create(['category_id' => $cat->id, 'name' => 'CY 224C', 'slug' => 'cy-224c', 'is_active' => true,
            'image' => 'products/a.webp', 'gallery' => ['products/gallery/b.webp']]);
        \Illuminate\Support\Facades\Cache::flush();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', $xml);
        $this->assertStringContainsString('<image:loc>' . \App\Support\Seo::storage('products/a.webp') . '</image:loc>', $xml);
        $this->assertStringContainsString('<image:loc>' . \App\Support\Seo::storage('products/gallery/b.webp') . '</image:loc>', $xml);
        $this->assertNotFalse(simplexml_load_string($xml), 'still valid XML');
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
