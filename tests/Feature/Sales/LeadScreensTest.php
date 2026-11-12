<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Filament\Resources\SalesLeads\Pages\ListSalesLeads;
use App\Sales\Filament\Resources\SalesLeads\SalesLeadResource;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Rep\Resources\MyLeads\Pages\ListMyLeads;
use App\Sales\Support\SalesInsights;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** The Leads screens: the manager sees every rep's leads, a rep only his own; the four views (Today / Last 7 days / Last month / All). */
class LeadScreensTest extends SalesTestCase
{
    private function lead(int $userId, array $over = []): SalesEnquiry
    {
        return SalesEnquiry::create(array_merge([
            'uuid' => (string) Str::uuid(), 'user_id' => $userId, 'client_name' => 'Client ' . Str::random(4), 'status' => 'qualification',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => 1500000]],
            'opened_at' => now()->subDays(2), 'next_followup_at' => now()->addDays(3), 'client_updated_at' => now()->subHour(),
            'timeline' => [['at' => now()->subDays(2)->utc()->format('Y-m-d\TH:i:s\Z'), 'text' => 'Enquiry opened', 'status' => 'qualification']],
        ], $over));
    }

    public function test_the_four_views_follow_one_definition(): void
    {
        $rep = $this->rep();
        $late = $this->lead($rep->user_id, ['next_followup_at' => now()->subHours(2)]);
        $planned = $this->lead($rep->user_id, ['next_followup_at' => now()->addDays(5)]);
        $idle = $this->lead($rep->user_id, ['next_followup_at' => null, 'client_updated_at' => now()->subDays(5), 'opened_at' => now()->subDays(6)]);
        $oldWon = $this->lead($rep->user_id, ['status' => 'won', 'next_followup_at' => null, 'closed_at' => now()->subDays(40), 'opened_at' => now()->subDays(60)]);
        $month = $this->lead($rep->user_id, ['opened_at' => now()->subDays(20), 'next_followup_at' => now()->addDays(9)]);

        $ids = fn (string $range) => SalesInsights::leadQuery(['range' => $range])->pluck('id')->sort()->values()->all();

        $this->assertEquals(collect([$late, $idle])->pluck('id')->sort()->values()->all(), $ids('today'));
        $this->assertEquals(collect([$late, $planned, $idle])->pluck('id')->sort()->values()->all(), $ids('week'));
        $this->assertEquals(collect([$late, $planned, $idle, $month])->pluck('id')->sort()->values()->all(), $ids('month'));
        $this->assertCount(5, $ids('all'));
        $this->assertSame(['today' => 2, 'week' => 3, 'month' => 4, 'all' => 5], SalesInsights::leadCounts());
        $this->assertNotContains($oldWon->id, $ids('today'));
    }

    public function test_a_status_filter_treats_an_old_enquiry_as_a_new_lead(): void
    {
        $rep = $this->rep();
        $old = $this->lead($rep->user_id);                                   // from before v1.15: lead_status is null
        $priced = $this->lead($rep->user_id, ['lead_status' => 'price_sent']);

        $this->assertEquals([$old->id], SalesInsights::leadQuery(['status' => 'new'])->pluck('id')->all());
        $this->assertEquals([$priced->id], SalesInsights::leadQuery(['status' => 'price_sent'])->pluck('id')->all());
        $this->assertSame('New lead', $old->leadStatusLabel());
    }

    public function test_the_manager_sees_every_reps_leads_in_the_four_views(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Asha');
        $b = $this->rep('REP002', '9000000002', name: 'Bharat');
        $la = $this->lead($a->user_id, ['next_followup_at' => now()->subHour(), 'lead_status' => 'call_not_picking', 'lead_remark' => 'Phone off']);
        $lb = $this->lead($b->user_id);
        $this->actingAs($this->manager());
        Filament::setCurrentPanel('sales');

        $page = Livewire::test(ListSalesLeads::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$la, $lb])                                    // default view: All
            ->assertSee('Asha')->assertSee('Bharat')->assertSee('Phone off');

        $page->set('activeTab', 'today')->assertCanSeeTableRecords([$la])->assertCanNotSeeTableRecords([$lb]);
        $page->set('activeTab', 'week')->assertCanSeeTableRecords([$la, $lb]);
        $page->set('activeTab', 'all')->filterTable('user_id', $b->user_id)->assertCanSeeTableRecords([$lb])->assertCanNotSeeTableRecords([$la]);
    }

    public function test_only_a_manager_with_the_right_opens_the_leads_screen(): void
    {
        $this->actingAs($this->manager());
        $this->assertTrue(SalesLeadResource::canViewAny());

        $this->actingAs(User::factory()->create(['role' => 'editor', 'is_active' => true]));
        $this->assertFalse(SalesLeadResource::canViewAny());
    }

    public function test_a_rep_sees_only_his_own_leads_and_logs_a_follow_up_from_the_list(): void
    {
        $me = $this->rep('REP001', '9000000001');
        $other = $this->rep('REP002', '9000000002');
        $mine = $this->lead($me->user_id);
        $theirs = $this->lead($other->user_id);
        $this->actingAs($me->user);
        Filament::setCurrentPanel('rep');

        Livewire::test(ListMyLeads::class)->assertSuccessful()
            ->assertCanSeeTableRecords([$mine])->assertCanNotSeeTableRecords([$theirs])
            ->callTableAction('leadFollowup', $mine, ['lead_status' => 'price_sent', 'lead_remark' => 'Quoted 1.5 lakh', 'next_followup_at' => now()->addDays(2)->toDateTimeString()])
            ->assertNotified();

        $mine->refresh();
        $this->assertSame(['price_sent', 'Quoted 1.5 lakh'], [$mine->lead_status, $mine->lead_remark]);
        $this->assertSame('rep', Filament::getCurrentPanel()->getId());
    }

    public function test_the_rep_list_does_not_save_without_a_reminder(): void
    {
        $me = $this->rep();
        $mine = $this->lead($me->user_id);
        $this->actingAs($me->user);
        Filament::setCurrentPanel('rep');

        Livewire::test(ListMyLeads::class)
            ->callTableAction('leadFollowup', $mine, ['lead_status' => 'price_sent', 'lead_remark' => 'x', 'next_followup_at' => null]);

        $this->assertNull($mine->fresh()->lead_status);
    }

    public function test_a_manager_does_not_enter_the_rep_leads(): void
    {
        $this->actingAs($this->manager());

        $this->get('/rep/leads')->assertForbidden();
    }
}
