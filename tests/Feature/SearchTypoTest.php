<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Support\SearchAssist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SearchTypoTest extends TestCase
{
    use RefreshDatabase;

    private function catalogue(): Product
    {
        Cache::forget('search_vocabulary');

        $brand = Brand::create(['name' => 'Hettich', 'slug' => 'hettich', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Centrifuges', 'slug' => 'centrifuges']);

        return Product::create(['category_id' => $cat->id, 'name' => 'Rotanta 460 R', 'slug' => 'rotanta-460-r', 'is_active' => true, 'short_description' => 'Refrigerated bench-top unit']);
    }

    public function test_a_misspelt_word_is_swapped_for_the_closest_real_word(): void
    {
        $this->catalogue();

        $this->assertSame('centrifuges', SearchAssist::correct('centrifuze'));
        $this->assertSame('hettich', SearchAssist::correct('hetich'));
        $this->assertSame('hettich centrifuges', SearchAssist::correct('hetich centrifuze'));
    }

    public function test_real_words_beginnings_of_words_and_nonsense_are_left_alone(): void
    {
        $this->catalogue();

        $this->assertNull(SearchAssist::correct('centrifuges'));     // already right
        $this->assertNull(SearchAssist::correct('centrif'));         // someone still typing
        $this->assertNull(SearchAssist::correct('hp'));              // too short to guess
        $this->assertNull(SearchAssist::correct('zzzznothing'));     // nothing is close
        $this->assertNull(SearchAssist::correct(''));
    }

    public function test_search_page_finds_the_product_and_tells_the_visitor_what_it_did(): void
    {
        $this->catalogue();

        $this->get('/search?q=centrifuze')
            ->assertOk()
            ->assertSee('Showing results for')
            ->assertSee('centrifuges')
            ->assertSee('Rotanta 460 R')
            ->assertSee('Search instead for');
    }

    public function test_the_visitor_can_insist_on_exactly_what_they_typed(): void
    {
        $this->catalogue();

        $this->get('/search?q=centrifuze&exact=1')
            ->assertOk()
            ->assertSee('No matches for')
            ->assertDontSee('Search instead for');       // no correction was made
    }

    public function test_a_search_that_already_works_is_never_changed(): void
    {
        $this->catalogue();

        $this->get('/search?q=centrifuges')->assertOk()->assertDontSee('Search instead for')->assertSee('Rotanta 460 R');
        // plural or singular needs no fixing: the normal search already finds both
        $this->get('/search?q=centrifuge')->assertOk()->assertDontSee('Search instead for')->assertSee('Rotanta 460 R');
    }

    public function test_live_suggestions_also_fix_the_spelling(): void
    {
        $this->catalogue();

        $json = $this->getJson('/search/suggest?q=centrifuze')->assertOk()->json();
        $this->assertSame('centrifuges', $json['corrected']);
        $this->assertNotEmpty($json['rows']);

        $this->getJson('/search/suggest?q=zzzznothing')->assertOk()->assertJson(['corrected' => null, 'rows' => []]);
        $this->getJson('/search/suggest?q=rotanta')->assertOk()->assertJson(['corrected' => null]);
    }

    public function test_empty_search_page_has_recent_searches_and_popular_instruments(): void
    {
        $product = $this->catalogue();
        $other = Product::create(['category_id' => $product->category_id, 'name' => 'Mikro 200 R', 'slug' => 'mikro-200-r', 'is_active' => true]);

        $enquiry = Enquiry::create(['name' => 'Asha', 'email' => 'asha@example.com']);
        $enquiry->items()->create(['product_id' => $other->id, 'product_name' => 'Mikro 200 R', 'quantity' => 1]);

        $html = $this->get('/search')->assertOk()->getContent();
        $this->assertStringContainsString('Your recent searches', $html);
        $this->assertStringContainsString('abSearches', $html);
        $this->assertStringContainsString('Popular instruments', $html);
        // the product people asked about comes first
        $this->assertLessThan(strpos($html, 'Rotanta 460 R'), strpos($html, 'Mikro 200 R'));

        // a search that finds nothing shows them too
        $this->get('/search?q=zzzznothing')->assertOk()->assertSee('No matches for')->assertSee('Popular instruments');
    }

    public function test_recent_searches_are_listed_on_the_privacy_page(): void
    {
        $this->get(route('privacy'))->assertOk()->assertSee('ab_recent_searches');
    }

    public function test_popular_brands_are_logo_cards_that_open_the_brand_page_and_the_busiest_come_first(): void
    {
        $product = $this->catalogue();                       // Hettich has one product line
        Brand::create(['name' => 'Empty Brand', 'slug' => 'empty-brand', 'is_active' => true]);

        $html = $this->get('/search')->assertOk()->getContent();

        $this->assertStringContainsString(route('brand.show', 'hettich'), $html);
        $this->assertStringContainsString('1 line', $html);
        $this->assertLessThan(strpos($html, 'Empty Brand'), strpos($html, 'Hettich'));
    }

    public function test_product_cards_lift_as_one_piece_so_their_buttons_move_with_them(): void
    {
        $this->catalogue();

        $html = $this->get('/search?q=rotanta')->assertOk()->getContent();

        // the wrapper (picture link + compare + list buttons) lifts; the link itself no longer lifts alone
        $this->assertStringContainsString('group transition-all duration-300 hover:-translate-y-1 relative flex', $html);
        $this->assertStringContainsString('group-hover:border-cyan/50 group-hover:shadow-xl', $html);
        $this->assertStringNotContainsString('duration-300 hover:-translate-y-1 hover:border-cyan/50', $html);
    }
}
