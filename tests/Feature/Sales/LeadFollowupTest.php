<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesEnquiry;
use App\Sales\Support\RepEnquiryService;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/** Lead follow-up (app v1.15): status, remark and next reminder on an enquiry: the sync, and the rep desk's service. */
class LeadFollowupTest extends SalesTestCase
{
    private function payload(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'client_name' => 'JK Cement', 'status' => 'qualification',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => 1500000]],
            'opened_at' => $this->moment('-2 hours'), 'next_followup_at' => $this->moment('+1 day'), 'closed_at' => null,
            'timeline' => [['at' => $this->moment('-2 hours'), 'text' => 'Opened', 'status' => 'qualification']],
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function pullOne(array $headers, string $uuid): ?array
    {
        $res = $this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk();

        return collect($res->json('enquiries'))->firstWhere('uuid', $uuid);
    }

    // ---- sync -------------------------------------------------------------------------------------------------------------------------

    public function test_the_three_lead_fields_are_stored_and_come_back(): void
    {
        $h = $this->headers($this->rep());
        $at = $this->moment('-1 hour');
        $e = $this->payload(['lead_status' => 'price_sent', 'lead_remark' => 'Will confirm on Friday', 'last_followup_at' => $at]);

        $this->assertSame('accepted', $this->answer($this->push($h, ['enquiries' => [$e]])->assertOk(), 'enquiries', $e['uuid'])['status']);

        $row = SalesEnquiry::where('uuid', $e['uuid'])->first();
        $this->assertSame('price_sent', $row->lead_status);
        $this->assertSame('Will confirm on Friday', $row->lead_remark);
        $this->assertNotNull($row->last_followup_at);

        $back = $this->pullOne($h, $e['uuid']);
        $this->assertSame(['price_sent', 'Will confirm on Friday'], [$back['lead_status'], $back['lead_remark']]);
        $this->assertNotNull($back['last_followup_at']);
    }

    public function test_an_older_phone_that_sends_none_of_them_never_wipes_what_is_stored(): void
    {
        $h = $this->headers($this->rep());
        $e = $this->payload(['lead_status' => 'waiting', 'lead_remark' => 'Call after Diwali', 'last_followup_at' => $this->moment('-1 hour'), 'updated_at' => $this->moment('-10 minutes')]);
        $this->push($h, ['enquiries' => [$e]])->assertOk();

        // a v1.15 phone edits the products and sends no lead key at all
        $older = $this->payload(['uuid' => $e['uuid'], 'client_name' => 'JK Cement Ltd', 'updated_at' => $this->moment()]);
        $this->assertSame('accepted', $this->answer($this->push($h, ['enquiries' => [$older]])->assertOk(), 'enquiries', $e['uuid'])['status']);

        $row = SalesEnquiry::where('uuid', $e['uuid'])->first();
        $this->assertSame('JK Cement Ltd', $row->client_name);
        $this->assertSame(['waiting', 'Call after Diwali'], [$row->lead_status, $row->lead_remark]);
        $this->assertNotNull($row->last_followup_at);
    }

    public function test_a_cleared_value_is_cleared_and_a_bad_one_is_refused(): void
    {
        $h = $this->headers($this->rep());
        $e = $this->payload(['lead_status' => 'interested', 'lead_remark' => 'ok']);
        $this->push($h, ['enquiries' => [$e]])->assertOk();

        $cleared = $this->payload(['uuid' => $e['uuid'], 'lead_status' => null, 'lead_remark' => null, 'last_followup_at' => null, 'updated_at' => $this->moment('+1 second')]);
        $this->answer($this->push($h, ['enquiries' => [$cleared]])->assertOk(), 'enquiries', $e['uuid']);
        $this->assertNull(SalesEnquiry::where('uuid', $e['uuid'])->first()->lead_status);

        foreach ([['lead_status' => 'nonsense'], ['lead_remark' => str_repeat('x', 301)]] as $bad) {
            $b = $this->payload($bad);
            $this->assertSame('rejected', $this->answer($this->push($h, ['enquiries' => [$b]])->assertOk(), 'enquiries', $b['uuid'])['status']);
            $this->assertNull(SalesEnquiry::where('uuid', $b['uuid'])->first());
        }
    }

    // ---- the rep desk's service -------------------------------------------------------------------------------------------------------

    private function enquiryOf(int $userId, array $over = []): SalesEnquiry
    {
        return SalesEnquiry::create(array_merge([
            'uuid' => (string) Str::uuid(), 'user_id' => $userId, 'client_name' => 'JK Cement', 'status' => 'qualification',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => 1500000]],
            'opened_at' => now()->subDays(2), 'next_followup_at' => now()->addDay(), 'client_updated_at' => now()->subDay(),
            'timeline' => [['at' => now()->subDays(2)->utc()->format('Y-m-d\TH:i:s\Z'), 'text' => 'Enquiry opened', 'status' => 'qualification']],
        ], $over));
    }

    public function test_a_follow_up_sets_status_remark_reminder_timeline_and_a_newer_version(): void
    {
        $rep = $this->rep();
        $e = $this->enquiryOf($rep->user_id);
        $before = $e->client_updated_at;
        $at = CarbonImmutable::now()->addDays(3)->setTime(10, 0);

        $row = app(RepEnquiryService::class)->leadFollowup($e, $rep->user, 'price_sent', '  Wants a better price  ', $at);

        $this->assertSame(['price_sent', 'Wants a better price'], [$row->lead_status, $row->lead_remark]);
        $this->assertSame($at->getTimestamp(), $row->next_followup_at->getTimestamp());
        $this->assertNotNull($row->last_followup_at);
        $this->assertTrue($row->client_updated_at->gt($before));
        $this->assertSame('qualification', $row->status);
        $timeline = $row->timeline;
        $line = $timeline[array_key_last($timeline)];
        $this->assertStringContainsString('Follow-up: Price sent', $line['text']);
        $this->assertStringContainsString('Wants a better price', $line['text']);
    }

    public function test_deal_done_and_deal_lost_finish_the_enquiry(): void
    {
        $rep = $this->rep();

        $won = app(RepEnquiryService::class)->leadFollowup($this->enquiryOf($rep->user_id), $rep->user, 'deal_done', 'PO received', null);
        $this->assertSame('won', $won->status);
        $this->assertNull($won->next_followup_at);
        $this->assertNotNull($won->closed_at);

        $lost = app(RepEnquiryService::class)->leadFollowup($this->enquiryOf($rep->user_id), $rep->user, 'deal_lost', null, now()->addDay());
        $this->assertSame('lost', $lost->status);
        $this->assertNull($lost->next_followup_at);
    }

    public function test_an_open_status_needs_a_reminder_and_the_rules_hold(): void
    {
        $rep = $this->rep();
        $svc = app(RepEnquiryService::class);
        $e = $this->enquiryOf($rep->user_id);

        foreach ([
            ['price_sent', 'x', null],                                 // no reminder
            ['unknown', 'x', now()->addDay()],                          // unknown status
            ['price_sent', str_repeat('y', 301), now()->addDay()],      // remark too long
            ['price_sent', 'x', now()->addYears(3)],                    // more than two years ahead
        ] as [$status, $remark, $at]) {
            try {
                $svc->leadFollowup($e, $rep->user, $status, $remark, $at);
                $this->fail("accepted: $status");
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
        $this->assertNull($e->fresh()->lead_status);

        // an explicit "no reminder" is allowed
        $this->assertNull($svc->leadFollowup($e, $rep->user, 'waiting', 'later', null, noReminder: true)->next_followup_at);
    }

    public function test_a_finished_enquiry_takes_no_more_follow_ups_and_nobody_else_can(): void
    {
        $rep = $this->rep('REP001', '9000000001');
        $other = $this->rep('REP002', '9000000002');
        $svc = app(RepEnquiryService::class);

        $done = $this->enquiryOf($rep->user_id, ['status' => 'won', 'closed_at' => now(), 'next_followup_at' => null]);
        try {
            $svc->leadFollowup($done, $rep->user, 'interested', 'x', now()->addDay());
            $this->fail('a finished enquiry took a follow-up');
        } catch (ValidationException) {
            $this->assertSame('won', $done->fresh()->status);
        }

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $svc->leadFollowup($this->enquiryOf($rep->user_id), $other->user, 'interested', 'x', now()->addDay());
    }

    public function test_the_suggested_reminders(): void
    {
        $from = CarbonImmutable::parse('2026-11-05 14:00', config('sales.timezone'));

        $this->assertSame('2026-11-08 10:00', SalesEnquiry::suggestedReminder('price_sent', $from)->format('Y-m-d H:i'));
        $this->assertSame('2026-11-06 10:00', SalesEnquiry::suggestedReminder('better_option', $from)->format('Y-m-d H:i'));
        $this->assertSame('2026-11-05 17:00', SalesEnquiry::suggestedReminder('call_not_picking', $from)->format('Y-m-d H:i'));
        $this->assertNull(SalesEnquiry::suggestedReminder('deal_done', $from));
        $this->assertNull(SalesEnquiry::suggestedReminder('deal_lost', $from));
    }
}
