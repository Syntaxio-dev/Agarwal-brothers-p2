<?php

namespace Tests\Feature;

use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\QuickUpdates;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\User;
use App\Support\DashboardCache;
use App\Support\DashboardData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    private function card(string $label): int
    {
        return collect(app(QuickUpdates::class)->getItems())->firstWhere('label', $label)['count'];
    }

    public function test_counts_are_cached_and_a_saved_record_clears_its_area_at_once(): void
    {
        $this->admin();
        Enquiry::create(['name' => 'A', 'email' => 'a@example.com']);
        $this->assertSame(1, $this->card('New product enquiries'));

        // a row written behind Eloquent's back (no saved event) is not seen: the number comes from the cache
        DB::table('enquiries')->insert(['name' => 'B', 'email' => 'b@example.com', 'status' => 'new', 'created_at' => now(), 'updated_at' => now()]);
        $this->assertSame(1, $this->card('New product enquiries'));
        $this->assertTrue(cache()->has(DashboardCache::key('enquiries', 'new-product-enquiries')));

        // a normal save clears the enquiries area, so the next read is exact again
        Enquiry::create(['name' => 'C', 'email' => 'c@example.com']);
        $this->assertSame(3, $this->card('New product enquiries'));
    }

    public function test_the_assigned_to_you_count_is_cached_per_person(): void
    {
        $me = $this->admin();
        $other = User::factory()->create(['role' => 'sales', 'is_active' => true]);
        Enquiry::create(['name' => 'A', 'email' => 'a@example.com', 'assigned_to' => $me->id]);

        $this->assertSame(1, $this->card('Enquiries assigned to you'));

        $this->actingAs($other);
        $this->assertSame(0, $this->card('Enquiries assigned to you'));
    }

    public function test_overview_stats_still_show_the_same_numbers(): void
    {
        $this->admin();
        Enquiry::create(['name' => 'A', 'email' => 'a@example.com']);

        Livewire::test(OverviewStats::class)->assertSee('New enquiries')->assertSee('Waiting for a response');
        $this->assertSame(1, (int) cache()->get(DashboardCache::key('enquiries', 'new')));
    }

    public function test_the_webinar_count_query_gives_the_same_answer_as_the_old_php_filter(): void
    {
        $this->admin();
        $now = now(Insight::TZ);
        $make = function (string $slug, $start, array $over = []) {
            Insight::create($over + ['type' => 'webinar', 'title' => $slug, 'slug' => $slug, 'is_active' => true,
                'starts_at' => $start, 'event_date' => $start->toDateString()]);
        };

        $make('in-2-days', $now->copy()->addDays(2));
        $make('in-7-days-late', $now->copy()->addDays(7)->setTime(23, 0));
        $make('in-8-days', $now->copy()->addDays(8));                                        // too far
        $make('yesterday', $now->copy()->subDay());                                          // over
        $make('an-hour-ago', $now->copy()->subHour());                                       // over (time known)
        $make('today-time-tbd', $now->copy()->startOfDay()->addMinute(), ['time_tbd' => true]);   // counts all day
        $make('switched-off', $now->copy()->addDay(), ['is_active' => false]);
        Insight::create(['type' => 'blog', 'title' => 'Blog', 'slug' => 'blog', 'is_active' => true,
            'starts_at' => $now->copy()->addDay(), 'event_date' => $now->toDateString()]);

        $expected = DashboardData::upcomingWebinars()->count();       // the old way: load all, filter in PHP

        $this->assertSame(3, $expected);
        $this->assertSame($expected, $this->card('Webinars in the next 7 days'));
    }

    public function test_the_performance_indexes_exist(): void
    {
        foreach ([
            'products' => ['is_active'], 'enquiries' => ['status', 'created_at'], 'brands' => ['is_active'],
            'contact_messages' => ['status', 'created_at'], 'job_applications' => ['status', 'created_at'], 'insights' => ['type', 'is_active'],
        ] as $table => $columns) {
            $this->assertTrue(Schema::hasIndex($table, $columns), "index on {$table}(" . implode(',', $columns) . ')');
        }
    }
}
