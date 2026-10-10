<?php

namespace Tests\Feature;

use App\Models\Vertical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivacyAndHelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_offers_popular_verticals_and_phone_numbers(): void
    {
        Vertical::create(['name' => 'Analytical Chemistry', 'slug' => 'analytical', 'is_active' => true, 'sort_order' => 1]);
        Vertical::create(['name' => 'Hidden One', 'slug' => 'hidden', 'is_active' => false, 'sort_order' => 2]);

        $res = $this->get('/definitely-not-a-page')->assertNotFound();

        $res->assertSee('Popular verticals')
            ->assertSee('Analytical Chemistry')
            ->assertDontSee('Hidden One')
            ->assertSee('Talk to our team')
            ->assertSee(config('contact.call'))
            ->assertSee('href="tel:', false);
    }

    public function test_empty_states_show_contact_numbers(): void
    {
        $this->get('/search?q=zzzznothing')->assertOk()->assertSee('Talk to our team');
        $this->get(route('compare'))->assertOk()->assertSee('Talk to our team');
        $this->get(route('enquiry-list'))->assertOk()->assertSee('Talk to our team');
    }

    public function test_privacy_page_lists_saved_data_and_choices(): void
    {
        $this->get(route('privacy'))->assertOk()
            ->assertSee('Privacy policy')
            ->assertSee('ab_compare')
            ->assertSee('ab_enquiry_list')
            ->assertSee('Clear saved data')
            ->assertSee('Open cookie settings')
            ->assertSee(config('privacy.contact_email'))
            ->assertDontSee('_ga');   // no analytics configured
    }

    public function test_privacy_page_mentions_analytics_only_when_it_is_configured(): void
    {
        config(['services.analytics_id' => 'G-TEST123456']);

        $this->get(route('privacy'))->assertOk()->assertSee('_ga')->assertSee('Accept all', false);
        $this->get('/')->assertSee('name="analytics-id" content="G-TEST123456"', false)->assertSee('Accept all');
    }

    public function test_cookie_notice_and_footer_links_are_on_every_page(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('Cookies &amp; saved data', false)
            ->assertSee('Got it')
            ->assertSee('Cookie settings')
            ->assertSee(route('privacy'))
            ->assertDontSee('analytics-id');
    }

    public function test_privacy_page_is_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('/privacy-policy');
    }

    public function test_housekeeping_removes_only_throwaway_data(): void
    {
        config(['session.driver' => 'database', 'cache.default' => 'database']);
        $now = time();

        DB::table('sessions')->insert([
            ['id' => 'old', 'payload' => 'x', 'last_activity' => $now - 60 * 60 * 24 * 30],
            ['id' => 'fresh', 'payload' => 'x', 'last_activity' => $now],
        ]);
        DB::table('cache')->insert([
            ['key' => 'expired', 'value' => 'x', 'expiration' => $now - 100],
            ['key' => 'alive', 'value' => 'x', 'expiration' => $now + 1000],
        ]);

        $disk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
        Storage::fake($disk);
        Storage::disk($disk)->put('livewire-tmp/stale.png', 'x');
        touch(Storage::disk($disk)->path('livewire-tmp/stale.png'), $now - 60 * 60 * 48);
        Storage::disk($disk)->put('livewire-tmp/new.png', 'x');

        $this->artisan('housekeeping:run')->assertSuccessful();

        $this->assertSame(['fresh'], DB::table('sessions')->pluck('id')->all());
        $this->assertSame(['alive'], DB::table('cache')->pluck('key')->all());
        Storage::disk($disk)->assertMissing('livewire-tmp/stale.png');
        Storage::disk($disk)->assertExists('livewire-tmp/new.png');
    }
}
