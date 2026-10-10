<?php

namespace Tests\Feature;

use App\Filament\Pages\HelpGuide as HelpPage;
use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Support\AdminSearchProvider;
use App\Filament\Widgets\QuickUpdates;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Country;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Slide;
use App\Models\User;
use App\Support\HelpGuide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminToolsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    private function product(array $over = [], ?Category $cat = null): Product
    {
        $cat ??= Category::create([
            'brand_id' => Brand::create(['name' => 'Sartorius ' . uniqid(), 'slug' => 'sar-' . uniqid(), 'is_active' => true])->id,
            'name' => 'Balances', 'slug' => 'bal-' . uniqid(),
        ]);

        return Product::create($over + ['category_id' => $cat->id, 'name' => 'Item ' . uniqid(), 'slug' => 'item-' . uniqid(), 'is_active' => true]);
    }

    // ---------------------------------------------------------------- bulk actions

    public function test_bulk_activate_deactivate_and_move(): void
    {
        $this->actingAs($this->admin());
        $a = $this->product(['is_active' => false]);
        $b = $this->product(['is_active' => false]);
        $target = Category::create(['brand_id' => Brand::create(['name' => 'Acculab', 'slug' => 'acculab', 'is_active' => true])->id, 'name' => 'Lab', 'slug' => 'lab']);

        Livewire::test(ListProducts::class)->callTableBulkAction('activate', [$a, $b]);
        $this->assertSame([true, true], [$a->fresh()->is_active, $b->fresh()->is_active]);

        Livewire::test(ListProducts::class)->callTableBulkAction('deactivate', [$a]);
        $this->assertSame([false, true], [$a->fresh()->is_active, $b->fresh()->is_active]);

        Livewire::test(ListProducts::class)->callTableBulkAction('set_category', [$a, $b], data: ['category_id' => $target->id]);
        $this->assertSame([$target->id, $target->id], [$a->fresh()->category_id, $b->fresh()->category_id]);
    }

    // ---------------------------------------------------------------- to-do filters and dashboard

    public function test_todo_filters_find_the_right_products_brands_and_slides(): void
    {
        $this->actingAs($this->admin());
        $bare = $this->product(['image' => null]);
        $full = $this->product(['image' => 'products/a.png', 'specs' => ['A' => '1'], 'seo_title' => 'T', 'seo_description' => 'D']);

        Livewire::test(ListProducts::class)->filterTable('no_image')->assertCanSeeTableRecords([$bare])->assertCanNotSeeTableRecords([$full]);
        Livewire::test(ListProducts::class)->filterTable('no_specs')->assertCanSeeTableRecords([$bare])->assertCanNotSeeTableRecords([$full]);
        Livewire::test(ListProducts::class)->filterTable('no_seo')->assertCanSeeTableRecords([$bare])->assertCanNotSeeTableRecords([$full]);

        $noCountry = Brand::create(['name' => 'NoCountry', 'slug' => 'nc', 'is_active' => true]);
        $withCountry = Brand::create(['name' => 'WithCountry', 'slug' => 'wc', 'is_active' => true, 'country_id' => Country::create(['name' => 'Germany', 'latitude' => 51, 'longitude' => 10])->id, 'logo' => 'brands/x.png']);
        Livewire::test(ListBrands::class)->filterTable('no_country')->assertCanSeeTableRecords([$noCountry])->assertCanNotSeeTableRecords([$withCountry]);
        Livewire::test(ListBrands::class)->filterTable('no_logo')->assertCanSeeTableRecords([$noCountry])->assertCanNotSeeTableRecords([$withCountry]);
    }

    public function test_dashboard_cards_count_and_link_to_filtered_lists(): void
    {
        $this->actingAs($this->admin());
        $this->product(['image' => null, 'is_active' => true]);
        $this->product(['image' => null, 'is_active' => false]);                 // draft: counted as draft, not as "without photo"
        Brand::create(['name' => 'NoCountry', 'slug' => 'nc', 'is_active' => true]);
        Slide::create(['title' => 'x', 'image' => 'slides/a.png', 'is_active' => true]);
        Enquiry::create(['name' => 'Ravi', 'email' => 'r@example.com', 'status' => 'new']);

        $cards = collect(app(QuickUpdates::class)->getItems())->keyBy('label');

        $this->assertSame(1, $cards['Products without a photo']['count']);
        $this->assertSame(1, $cards['Draft products']['count']);
        $this->assertSame(3, $cards['Brands without a country']['count']);      // NoCountry + the two brands made for the products
        $this->assertSame(1, $cards['Slides without a description']['count']);
        $this->assertSame(1, $cards['New product enquiries']['count']);

        // The links open the list with the same filters the count used
        $this->assertStringContainsString('no_image', urldecode($cards['Products without a photo']['url']));
        $this->assertStringContainsString('no_country', urldecode($cards['Brands without a country']['url']));

        $this->get('/admin')->assertOk();
        Livewire::test(QuickUpdates::class)->assertSee('Add product')->assertSee('Import products')->assertSee('Tidy up')->assertSee('Needs a reply');
    }

    public function test_dashboard_links_really_open_the_filtered_list(): void
    {
        $this->actingAs($this->admin());
        $bareLive = $this->product(['image' => null, 'is_active' => true]);
        $bareDraft = $this->product(['image' => null, 'is_active' => false]);
        $full = $this->product(['image' => 'products/a.png', 'is_active' => true]);
        Enquiry::create(['name' => 'Fresh', 'email' => 'f@example.com', 'status' => 'new']);
        Enquiry::create(['name' => 'Done', 'email' => 'd@example.com', 'status' => 'closed']);

        $cards = collect(app(QuickUpdates::class)->getItems())->keyBy('label');
        $query = fn (string $label) => (function (string $url) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $q);

            return $q;
        })($cards[$label]['url']);

        Livewire::withQueryParams($query('Products without a photo'))->test(ListProducts::class)
            ->assertCanSeeTableRecords([$bareLive])->assertCanNotSeeTableRecords([$bareDraft, $full]);

        Livewire::withQueryParams($query('Draft products'))->test(ListProducts::class)
            ->assertCanSeeTableRecords([$bareDraft])->assertCanNotSeeTableRecords([$bareLive, $full]);

        Livewire::withQueryParams($query('New product enquiries'))->test(\App\Filament\Resources\Enquiries\Pages\ListEnquiries::class)
            ->assertSee('Fresh')->assertDontSee('Done');
    }

    public function test_dashboard_cards_follow_the_role(): void
    {
        $this->actingAs($this->admin('hr'));

        $labels = collect(app(QuickUpdates::class)->getItems())->pluck('label');

        $this->assertTrue($labels->contains('New job applications'));
        $this->assertFalse($labels->contains('Products without a photo'));
        $this->assertFalse($labels->contains('New product enquiries'));
    }

    // ---------------------------------------------------------------- View on site and preview

    public function test_view_on_site_button_and_preview_mode(): void
    {
        $user = $this->admin();
        $draft = $this->product(['is_active' => false, 'name' => 'Secret Draft', 'slug' => 'secret-draft']);
        $live = $this->product(['name' => 'Live One', 'slug' => 'live-one']);

        // Visitors and guests never see the draft, even with ?preview=1
        $this->get(route('product.show', 'secret-draft'))->assertNotFound();
        $this->get(route('product.show', 'secret-draft') . '?preview=1')->assertNotFound();

        // Staff can preview it, with a clear notice and no indexing
        $this->actingAs($user);
        $this->get(route('product.show', 'secret-draft') . '?preview=1')->assertOk()
            ->assertSee('Preview mode')->assertSee('Secret Draft')->assertSee('noindex', false);
        $this->get(route('product.show', 'secret-draft'))->assertNotFound();           // without the flag it is still hidden
        $this->get(route('product.show', 'live-one'))->assertOk()->assertDontSee('Preview mode');

        // Buttons on the edit pages
        Livewire::test(EditProduct::class, ['record' => $draft->getRouteKey()])->assertActionExists('view_on_site')->assertSee('Preview on site');
        Livewire::test(EditProduct::class, ['record' => $live->getRouteKey()])->assertSee('View on site')->assertDontSee('Preview on site');
    }

    public function test_inactive_staff_cannot_preview_and_other_pages_work_too(): void
    {
        $brand = Brand::create(['name' => 'Hidden Brand', 'slug' => 'hidden-brand', 'is_active' => false]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Line', 'slug' => 'line']);

        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => false]));
        $this->get(route('brand.show', 'hidden-brand') . '?preview=1')->assertNotFound();

        $this->actingAs($this->admin());
        $this->get(route('brand.show', 'hidden-brand') . '?preview=1')->assertOk()->assertSee('Preview mode');
        $this->get(route('category.show', ['hidden-brand', 'line']) . '?preview=1')->assertOk();
        Livewire::test(EditBrand::class, ['record' => $brand->getRouteKey()])->assertSee('Preview on site');
    }

    public function test_product_edit_page_shows_the_card_preview_and_checklist(): void
    {
        $this->actingAs($this->admin());
        $p = $this->product(['name' => 'Preview Me', 'image' => null, 'short_description' => null]);

        Livewire::test(EditProduct::class, ['record' => $p->getRouteKey()])
            ->assertSee('Card preview')->assertSee('Preview Me')->assertSee('Quick check')
            ->assertSee('Upload a main image')->assertSee('Add one or two lines');
    }

    // ---------------------------------------------------------------- admin search

    public function test_search_finds_products_brands_enquiries_and_guide_topics(): void
    {
        $this->actingAs($this->admin());
        $p = $this->product(['name' => 'Quintix Balance 224']);
        $brand = $p->category->brand;
        Enquiry::create(['name' => 'Quentin Customer', 'email' => 'quentin@example.com', 'phone' => '+91 9876543210', 'status' => 'new']);

        $titles = fn (string $q) => collect((new AdminSearchProvider)->getResults($q)->getCategories())
            ->map(fn ($results) => collect($results)->map(fn ($r) => (string) $r->title)->all())->all();

        $this->assertContains('Quintix Balance 224', $titles('quintix')['Products']);
        $this->assertContains($brand->name, $titles('sartorius')['Brands']);
        $this->assertContains('Quentin Customer', $titles('quentin@example')['Enquiries']);
        $this->assertContains('Quentin Customer', $titles('9876543210')['Enquiries']);       // by phone
        $this->assertArrayHasKey('How-to guide', $titles('how to add a brand'));
        $this->assertContains('Adding a product (full steps)', $titles('how to upload product')['How-to guide']);
        $this->assertSame([], (new AdminSearchProvider)->getResults('q')->getCategories()->all());   // too short
    }

    public function test_search_only_shows_what_the_role_may_open(): void
    {
        $this->product(['name' => 'Quintix Balance 224']);
        Enquiry::create(['name' => 'Quentin Customer', 'email' => 'q@example.com', 'status' => 'new']);

        $this->actingAs($this->admin('hr'));
        $cats = (new AdminSearchProvider)->getResults('quin')->getCategories()->keys()->all();

        $this->assertNotContains('Products', $cats);
        $this->assertNotContains('Enquiries', $cats);
    }

    public function test_guide_search_understands_questions_and_roles(): void
    {
        $admin = $this->admin();
        $ids = fn (string $q, $u = null) => array_column(HelpGuide::search($q, $u ?? $admin), 'id');

        $this->assertContains('import', $ids('how to import products from excel'));
        $this->assertContains('import', $ids('csv upload'));
        $this->assertContains('duplicate', $ids('copy a product'));
        $this->assertContains('bulk', $ids('activate many products'));
        $this->assertContains('users', $ids('how do I add a user'));
        $this->assertContains('catalogue', $ids('how to add brand'));
        $this->assertContains('preview', $ids('view on site'));
        $this->assertSame([], $ids('zzzzqqq nothing'));
        $this->assertSame([], $ids('how to'));                                                  // only filler words

        // HR cannot open products, so product topics are not suggested to them
        $this->assertNotContains('import', $ids('import products excel', $this->admin('hr')));
    }

    public function test_help_page_has_the_new_topics_and_links_from_search_open_the_right_one(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/help')->assertOk()
            ->assertSee('Uploading many products at once')
            ->assertSee('Changing many products at once')
            ->assertSee('Checking how a page looks')
            ->assertSee('Dashboard and search');

        $this->assertStringEndsWith('/admin/help', HelpPage::getUrl());
        $this->assertSame('import', HelpGuide::topicFor('admin/import-products'));
    }
}
