<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\BusinessPulse;
use App\Filament\Widgets\CatalogueHealth;
use App\Filament\Widgets\DashboardHeader;
use App\Filament\Widgets\EnquirySources;
use App\Filament\Widgets\EnquiryTrend;
use App\Filament\Widgets\MyShortcuts;
use App\Filament\Widgets\ResponseWatch;
use App\Filament\Widgets\TeamWorkload;
use App\Filament\Widgets\TopProducts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\EnquiryItem;
use App\Models\Insight;
use App\Models\Product;
use App\Models\User;
use App\Models\Vertical;
use App\Support\DashboardData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPulseTest extends TestCase
{
    use RefreshDatabase;

    private function as(string $role): User
    {
        $user = User::factory()->create(['role' => $role, 'is_active' => true, 'name' => 'Ayush Sharma']);
        $this->actingAs($user);

        return $user;
    }

    private function product(string $name, ?Vertical $vertical = null, array $over = [], string $line = 'bsa'): Product
    {
        $brand = Brand::firstOrCreate(['slug' => 'sartorius'], ['name' => 'Sartorius', 'is_active' => true]);
        $cat = Category::firstOrCreate(['slug' => $line], ['brand_id' => $brand->id, 'name' => strtoupper($line)]);
        if ($vertical) {
            $cat->verticals()->syncWithoutDetaching([$vertical->id]);
        }

        return Product::create($over + ['category_id' => $cat->id, 'name' => $name, 'slug' => str($name)->slug()->toString(), 'is_active' => true]);
    }

    private function enquiry(array $over = [], ?string $createdAgo = null): Enquiry
    {
        $e = Enquiry::create($over + ['name' => 'Ravi', 'email' => 'r@example.com']);

        if ($createdAgo) {
            $e->forceFill(['created_at' => now()->sub($createdAgo)])->saveQuietly();
        }

        return $e->fresh();
    }

    // ------------------------------------------------------------------ range

    public function test_the_range_is_7_30_or_90_and_anything_else_falls_back_to_30(): void
    {
        $this->assertSame(30, DashboardData::days(null));
        $this->assertSame(7, DashboardData::days(['range' => '7']));
        $this->assertSame(90, DashboardData::days(['range' => 90]));
        $this->assertSame(30, DashboardData::days(['range' => '365']));
        $this->assertSame(30, DashboardData::days(['range' => 'abc']));
    }

    public function test_the_dashboard_page_offers_the_range_switch(): void
    {
        $this->as('admin');
        $this->get('/admin')->assertOk()->assertSee('Show numbers for')->assertSee('Last 7 days')->assertSee('Last 90 days');
        $this->assertTrue(method_exists(Dashboard::class, 'filtersForm'));
    }

    // ------------------------------------------------------------------ pulse numbers

    public function test_counts_compare_with_the_range_before_and_daily_lists_have_one_value_per_day(): void
    {
        $this->enquiry();
        $this->enquiry();
        $this->enquiry([], '10 days');
        $this->enquiry([], '40 days');                    // in the 30 days before the range
        ContactMessage::create(['name' => 'M', 'email' => 'm@example.com', 'phone' => '9876543210', 'message' => 'hi']);

        $cmp = DashboardData::compare(Enquiry::class, 30);
        $this->assertSame(3, $cmp['now']);
        $this->assertSame(1, $cmp['before']);
        $this->assertSame(200, $cmp['change']);

        $this->assertNull(DashboardData::compare(ContactMessage::class, 30)['change']);      // nothing before: no percentage

        $daily = DashboardData::daily(Enquiry::class, 30);
        $this->assertCount(30, $daily['data']);
        $this->assertSame(3, array_sum($daily['data']));
        $this->assertSame(2, end($daily['data']));        // today
        $this->assertCount(7, DashboardData::daily(Enquiry::class, 7)['labels']);
    }

    public function test_the_pulse_card_only_shows_what_the_role_may_see(): void
    {
        $this->as('hr');
        $labels = fn () => collect((new \ReflectionMethod(BusinessPulse::class, 'getStats'))->invoke(new BusinessPulse))->map(fn ($s) => $s->getLabel())->all();
        $this->assertSame(['Job applications'], $labels());

        $this->as('sales');
        $this->assertSame(['Enquiries', 'Contact messages', 'Average first response'], $labels());

        $this->as('editor');
        $this->assertFalse(BusinessPulse::canView());

        $this->as('admin');
        Livewire::test(BusinessPulse::class)->assertSee('Enquiries')->assertSee('Job applications')->assertSee('Average first response');
    }

    // ------------------------------------------------------------------ response time

    public function test_the_first_response_time_is_stored_once_when_a_new_enquiry_is_moved_on(): void
    {
        $e = $this->enquiry();
        $this->assertNull($e->first_responded_at);

        $e->update(['status' => 'contacted']);
        $first = $e->fresh()->first_responded_at;
        $this->assertNotNull($first);
        $this->assertSame('contacted', $e->fresh()->status);
        $this->assertStringContainsString('Status changed from New to Contacted', $e->fresh()->teamNotes()->first()->body);   // the log still works

        $this->travel(2)->hours();
        $e->fresh()->update(['status' => 'quoted']);
        $this->assertTrue($first->equalTo($e->fresh()->first_responded_at));      // later changes do not move it
    }

    public function test_average_response_counts_only_answered_enquiries_of_the_range(): void
    {
        $this->assertNull(DashboardData::averageResponse(30)['hours']);

        $a = $this->enquiry([], '5 hours');
        $a->forceFill(['first_responded_at' => $a->created_at->copy()->addHours(2)])->saveQuietly();
        $b = $this->enquiry([], '5 hours');
        $b->forceFill(['first_responded_at' => $b->created_at->copy()->addHours(4)])->saveQuietly();
        $this->enquiry([], '3 hours');                                              // unanswered: not counted
        $old = $this->enquiry([], '60 days');
        $old->forceFill(['first_responded_at' => $old->created_at->copy()->addHours(50)])->saveQuietly();   // outside the range

        $avg = DashboardData::averageResponse(30);
        $this->assertSame(3.0, $avg['hours']);
        $this->assertSame(2, $avg['count']);
    }

    public function test_human_hours_reads_like_a_person_would_say_it(): void
    {
        $this->assertSame('-', DashboardData::humanHours(null));
        $this->assertSame('30 min', DashboardData::humanHours(0.5));
        $this->assertSame('5 h', DashboardData::humanHours(5.0));
        $this->assertSame('1.5 h', DashboardData::humanHours(1.5));
        $this->assertSame('2.5 days', DashboardData::humanHours(60.0));
    }

    public function test_oldest_waiting_picks_the_longest_wait_across_enquiries_and_messages(): void
    {
        $user = $this->as('admin');
        $this->assertNull(DashboardData::oldestPending($user));
        Livewire::test(ResponseWatch::class)->assertSee('Nothing is waiting');

        $this->enquiry(['name' => 'Fresh'], '1 hour');
        $this->enquiry(['name' => 'Handled', 'status' => 'contacted'], '9 days');          // not new: ignored
        $m = ContactMessage::create(['name' => 'Old Message', 'email' => 'o@example.com', 'phone' => '9876543210', 'message' => 'x']);
        $m->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();

        $oldest = DashboardData::oldestPending($user);
        $this->assertSame('Old Message', $oldest['name']);
        $this->assertSame('Message', $oldest['type']);
        $this->assertGreaterThan(70, $oldest['hours']);

        Livewire::test(ResponseWatch::class)->assertSee('Old Message')->assertSee('later than the');

        // an HR person sees neither
        $this->assertNull(DashboardData::oldestPending($this->as('hr')));
        $this->assertFalse(ResponseWatch::canView());
    }

    // ------------------------------------------------------------------ top products and sources

    public function test_top_products_count_single_and_group_enquiries_inside_the_range(): void
    {
        $a = $this->product('BSA224S');
        $b = $this->product('BSA124S');
        $c = $this->product('BSA423S');

        $this->enquiry(['product_id' => $a->id]);
        $this->enquiry(['product_id' => $a->id]);
        $group = $this->enquiry();
        foreach ([$a, $b] as $p) {
            EnquiryItem::create(['enquiry_id' => $group->id, 'product_id' => $p->id, 'product_name' => $p->name, 'quantity' => 3]);
        }
        $this->enquiry(['product_id' => $c->id], '50 days');                // outside the range

        $rows = DashboardData::topProducts(30, 5);

        $this->assertSame(['BSA224S', 'BSA124S'], $rows->pluck('name')->all());
        $this->assertSame([3, 1], $rows->pluck('count')->all());
        $this->assertSame('Sartorius', $rows[0]['brand']);
        $this->assertContains('BSA423S', DashboardData::topProducts(90, 5)->pluck('name')->all());      // the wider range reaches the 50-day-old one
        $this->assertNotContains('BSA423S', $rows->pluck('name')->all());

        $this->as('admin');
        Livewire::test(TopProducts::class)->assertSee('BSA224S')->assertSee('3 enquiries');
    }

    public function test_sources_group_enquiries_by_vertical_and_count_general_ones_apart(): void
    {
        $balances = Vertical::create(['name' => 'Weighing Balances', 'slug' => 'wb', 'is_active' => true]);
        $other = Vertical::create(['name' => 'Microscopes', 'slug' => 'mi', 'is_active' => true]);
        $p = $this->product('BSA224S', $balances);
        $both = $this->product('Combo', $other, [], 'combo');
        $both->category->verticals()->attach($balances->id);

        $this->enquiry(['product_id' => $p->id]);
        $this->enquiry(['product_id' => $p->id]);
        $this->enquiry(['product_id' => $both->id]);       // counts in both of its verticals
        $this->enquiry();                                  // no product at all

        $counts = DashboardData::bySource(30);

        $this->assertSame(['Weighing Balances' => 3, 'Microscopes' => 1, 'Other / general' => 1], $counts->all());

        $this->as('admin');
        Livewire::test(EnquirySources::class)->assertSee('Enquiries by vertical');
    }

    public function test_the_charts_follow_the_chosen_range(): void
    {
        $this->as('admin');
        $this->enquiry();

        $trend = Livewire::test(EnquiryTrend::class, ['pageFilters' => ['range' => '7']]);
        $this->assertCount(7, (new \ReflectionMethod(EnquiryTrend::class, 'getData'))->invoke($trend->instance())['labels']);
        $trend->assertSee('last 7 days');

        $trend90 = Livewire::test(EnquiryTrend::class, ['pageFilters' => ['range' => '90']]);
        $this->assertCount(90, (new \ReflectionMethod(EnquiryTrend::class, 'getData'))->invoke($trend90->instance())['labels']);
    }

    // ------------------------------------------------------------------ workload

    public function test_workload_counts_open_enquiries_per_person_and_unassigned(): void
    {
        $asha = User::factory()->create(['role' => 'sales', 'is_active' => true, 'name' => 'Asha']);
        $ravi = User::factory()->create(['role' => 'sales', 'is_active' => true, 'name' => 'Ravi']);

        $this->enquiry(['assigned_to' => $asha->id], '3 days');
        $this->enquiry(['assigned_to' => $asha->id]);
        $this->enquiry(['assigned_to' => $ravi->id, 'status' => 'quoted']);
        $this->enquiry(['assigned_to' => $ravi->id, 'status' => 'closed']);      // closed: not workload
        $this->enquiry();                                                        // nobody yet

        $rows = DashboardData::workload();

        $this->assertSame(['Asha' => 2, 'Nobody yet' => 1, 'Ravi' => 1], $rows->pluck('open', 'name')->all());      // busiest first, ties by name
        $this->assertTrue($rows->firstWhere('name', 'Nobody yet')['unassigned']);

        $this->as('admin');
        Livewire::test(TeamWorkload::class)->assertSee('Asha')->assertSee('Not assigned to anybody');
    }

    // ------------------------------------------------------------------ catalogue

    public function test_catalogue_health_and_stale_products(): void
    {
        $this->assertSame(['total' => 0, 'complete' => 0, 'percent' => 100], DashboardData::catalogueHealth());

        $this->product('Complete', null, ['image' => 'products/a.webp', 'specs' => ['Model' => 'X'], 'overview' => '<p>Text</p>']);
        $this->product('No photo', null, ['specs' => ['Model' => 'X'], 'overview' => '<p>Text</p>']);
        $this->product('No specs', null, ['image' => 'products/b.webp', 'overview' => '<p>Text</p>']);
        $this->product('No overview', null, ['image' => 'products/c.webp', 'specs' => ['Model' => 'X']]);
        $this->product('Hidden draft', null, ['is_active' => false]);

        $this->assertSame(['total' => 4, 'complete' => 1, 'percent' => 25], DashboardData::catalogueHealth());
        $this->assertSame(0, DashboardData::stale()['count']);

        $old = Product::where('name', 'No photo')->first();
        $old->forceFill(['updated_at' => now()->subMonths(8)])->saveQuietly();
        Product::where('name', 'Hidden draft')->first()->forceFill(['updated_at' => now()->subYear()])->saveQuietly();   // drafts are ignored

        $stale = DashboardData::stale();
        $this->assertSame(1, $stale['count']);
        $this->assertSame('No photo', $stale['rows'][0]->name);

        $this->as('admin');
        Livewire::test(CatalogueHealth::class)->assertSee('of 4 live products are complete')->assertSee('25%')->assertSee('No photo');
        $this->as('hr');
        $this->assertFalse(CatalogueHealth::canView());
    }

    public function test_paired_widgets_take_the_full_row_when_their_partner_is_hidden(): void
    {
        $this->as('admin');
        $this->assertSame(['default' => 'full', 'lg' => 1], (new TopProducts)->getColumnSpan());
        $this->assertSame(['default' => 'full', 'lg' => 1], (new CatalogueHealth)->getColumnSpan());

        $this->as('editor');                       // may open products, not enquiries
        $this->assertSame('full', (new CatalogueHealth)->getColumnSpan());
    }

    // ------------------------------------------------------------------ webinars and shortcuts

    public function test_upcoming_webinars_appear_under_the_greeting(): void
    {
        $this->as('admin');
        Insight::create(['type' => 'webinar', 'title' => 'Future of HPLC', 'slug' => 'future-of-hplc', 'is_active' => true,
            'starts_at' => now('Asia/Kolkata')->addDays(3), 'event_date' => now('Asia/Kolkata')->addDays(3)->toDateString()]);
        Insight::create(['type' => 'webinar', 'title' => 'Far away', 'slug' => 'far-away', 'is_active' => true,
            'starts_at' => now('Asia/Kolkata')->addDays(30), 'event_date' => now('Asia/Kolkata')->addDays(30)->toDateString()]);
        Insight::create(['type' => 'webinar', 'title' => 'Already over', 'slug' => 'over', 'is_active' => true,
            'starts_at' => now('Asia/Kolkata')->subDays(2), 'event_date' => now('Asia/Kolkata')->subDays(2)->toDateString()]);

        $this->assertSame(['Future of HPLC'], DashboardData::upcomingWebinars()->pluck('title')->all());

        Livewire::test(DashboardHeader::class)->assertSee('Coming up in the next 7 days')->assertSee('Future of HPLC')->assertDontSee('Far away');

        $this->as('sales');       // sales do not manage insights
        Livewire::test(DashboardHeader::class)->assertDontSee('Coming up in the next 7 days');
    }

    public function test_each_person_pins_their_own_shortcuts(): void
    {
        $user = $this->as('sales');

        Livewire::test(MyShortcuts::class)->assertSee('Pin the areas you open every day');

        Livewire::test(MyShortcuts::class)
            ->callAction('editShortcuts', ['pins' => ['enquiries', 'products']])
            ;

        $this->assertSame(['enquiries', 'products'], $user->fresh()->dashboard_pins);
        Livewire::test(MyShortcuts::class)->assertSee('Enquiries')->assertSee('Products')->assertDontSee('Pin the areas you open every day');

        // somebody else is not affected
        $this->as('admin');
        Livewire::test(MyShortcuts::class)->assertSee('Pin the areas you open every day');

        // an area the role can no longer open is not shown, even if it is still pinned
        $user->forceFill(['dashboard_pins' => ['enquiries', 'backups']])->save();
        $this->actingAs($user->fresh());
        $titles = collect((new MyShortcuts)->getPinned())->pluck('title')->all();
        $this->assertSame(['Enquiries'], $titles);
    }

    // ------------------------------------------------------------------ uniformity with the rest of the site

    public function test_the_dashboard_only_uses_the_site_palette(): void
    {
        // Filament colour names (primary, info, success, danger, gray) are mapped to the site palette in AdminPanelProvider.
        // Tailwind's own colour names (red, green, blue, amber ...) would be colours that do not exist anywhere else on the site.
        $alien = '/(?:bg|text|border|ring|from|to|via|fill|stroke|divide|outline|shadow|accent|decoration)-(?:red|orange|amber|yellow|lime|green|emerald|teal|cyan|sky|blue|indigo|violet|purple|fuchsia|pink|rose|stone|zinc|neutral|slate)-\d{2,3}/';
        $palette = ['#0B2545', '#0077B6', '#00B4D8', '#007C98', '#6C757D', '#F4F9FB', '#2A9D8F', '#E76F51', '#E5E7EB', '#FFFFFF'];

        $files = array_merge(
            glob(resource_path('views/filament/widgets/*.blade.php')),
            glob(app_path('Filament/Widgets/*.php')),
            [app_path('Filament/Pages/Dashboard.php')],
        );

        foreach ($files as $file) {
            $code = file_get_contents($file);

            $this->assertDoesNotMatchRegularExpression($alien, $code, basename($file) . ' uses a colour that is not in the site palette');

            preg_match_all('/#[0-9a-fA-F]{6}/', $code, $hex);
            foreach ($hex[0] as $colour) {
                $this->assertContains(strtoupper($colour), $palette, basename($file) . " uses {$colour}, which is not a site colour");
            }
        }
    }
}
