<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesEnquiries\Pages\ListSalesEnquiries;
use App\Sales\Filament\Resources\SalesReports\Pages\ListSalesReports;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** Enquiries (several products at once, status, follow-up, notes) and the reps' daily reports. */
class EnquiryReportTest extends SalesTestCase
{
    private function enquiry(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'client_uuid' => null,
            'client_name' => 'JK Cement',
            'contact_uuid' => null,
            'contact_name' => 'Rohit Sharma',
            'status' => 'new',
            'items' => [
                ['product' => 'Muffle Furnace', 'brands' => 'Labtech, Remi', 'quantity' => 1, 'budget_paise' => 15000000],
                ['product' => 'Hot Air Oven', 'brands' => '', 'quantity' => 2, 'budget_paise' => null],
            ],
            'opened_at' => $this->moment('-2 minutes'),
            'next_followup_at' => $this->moment('+1 day'),
            'closed_at' => null,
            'timeline' => [['at' => $this->moment('-2 hours'), 'text' => 'Asked for the price of an oven and a furnace', 'status' => 'new']],
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function report(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'report_date' => now(config('sales.timezone'))->toDateString(),
            'notes' => 'Visited JK Cement, two enquiries',
            'tomorrow_plan' => 'Send the prices',
            'issues' => '',
            'figures' => ['calls' => 7, 'calls_missed' => 1, 'talk_seconds' => 1500, 'meetings' => 2, 'enquiries_new' => 2, 'enquiries_worked' => 3, 'expenses_paise' => 85000],
            'submitted_at' => $this->moment(),
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function pull(array $headers, ?string $since = null)
    {
        return $this->getJson('/api/sales/v1/sync/pull' . ($since ? '?since=' . urlencode($since) : ''), $headers);
    }

    // ---- enquiries -----------------------------------------------------------------------------

    public function test_one_enquiry_carries_several_products_and_comes_back_whole(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->enquiry();

        $this->assertSame('accepted', $this->answer($this->push($headers, ['enquiries' => [$e]])->assertOk(), 'enquiries', $e['uuid'])['status']);

        $row = SalesEnquiry::first();
        $this->assertCount(2, $row->items);
        $this->assertSame('Muffle Furnace, Hot Air Oven x2', $row->itemsSummary());
        $this->assertSame(15000000, $row->budgetPaise());

        $back = $this->pull($headers)->json('enquiries.0');
        $this->assertSame($e['uuid'], $back['uuid']);
        $this->assertSame('new', $back['status']);
        $this->assertSame('Labtech, Remi', $back['items'][0]['brands']);
        $this->assertSame(2, $back['items'][1]['quantity']);
        $this->assertSame('Asked for the price of an oven and a furnace', $back['timeline'][0]['text']);
    }

    public function test_status_follow_up_and_notes_move_with_each_push_and_a_closed_enquiry_has_no_follow_up(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->enquiry();
        $this->push($headers, ['enquiries' => [$e]])->assertOk();

        $this->travel(2)->seconds();
        $e['status'] = 'price_sent';
        $e['next_followup_at'] = $this->moment('+3 days');
        $e['timeline'][] = ['at' => $this->moment(), 'text' => 'Mailed the quote', 'status' => 'price_sent'];
        $e['updated_at'] = $this->moment();
        $this->push($headers, ['enquiries' => [$e]])->assertOk();

        $row = SalesEnquiry::first();
        $this->assertSame('price_sent', $row->status);
        $this->assertTrue($row->isOpen());
        $this->assertCount(2, $row->timeline);
        $this->assertNotNull($row->next_followup_at);

        $this->travel(2)->seconds();
        $e['status'] = 'won';
        $e['updated_at'] = $this->moment();
        $this->push($headers, ['enquiries' => [$e]])->assertOk();

        $row->refresh();
        $this->assertFalse($row->isOpen());
        $this->assertNull($row->next_followup_at, 'nothing is left to follow up');
        $this->assertNotNull($row->closed_at);
    }

    public function test_enquiry_rules(): void
    {
        $headers = $this->headers($this->rep());

        $bad = $this->push($headers, ['enquiries' => [
            $this->enquiry(['status' => 'maybe']),
            $this->enquiry(['items' => []]),
            $this->enquiry(['items' => [['product' => 'x', 'quantity' => 1]]]),
            $this->enquiry(['items' => [['product' => 'Oven', 'quantity' => 0]]]),
            $this->enquiry(['items' => array_fill(0, 11, ['product' => 'Oven', 'quantity' => 1])]),
            $this->enquiry(['client_name' => '']),
            $this->enquiry(['timeline' => [['at' => $this->moment(), 'text' => str_repeat('x', 501)]]]),
            $this->enquiry(['timeline' => [['at' => $this->moment(), 'text' => 'ok', 'status' => 'bogus']]]),
            $this->enquiry(['opened_at' => now()->addDays(5)->toIso8601String()]),
        ]])->assertOk();

        foreach (range(0, 8) as $i) {
            $this->assertSame('rejected', $bad->json("results.enquiries.$i.status"), "enquiry $i");
        }
        $this->assertSame(0, SalesEnquiry::count());
    }

    public function test_enquiries_are_private_to_the_rep_but_the_manager_sees_all(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $e = $this->enquiry();
        $this->push($this->headers($a), ['enquiries' => [$e]])->assertOk();

        $this->assertSame([], $this->pull($this->headers($b))->json('enquiries'));
        $this->assertSame('UUID_TAKEN', $this->answer($this->push($this->headers($b), ['enquiries' => [$e]])->assertOk(), 'enquiries', $e['uuid'])['code']);

        $manager = $this->manager();
        $this->assertTrue($manager->can('view', SalesEnquiry::first()));
        $this->assertFalse($manager->can('update', SalesEnquiry::first()), 'the rep owns the follow-up');
        $this->assertFalse($b->user->can('view', SalesEnquiry::first()));
    }

    public function test_an_enquiry_can_be_deleted_by_its_rep_only(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $e = $this->enquiry();
        $this->push($a, ['enquiries' => [$e]])->assertOk();

        $this->push($b, ['deleted' => ['enquiries' => [$e['uuid']]]])->assertOk();
        $this->assertSame(1, SalesEnquiry::count(), 'somebody else cannot delete it');

        $this->push($a, ['deleted' => ['enquiries' => [$e['uuid']]]])->assertOk();
        $this->assertSame(0, SalesEnquiry::count());

        $this->travel(2)->seconds();
        $this->assertTrue($this->pull($a)->json('enquiries.0.deleted'));
    }

    public function test_the_insights_count_open_late_and_idle_enquiries_and_the_products_asked(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $late = $this->enquiry(['next_followup_at' => $this->moment('-1 day')]);
        $fine = $this->enquiry(['items' => [['product' => 'Hot Air Oven', 'quantity' => 1]]]);
        $done = $this->enquiry(['status' => 'won']);
        $this->push($headers, ['enquiries' => [$late, $fine, $done]])->assertOk();

        $s = SalesInsights::summary(30);
        $this->assertSame(2, $s['enquiries_open']);
        $this->assertSame(1, $s['enquiries_overdue']);
        $this->assertSame(1, $s['enquiries_won']);
        $this->assertSame(3, $s['enquiries_new']);
        $this->assertSame(['hot air oven' => 3, 'muffle furnace' => 2], collect(SalesInsights::topProducts(30))->mapWithKeys(fn ($p) => [mb_strtolower($p['product']) => $p['count']])->all());
        $this->assertSame(2, SalesInsights::enquiriesByStatus()['new']);
        $this->assertCount(1, SalesInsights::overdueEnquiries());

        $rep = SalesInsights::reps(30)->first();
        $this->assertSame(2, $rep['enquiries_open']);
        $this->assertSame(1, $rep['enquiries_late']);
    }

    // ---- daily reports ------------------------------------------------------------------------------

    public function test_a_report_is_stored_once_per_day_and_can_be_corrected(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $r = $this->report();

        $this->assertSame('accepted', $this->answer($this->push($headers, ['reports' => [$r]])->assertOk(), 'reports', $r['uuid'])['status']);
        $this->assertSame('duplicate', $this->answer($this->push($headers, ['reports' => [$r]])->assertOk(), 'reports', $r['uuid'])['status']);

        $row = SalesDailyReport::first();
        $this->assertSame(7, $row->figures['calls']);
        $this->assertSame('Visited JK Cement, two enquiries', $row->notes);

        $this->travel(2)->seconds();
        $r['notes'] = 'Visited JK Cement, three enquiries';
        $r['updated_at'] = $this->moment();
        $this->push($headers, ['reports' => [$r]])->assertOk();
        $this->assertSame('Visited JK Cement, three enquiries', SalesDailyReport::first()->fresh()->notes);

        // a second report for the same day under another id is refused: the first one stands
        $other = $this->push($headers, ['reports' => [$this->report(['notes' => 'again'])]])->assertOk();
        $this->assertSame('INVALID', $other->json('results.reports.0.code'));
        $this->assertSame(1, SalesDailyReport::count());

        $this->assertSame($r['uuid'], $this->pull($headers)->json('reports.0.uuid'), 'a new phone gets its reports back');
    }

    public function test_report_rules_and_privacy(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));

        $bad = $this->push($a, ['reports' => [
            $this->report(['report_date' => now()->addDays(3)->toDateString()]),
            $this->report(['report_date' => now()->subDays(60)->toDateString()]),
            $this->report(['notes' => str_repeat('x', 2001)]),
            $this->report(['figures' => ['calls' => -1]]),
            $this->report(['report_date' => 'yesterday']),
        ]])->assertOk();
        foreach (range(0, 4) as $i) {
            $this->assertSame('rejected', $bad->json("results.reports.$i.status"), "report $i");
        }

        $mine = $this->report();
        $this->push($a, ['reports' => [$mine]])->assertOk();
        $this->assertSame([], $this->pull($b)->json('reports'));
        $this->assertSame('UUID_TAKEN', $this->answer($this->push($b, ['reports' => [$mine]])->assertOk(), 'reports', $mine['uuid'])['code']);

        // a report cannot be deleted from the phone
        $this->push($a, ['deleted' => ['reports' => [$mine['uuid']]]])->assertOk();
        $this->assertSame(1, SalesDailyReport::count());
    }

    public function test_who_has_not_reported_and_what_was_worked_on(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $headers = $this->headers($a);
        $this->push($headers, ['reports' => [$this->report()], 'enquiries' => [$this->enquiry()]])->assertOk();

        $this->assertSame(1, SalesInsights::summary(7)['reports_today']);
        $this->assertSame(['Rep REP002'], SalesInsights::repsWithoutReport()->map(fn ($p) => $p->user->name)->all());

        $worked = SalesInsights::enquiriesWorked($a->user_id, now(config('sales.timezone'))->toDateString());
        $this->assertCount(1, $worked);
        $this->assertCount(0, SalesInsights::enquiriesWorked($b->user_id, now(config('sales.timezone'))->toDateString()));
    }

    // ---- the admin screens -----------------------------------------------------------------------------

    public function test_the_manager_reads_enquiries_and_reports_in_the_admin(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $e = $this->enquiry();
        $r = $this->report();
        $this->push($headers, ['enquiries' => [$e], 'reports' => [$r]])->assertOk();

        $this->actingAs($this->manager());
        $this->get('/admin/sales-enquiries')->assertOk();
        $this->get('/admin/sales-reports')->assertOk();
        $this->get('/admin/sales-management')->assertOk();

        $enquiry = SalesEnquiry::first();
        Livewire::test(ListSalesEnquiries::class)
            ->assertCanSeeTableRecords([$enquiry])
            ->assertSee('Muffle Furnace')
            ->filterTable('status', ['new'])->assertCanSeeTableRecords([$enquiry])
            ->resetTableFilters()
            ->filterTable('status', ['won'])->assertCanNotSeeTableRecords([$enquiry])
            ->resetTableFilters()
            ->mountTableAction('view', $enquiry)->assertHasNoTableActionErrors();

        $report = SalesDailyReport::first();
        Livewire::test(ListSalesReports::class)
            ->assertCanSeeTableRecords([$report])
            ->assertSee('Visited JK Cement')
            ->mountTableAction('view', $report)->assertHasNoTableActionErrors();
    }

    public function test_a_report_not_sent_yet_is_not_listed_and_others_are_kept_out(): void
    {
        $profile = $this->rep();
        $this->push($this->headers($profile), ['reports' => [$this->report(['submitted_at' => null])]])->assertOk();
        $draft = SalesDailyReport::first();

        $this->actingAs($this->manager());
        Livewire::test(ListSalesReports::class)->assertCanNotSeeTableRecords([$draft]);

        $this->actingAs(\App\Models\User::factory()->create(['role' => 'editor', 'is_active' => true]));
        $this->get('/admin/sales-enquiries')->assertForbidden();
        $this->get('/admin/sales-reports')->assertForbidden();
    }
}
