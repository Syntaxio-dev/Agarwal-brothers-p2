<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\AccountInfo;
use App\Filament\Widgets\AdminFeaturesBox;
use App\Filament\Widgets\BusinessPulse;
use App\Filament\Widgets\CatalogueHealth;
use App\Filament\Widgets\EnquirySources;
use App\Filament\Widgets\MyShortcuts;
use App\Filament\Widgets\ResponseWatch;
use App\Filament\Widgets\TeamWorkload;
use App\Filament\Widgets\TopProducts;
use App\Filament\Widgets\DashboardHeader;
use App\Filament\Widgets\EnquiryTrend;
use App\Filament\Widgets\LatestActivity;
use App\Filament\Widgets\OverviewStats;
use App\Filament\Widgets\QuickUpdates;
use App\Filament\Widgets\TeamActivity;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    private function as(string $role): User
    {
        $user = User::factory()->create(['role' => $role, 'is_active' => true, 'name' => 'Ayush Sharma']);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_widgets_come_in_the_planned_order(): void
    {
        $sorts = fn (string $class) => (new \ReflectionClass($class))->getProperty('sort')->getValue();

        $order = collect([DashboardHeader::class, MyShortcuts::class, BusinessPulse::class, OverviewStats::class, QuickUpdates::class, ResponseWatch::class, TeamWorkload::class, EnquiryTrend::class, EnquirySources::class, TopProducts::class, CatalogueHealth::class, LatestActivity::class, TeamActivity::class, AdminFeaturesBox::class, AccountInfo::class])
            ->map($sorts)->all();

        $this->assertSame(range(0, 14), $order);
    }

    public function test_the_grid_is_one_column_on_phones_and_two_on_large_screens(): void
    {
        $this->as('admin');
        $this->assertSame(['default' => 1, 'lg' => 2], (new Dashboard)->getColumns());
        $this->get('/admin')->assertOk();
    }

    public function test_the_header_greets_by_first_name_and_counts_what_needs_attention(): void
    {
        $this->as('admin');
        Enquiry::create(['name' => 'Ravi', 'email' => 'r@example.com', 'status' => 'new']);

        Livewire::test(DashboardHeader::class)
            ->assertSee('Ayush')
            ->assertDontSee('Sharma')
            ->assertSeeText('need attention')
            ->assertSee('Add product')
            ->assertSee('Import products')
            ->assertSee('New blog / news');

        $header = new DashboardHeader;
        $this->assertMatchesRegularExpression('/^Good (morning|afternoon|evening), Ayush$/', $header->getGreeting());
        $this->assertGreaterThanOrEqual(1, $header->getAttentionCount());
    }

    public function test_the_header_buttons_follow_the_role(): void
    {
        $this->as('sales');
        $labels = collect((new DashboardHeader)->getPrimaryLinks())->pluck('label')->all();

        $this->assertContains('Add product', $labels);          // sales may create products
        $this->assertNotContains('New blog / news', $labels);   // but not blogs

        $this->as('hr');
        $this->assertSame([], (new DashboardHeader)->getPrimaryLinks());
    }

    public function test_needs_attention_dims_empty_cards_and_tints_the_urgent_ones(): void
    {
        $this->as('admin');
        Enquiry::create(['name' => 'Ravi', 'email' => 'r@example.com', 'status' => 'new']);

        $html = Livewire::test(QuickUpdates::class)->html();

        $this->assertStringContainsString('border-danger-200 bg-danger-50', $html);   // customers waiting
        $this->assertStringContainsString('bg-danger-600 text-white', $html);          // solid count chip
        $this->assertStringContainsString('opacity-55', $html);                        // cards with nothing to do
        $this->assertStringContainsString('All done ✓', $html);
    }

    public function test_the_stat_cards_have_a_coloured_left_edge(): void
    {
        $this->as('admin');
        $html = Livewire::test(OverviewStats::class)->html();

        foreach (['!border-s-success-500', '!border-s-info-500', '!border-s-primary-500', '!border-s-gray-400'] as $edge) {
            $this->assertStringContainsString($edge, $html);
        }

        Enquiry::create(['name' => 'Ravi', 'email' => 'r@example.com', 'status' => 'new']);
        $this->assertStringContainsString('!border-s-danger-500', Livewire::test(OverviewStats::class)->html());
    }

    public function test_the_trend_chart_counts_enquiries_per_day_and_is_only_for_enquiry_staff(): void
    {
        $this->as('admin');
        Enquiry::create(['name' => 'A', 'email' => 'a@example.com']);
        Enquiry::create(['name' => 'B', 'email' => 'b@example.com']);
        $old = Enquiry::create(['name' => 'Old', 'email' => 'o@example.com']);
        $old->forceFill(['created_at' => now()->subDays(40)])->saveQuietly();

        $data = (new \ReflectionMethod(EnquiryTrend::class, 'getData'))->invoke(new EnquiryTrend);

        $this->assertCount(30, $data['labels']);      // the default range is 30 days
        $this->assertSame(2, array_sum($data['datasets'][0]['data']));      // the 40-day-old one is outside the window
        $this->assertTrue(EnquiryTrend::canView());

        $this->as('hr');
        $this->assertFalse(EnquiryTrend::canView());
    }

    public function test_recent_activity_has_a_full_row_of_its_own(): void
    {
        $this->as('admin');
        $this->assertSame('full', (new LatestActivity)->getColumnSpan());

        $this->as('hr');
        $this->assertSame('full', (new LatestActivity)->getColumnSpan());
    }
}
