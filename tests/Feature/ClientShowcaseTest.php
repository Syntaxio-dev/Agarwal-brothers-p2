<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientCategory;
use App\Models\Review;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientShowcaseTest extends TestCase
{
    use RefreshDatabase;

    private function clients(int $n, ?ClientCategory $category = null): void
    {
        $category ??= ClientCategory::firstOrCreate(['name' => 'Pharma'], ['sort_order' => 1]);
        for ($i = 1; $i <= $n; $i++) {
            Client::create(['name' => "Lab {$i}", 'client_category_id' => $category->id, 'is_active' => true, 'sort_order' => $i]);
        }
    }

    public function test_home_shows_the_bento_with_logos_a_quote_and_the_number_tile(): void
    {
        $this->clients(7);
        Client::create(['name' => 'Hidden Lab', 'is_active' => false]);
        Review::create(['name' => 'Dr. Rao', 'designation' => 'QC Head', 'organization' => 'Sun Pharma', 'content' => 'Fast service and honest advice.', 'rating' => 5, 'is_active' => true, 'is_featured' => true]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('The labs that rely', $html);
        $this->assertStringContainsString('Fast service and honest advice.', $html);
        $this->assertStringContainsString('QC Head · Sun Pharma', $html);
        $this->assertStringContainsString('7+', $html);                    // automatic number = active clients
        $this->assertStringContainsString('Active clients', $html);
        $this->assertStringContainsString('+3 more clients', $html);       // 7 clients, 4 on show
        $this->assertStringContainsString('aria-label="Lab 1', $html);
        $this->assertStringNotContainsString('Hidden Lab', $html);
        // 7 clients = 2 pages of 4 logos that take turns
        $this->assertStringContainsString('pages: 2', $html);
    }

    public function test_the_number_tile_can_be_edited_in_site_settings(): void
    {
        $this->clients(2);
        SiteSetting::create(['clients_stat_number' => '300+', 'clients_stat_label' => 'Labs served']);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('300+', $html);
        $this->assertStringContainsString('Labs served', $html);
    }

    public function test_fewer_than_five_clients_do_not_rotate_and_have_no_more_tile(): void
    {
        $this->clients(4);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('pages: 1', $html);
        $this->assertStringNotContainsString(' more client', $html);
    }

    public function test_several_featured_reviews_take_turns_and_without_any_the_first_review_is_used(): void
    {
        $this->clients(3);
        Review::create(['name' => 'First', 'content' => 'One one one', 'rating' => 5, 'is_active' => true, 'sort_order' => 1]);
        Review::create(['name' => 'Second', 'content' => 'Two two two', 'rating' => 5, 'is_active' => true, 'sort_order' => 2]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('revs: 1', $html);               // none featured -> first review only

        Review::where('name', 'First')->update(['is_featured' => true]);
        Review::where('name', 'Second')->update(['is_featured' => true]);
        Review::create(['name' => 'Hidden', 'content' => 'Not shown', 'rating' => 5, 'is_active' => false, 'is_featured' => true]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('revs: 2', $html);
        $this->assertStringContainsString('Two two two', $html);
        $this->assertStringNotContainsString('Not shown', $html);
    }

    public function test_the_section_is_left_out_when_there_are_no_clients(): void
    {
        $this->get('/')->assertOk()->assertDontSee('The labs that rely');
    }

    public function test_the_world_map_section_is_switched_off_on_the_home_page(): void
    {
        $src = file_get_contents(resource_path('views/public/home.blade.php'));
        $this->assertStringContainsString('GLOBAL BRANDS MAP: switched off', $src);
        $this->assertMatchesRegularExpression('/GLOBAL BRANDS MAP[^\n]*\n\s*@if \(false\)/', $src);
    }

    public function test_the_number_tile_lists_categories_that_have_active_clients_in_order(): void
    {
        $pharma = ClientCategory::create(['name' => 'Pharma', 'sort_order' => 2]);
        $agro = ClientCategory::create(['name' => 'Agro', 'sort_order' => 1]);
        ClientCategory::create(['name' => 'Empty Category', 'sort_order' => 3]);          // no clients
        $hidden = ClientCategory::create(['name' => 'Switched Off', 'sort_order' => 4, 'is_active' => false]);
        $this->clients(2, $pharma);
        Client::create(['name' => 'Farm Lab', 'client_category_id' => $agro->id, 'is_active' => true]);
        Client::create(['name' => 'Off Lab', 'client_category_id' => $hidden->id, 'is_active' => true]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Across sectors', $html);
        $this->assertStringNotContainsString('Empty Category', $html);
        $this->assertStringNotContainsString('Switched Off', $html);
        // admin order: Agro first, then Pharma, each with its number of active clients
        $this->assertLessThan(strpos($html, 'Pharma'), strpos($html, 'Agro'));
        $this->assertMatchesRegularExpression('/count.{1,8}:\s*2/', $html);   // @js escapes the quotes
        $this->assertMatchesRegularExpression('/count.{1,8}:\s*1/', $html);
        $this->assertStringContainsString('2000', $html);            // each category stays about 2 seconds
        $this->assertStringContainsString('5000', $html);            // logos and quote change every 5 seconds
    }

    public function test_a_long_review_is_cut_short_with_a_read_full_review_popup(): void
    {
        $this->clients(2);
        $long = str_repeat('The instruments are reliable and the service team is quick. ', 6);
        Review::create(['name' => 'Dr. Long', 'content' => $long, 'rating' => 5, 'is_active' => true, 'is_featured' => true]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('line-clamp-4', $html);
        $this->assertStringContainsString('Read full review', $html);
        $this->assertStringContainsString('backdrop-blur-md', $html);
        $this->assertStringContainsString('aria-label="Close review"', $html);
        $this->assertStringContainsString('role="dialog"', $html);

        // a short review needs no button
        Review::query()->delete();
        Review::create(['name' => 'Dr. Short', 'content' => 'Great service.', 'rating' => 5, 'is_active' => true, 'is_featured' => true]);
        $short = $this->get('/')->assertOk()->getContent();
        $this->assertStringNotContainsString('line-clamp-4', $short);
        $this->assertStringNotContainsString('>Read full review', $short);
    }

    public function test_a_category_with_clients_cannot_be_deleted_and_an_empty_one_can(): void
    {
        $category = ClientCategory::create(['name' => 'Pharma']);
        $client = Client::create(['name' => 'Lab', 'client_category_id' => $category->id]);

        $this->assertFalse($category->delete());
        $this->assertDatabaseHas('client_categories', ['id' => $category->id]);

        $client->delete();
        $this->assertTrue($category->fresh()->delete());
    }

    public function test_the_client_form_has_a_searchable_category_dropdown_that_can_add_a_category(): void
    {
        $src = file_get_contents(app_path('Filament/Resources/Clients/Schemas/ClientForm.php'));
        $this->assertStringContainsString("Select::make('client_category_id')", $src);
        $this->assertStringContainsString('->searchable()', $src);
        $this->assertStringContainsString('->createOptionForm', $src);
        $this->assertStringContainsString('->createOptionUsing', $src);
    }

    public function test_the_owners_category_list_is_loaded_and_the_old_placeholders_are_gone(): void
    {
        $list = require database_path('data/client_categories.php');

        $this->assertCount(count($list), array_unique($list));            // no duplicates in the list itself
        $this->assertGreaterThan(60, count($list));
        foreach (['Dairy & Dairy Products', 'Textiles & Apparel', 'NABL-Accredited Independent Testing Labs', 'Oil & Gas Refineries'] as $name) {
            $this->assertContains($name, $list);
            $this->assertDatabaseHas('client_categories', ['name' => $name, 'is_active' => true]);
        }
        $this->assertSame(count($list), ClientCategory::whereIn('name', $list)->count());
        foreach (['Pharma & Biotech', 'Dairy & Food', 'Government & Research Labs', 'Universities & Institutes',
                  'Hospitals & Diagnostics', 'Agriculture & Environment', 'Chemicals & Industry'] as $old) {
            $this->assertDatabaseMissing('client_categories', ['name' => $old]);
        }
    }
}
