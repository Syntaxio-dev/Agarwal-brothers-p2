<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\CatalogueSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The site search finds products by model number (any spacing), by specification ("readability 0.001") and by
 * words in any order ("aczet analytical balance"). Data shaped like the real Aczet / Sartorius catalogue.
 */
class CatalogueSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        CatalogueSearch::forget();

        $aczet = Brand::create(['name' => 'Aczet', 'slug' => 'aczet', 'is_active' => true]);
        $sartorius = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $aczetAnalytical = Category::create(['brand_id' => $aczet->id, 'name' => 'Analytical Balance', 'slug' => 'analytical-balance']);
        $aczetPrecision = Category::create(['brand_id' => $aczet->id, 'name' => 'Precision Balance', 'slug' => 'precision-balance']);
        $sartAnalytical = Category::create(['brand_id' => $sartorius->id, 'name' => 'Analytical Lab Balances', 'slug' => 'analytical']);
        $sartMicro = Category::create(['brand_id' => $sartorius->id, 'name' => 'Micro Lab Balances', 'slug' => 'micro']);

        Product::create(['category_id' => $aczetAnalytical->id, 'name' => 'CY 224C', 'slug' => 'cy-224c', 'is_active' => true,
            'heading' => 'Aczet CY 224C Analytical Balance 220 g × 0.1 mg',
            'specs' => ['Model' => 'CY 224C', 'Weighing capacity (g)' => '220', 'Readability (mg)' => '0.1', 'Pan size (mm)' => 'Ø 90']]);
        Product::create(['category_id' => $aczetAnalytical->id, 'name' => 'CY 124C', 'slug' => 'cy-124c', 'is_active' => true,
            'specs' => ['Model' => 'CY 124C', 'Weighing capacity (g)' => '120', 'Readability (mg)' => '0.1']]);
        Product::create(['category_id' => $aczetPrecision->id, 'name' => 'CY 323C', 'slug' => 'cy-323c', 'is_active' => true,
            'specs' => ['Model' => 'CY 323C', 'Weighing capacity (g)' => '320', 'Readability (g)' => '0.001', 'Linearity (g)' => '0.002']]);
        Product::create(['category_id' => $aczetPrecision->id, 'name' => 'CY Series Top-Pan Precision Balance', 'slug' => 'cy-top-pan', 'is_active' => true,
            'specs' => ['Model' => 'CY 312, CY 612, CY 1202, CY 3102', 'Readability (g)' => '0.01', 'Linearity (g)' => '0.001']]);
        Product::create(['category_id' => $sartAnalytical->id, 'name' => 'BSA / BSA II Analytical', 'slug' => 'bsa-analytical', 'is_active' => true,
            'specs_general' => true,
            'specs' => ['Model' => 'BSA II: BSA224I-1x, BSA224-1x / BSA: BSA224S-CW, BSA224S, BSA124S', 'Readability (mg)' => '0.1']]);
        Product::create(['category_id' => $sartMicro->id, 'name' => 'Cubis II Micro', 'slug' => 'cubis-ii-micro', 'is_active' => true,
            'specs_general' => true,
            'specs' => ['Readability (mg)' => '0.001 (1 µg) to 0.01 (10 µg)', 'Weighing capacity (g)' => '1.1 to 111']]);
        Product::create(['category_id' => $sartMicro->id, 'name' => 'Hidden Draft CY 224C', 'slug' => 'hidden', 'is_active' => false]);
    }

    private function names(string $q): array
    {
        return CatalogueSearch::run($q, 60, 8, 10, 6)['products']->pluck('name')->all();
    }

    public function test_a_model_number_is_found_with_or_without_spaces_and_dashes(): void
    {
        foreach (['CY 224C', 'cy224c', 'CY-224C', 'cy 224 c'] as $q) {
            $this->assertSame('CY 224C', $this->names($q)[0] ?? null, "first result for \"{$q}\"");
            $this->assertNotContains('Hidden Draft CY 224C', $this->names($q), 'hidden products never show');
        }
    }

    public function test_a_model_listed_in_a_series_finds_that_series(): void
    {
        $this->assertSame(['BSA / BSA II Analytical'], $this->names('BSA224S'));
        $this->assertSame(['BSA / BSA II Analytical'], $this->names('bsa-224s-cw'));
        $this->assertContains('CY Series Top-Pan Precision Balance', $this->names('cy 3102'));
    }

    public function test_an_unknown_model_falls_back_to_the_closest_real_model(): void
    {
        $r = CatalogueSearch::run('BSA-2240ion', 60, 8, 10, 6);

        $this->assertSame('bsa224', $r['corrected']);
        $this->assertSame(['BSA / BSA II Analytical'], $r['products']->pluck('name')->all());

        // ... but not when the visitor asked for exactly what they typed
        $this->assertTrue(CatalogueSearch::run('BSA-2240ion', 60, 8, 10, 6, false)['products']->isEmpty());
    }

    public function test_a_specification_search_finds_the_products_with_that_value_in_that_row(): void
    {
        $r = CatalogueSearch::run('readability 0.001', 60, 8, 10, 6);
        $names = $r['products']->pluck('name')->all();

        $this->assertContains('CY 323C', $names);                 // Readability (g): 0.001
        $this->assertContains('Cubis II Micro', $names);          // 0.001 to 0.01 mg
        $this->assertNotContains('CY Series Top-Pan Precision Balance', $names, '0.001 is its LINEARITY, not its readability');
        $this->assertNotContains('CY 224C', $names);

        $cy323 = $r['products']->firstWhere('name', 'CY 323C');
        $this->assertSame(['Readability (g)'], $r['matches'][$cy323->id], 'the matching row is shown on the card');
    }

    public function test_units_are_understood(): void
    {
        // 0.1 mg = 0.0001 g; 1 mg = 0.001 g
        $this->assertEqualsCanonicalizing(['CY 224C', 'CY 124C', 'BSA / BSA II Analytical'], $this->names('readability 0.1 mg'));
        $this->assertContains('CY 323C', $this->names('readability 1 mg'));
        $this->assertContains('CY 323C', $this->names('readability 0.001g'));
        // a weight never matches a number in another unit (CY 224C's pan size is 90 mm) ...
        $this->assertNotContains('CY 224C', $this->names('90 g'));
        // ... but does fall inside a weight range (Cubis II Micro: 1.1 to 111 g)
        $this->assertSame(['Cubis II Micro'], $this->names('90 g'));
        $this->assertEqualsCanonicalizing(['CY 224C'], $this->names('capacity 220 g'));
    }

    public function test_words_match_in_any_order_and_across_brand_line_and_name(): void
    {
        $this->assertEqualsCanonicalizing(['CY 224C', 'CY 124C'], $this->names('aczet analytical balance'));
        $this->assertEqualsCanonicalizing(['CY 224C', 'CY 124C'], $this->names('balances analytical aczet'));
        $this->assertCount(4, $this->names('aczet balance price in jaipur'), 'price / in / jaipur are ignored');
        $this->assertEqualsCanonicalizing(['Cubis II Micro'], $this->names('sartorius micro'));
    }

    public function test_most_words_matching_is_shown_as_a_partial_result(): void
    {
        $r = CatalogueSearch::run('aczet analytical zebra', 60, 8, 10, 6);

        $this->assertTrue($r['partial']);
        $this->assertEqualsCanonicalizing(['CY 224C', 'CY 124C'], $r['products']->pluck('name')->all());
    }

    public function test_the_search_page_and_live_suggestions_use_it(): void
    {
        $this->get('/search?q=cy224c')->assertOk()->assertSee('CY 224C');

        $html = $this->get('/search?q=' . urlencode('readability 0.001'))->assertOk()->getContent();
        $this->assertStringContainsString('CY 323C', $html);
        $this->assertStringContainsString('font-semibold text-cyan-ink">Readability (g)</p>', $html, 'the matching spec is first on the card and highlighted');

        $this->get('/search?q=BSA-2240ion')->assertOk()->assertSee('Showing results for')->assertSee('bsa224');
        $this->get('/search?q=aczet+analytical+zebra')->assertOk()->assertSee('These products match most of it');

        $rows = $this->getJson('/search/suggest?q=' . urlencode('readability 0.001'))->assertOk()->json('rows');
        $row = collect($rows)->firstWhere('label', 'CY 323C');
        $this->assertSame('Readability (g): 0.001', $row['sub']);
    }

    public function test_the_index_is_rebuilt_when_a_product_changes(): void
    {
        $this->assertSame([], $this->names('zz9000'));

        Product::where('name', 'CY 124C')->first()->update(['name' => 'ZZ9000']);

        $this->assertSame(['ZZ9000'], $this->names('zz9000'));
    }
}
