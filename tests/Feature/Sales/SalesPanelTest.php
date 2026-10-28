<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Pages\SalesManagement;
use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesMeeting;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** The redesigned "Sales management" page: the figures behind it, and what a manager can do on it. */
class SalesPanelTest extends SalesTestCase
{
    private function enquiryFor(int $userId, array $over = []): SalesEnquiry
    {
        return SalesEnquiry::create(array_merge([
            'uuid' => (string) Str::uuid(), 'user_id' => $userId, 'client_name' => 'JK Cement', 'status' => 'new',
            'items' => [['product' => 'Muffle Furnace', 'quantity' => 1]], 'opened_at' => now()->subDay(), 'client_updated_at' => now(),
        ], $over));
    }

    private function reportFor(int $userId, array $over = []): SalesDailyReport
    {
        return SalesDailyReport::create(array_merge([
            'uuid' => (string) Str::uuid(), 'user_id' => $userId, 'report_date' => now(config('sales.timezone'))->toDateString(),
            'notes' => 'Visited two clients and sent one price.', 'figures' => [], 'submitted_at' => now(), 'client_updated_at' => now(),
        ], $over));
    }

    public function test_the_reps_table_counts_orders_won_and_who_has_reported(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Ravi Sharma');
        $b = $this->rep('REP002', '9000000002', name: 'Anita Verma');
        $this->enquiryFor($a->user_id, ['status' => 'won', 'closed_at' => now()->subHour()]);
        $this->enquiryFor($a->user_id, ['status' => 'won', 'closed_at' => now()->subDays(60)]);   // outside a 30-day window
        $this->enquiryFor($a->user_id, ['status' => 'lost', 'closed_at' => now()]);
        $this->reportFor($a->user_id);

        $reps = SalesInsights::reps(30)->keyBy('code');

        $this->assertSame(1, $reps['REP001']['won'], 'only an order closed inside the window counts');
        $this->assertSame(0, $reps['REP002']['won']);
        $this->assertTrue($reps['REP001']['report_today']);
        $this->assertFalse($reps['REP002']['report_today']);
        $this->assertSame($a->user_id, $reps['REP001']['user_id']);
        $this->assertSame(2, SalesInsights::reps(90)->keyBy('code')['REP001']['won'] ?? 0, 'a longer window sees the older order too');
    }

    public function test_the_selected_rep_card_has_the_phone_the_report_and_the_most_urgent_enquiries(): void
    {
        $profile = $this->rep('REP001', '9000000001', name: 'Ravi Sharma');
        $profile->forceFill(['app_version' => '1.4', 'device_model' => 'Pixel 7', 'last_sync_at' => now()->subMinutes(12)])->save();
        $this->reportFor($profile->user_id, ['notes' => 'Two enquiries, one price sent']);

        $late = $this->enquiryFor($profile->user_id, ['client_name' => 'Late Co', 'next_followup_at' => now()->subDays(2)]);
        $soon = $this->enquiryFor($profile->user_id, ['client_name' => 'Soon Co', 'next_followup_at' => now()->addDay()]);
        $none = $this->enquiryFor($profile->user_id, ['client_name' => 'No Date Co']);
        $this->enquiryFor($profile->user_id, ['client_name' => 'Fourth Co', 'next_followup_at' => now()->addDays(9)]);
        $this->enquiryFor($profile->user_id, ['client_name' => 'Closed Co', 'status' => 'won', 'closed_at' => now()]);   // not open: never listed

        $detail = SalesInsights::repDetail($profile->id, 30);

        $this->assertSame('Ravi Sharma', $detail['rep']['name']);
        $this->assertSame('Pixel 7', $detail['device_model']);
        $this->assertSame('1.4', $detail['app_version']);
        $this->assertSame('+919000000001', $detail['phone']);
        $this->assertFalse($detail['stale']);
        $this->assertTrue($detail['report']['submitted']);
        $this->assertSame('Two enquiries, one price sent', $detail['report']['notes']);
        $this->assertSame(['Late Co', 'Soon Co', 'Fourth Co'], $detail['enquiries']->pluck('client_name')->all(), 'the three most urgent: overdue first, undated last');
        $this->assertTrue($detail['enquiries'][0]->isOverdue());
        $this->assertNotContains('Closed Co', $detail['enquiries']->pluck('client_name')->all());
        $this->assertTrue($none->exists && $soon->exists && $late->exists);
    }

    public function test_the_card_is_empty_for_a_rep_who_is_not_active_or_not_there(): void
    {
        $profile = $this->rep();
        $this->assertNull(SalesInsights::repDetail(999999, 30));

        \App\Sales\Support\RepAccount::setActive($profile, false);
        $this->assertNull(SalesInsights::repDetail($profile->id, 30));
    }

    public function test_a_report_that_is_only_a_draft_does_not_count_as_submitted(): void
    {
        $profile = $this->rep();
        $this->reportFor($profile->user_id, ['submitted_at' => null, 'notes' => 'half written']);

        $detail = SalesInsights::repDetail($profile->id, 30);

        $this->assertFalse($detail['report']['submitted']);
        $this->assertNull($detail['report']['notes'], 'a draft is not shown to the manager');
        $this->assertFalse(SalesInsights::reps(30)->first()['report_today']);
    }

    public function test_not_reached_rows_know_how_long_ago_the_last_contact_was(): void
    {
        $profile = $this->rep();
        $old = now()->subDays(80);

        $called = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'name' => 'Called Co', 'client_updated_at' => now()]);
        $met = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'name' => 'Met Co', 'client_updated_at' => now()]);
        $never = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'name' => 'Never Co', 'client_updated_at' => now()]);
        $fresh = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'name' => 'Fresh Co', 'client_updated_at' => now()]);
        $recent = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'name' => 'Recent Co', 'client_updated_at' => now()]);
        DB::table('sales_clients')->whereIn('id', [$called->id, $met->id, $never->id, $recent->id])->update(['created_at' => $old]);

        SalesCall::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'direction' => 'outgoing', 'number' => '+919811100001', 'client_uuid' => $called->uuid, 'started_at' => now()->subDays(40), 'duration_seconds' => 10, 'client_updated_at' => now()]);
        SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'client_uuid' => $met->uuid, 'client_name' => 'Met Co', 'meeting_at' => now()->subDays(35), 'client_updated_at' => now()]);
        SalesCall::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'direction' => 'outgoing', 'number' => '+919811100002', 'client_uuid' => $recent->uuid, 'started_at' => now()->subDays(3), 'duration_seconds' => 10, 'client_updated_at' => now()]);

        $rows = SalesInsights::neglectedClients(30, 10)->keyBy('name');

        $this->assertSame(['Called Co', 'Met Co', 'Never Co'], $rows->keys()->sort()->values()->all(), 'a recent call, and a client added this week, are not neglected');
        $this->assertEqualsWithDelta(40, $rows['Called Co']->days_since_contact, 1);
        $this->assertEqualsWithDelta(35, $rows['Met Co']->days_since_contact, 1);
        $this->assertNull($rows['Never Co']->days_since_contact, 'never reached at all');
        $this->assertSame(3, SalesInsights::neglectedTotal());
        $this->assertCount(2, SalesInsights::neglectedClients(30, 2), 'the page shows a few, the tab counts them all');
        $this->assertTrue($fresh->exists);
    }

    public function test_a_manager_picks_a_rep_and_a_tab_on_the_page(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Ravi Sharma');
        $b = $this->rep('REP002', '9000000002', name: 'Anita Verma');
        $this->storedExpense($a, ['amount_paise' => 900000]);
        $this->reportFor($b->user_id, ['notes' => 'Anita wrote this report']);
        SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'client_name' => 'Acme Labs', 'meeting_at' => now(), 'next_followup_at' => now()->addDay(), 'client_updated_at' => now()]);

        $this->actingAs($this->manager());

        $page = Livewire::test(SalesManagement::class)
            ->assertSee('Sales management')
            ->assertSee('Ravi Sharma')->assertSee('Anita Verma')
            ->assertSet('tab', 'approvals')
            ->assertSee('Report in')->assertSee('No report');

        $page->call('selectRep', $b->id)->assertSet('selectedRep', $b->id);
        $this->assertStringContainsString('Anita wrote this report', $page->instance()->getMountedAction()->getModalContent()->render(), 'the rep detail opens in a slide-over');

        $page->call('setTab', 'followups')->assertSet('tab', 'followups')->assertSee('Acme Labs');
        $page->call('setTab', 'nonsense')->assertSet('tab', 'approvals');

        $page->call('selectRep', 987654)->assertSee('Ravi Sharma');   // a rep that does not exist: the page still draws, on the first row
    }

    public function test_the_export_needs_the_right_to_see_expenses_and_gives_a_spreadsheet(): void
    {
        $profile = $this->rep();
        $this->storedExpense($profile, ['amount_paise' => 12300]);

        $this->actingAs($this->manager());
        Livewire::test(SalesManagement::class)->call('exportReport')->assertFileDownloaded();

        // somebody who may open the page but not the expenses gets nothing (a role that has the first key only)
        config(['roles.sales_dashboard_only' => ['label' => 'Dashboard only', 'description' => 'x', 'access' => ['sales-dashboard']]]);
        $viewer = \App\Models\User::factory()->create(['role' => 'sales_dashboard_only', 'is_active' => true]);
        $this->actingAs($viewer);
        Livewire::test(SalesManagement::class)->assertDontSee('Export report')->call('exportReport')->assertForbidden();
    }

    public function test_the_page_is_escaped_and_has_the_accessible_controls(): void
    {
        // the page is one view with five parts
        $source = file_get_contents(resource_path('views/filament/pages/sales-management.blade.php'));
        foreach (glob(resource_path('views/filament/pages/sales-management/*.blade.php')) as $part) {
            $source .= file_get_contents($part);
        }

        $this->assertStringNotContainsString('{!!', $source, 'every value goes through {{ }}');
        $this->assertStringContainsString('role="img"', $source);
        $this->assertStringContainsString('aria-pressed', $source);
        $this->assertStringContainsString('aria-selected', $source);
        $this->assertStringContainsString('wire:keydown.enter', $source);

        // a name with markup is shown as text
        $profile = $this->rep('REP001', '9000000001', name: '<b>Bold</b> Rep');
        $this->actingAs($this->manager());
        Livewire::test(SalesManagement::class)->assertDontSeeHtml('<b>Bold</b>')->assertSee('<b>Bold</b> Rep');
        $this->assertNotNull($profile);
    }
}
