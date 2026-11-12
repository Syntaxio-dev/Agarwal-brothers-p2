<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesEnquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** The six enquiry stages, and the eight old ones: read from phones and rows that still have them, and sent to phones that only know them. */
class EnquiryStagesTest extends SalesTestCase
{
    private function payload(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'client_name' => 'Sharma Scientific', 'status' => 'qualification',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => null]],
            'opened_at' => $this->moment('-2 hours'), 'next_followup_at' => null, 'closed_at' => null,
            'timeline' => [['at' => $this->moment('-2 hours'), 'text' => 'Opened', 'status' => 'qualification']],
            'updated_at' => $this->moment(),
        ], $over);
    }

    public function test_the_stages_and_their_words(): void
    {
        $this->assertSame(['Qualification', 'Need Analysis', 'Proposal / Price Quote', 'Negotiation / Review', 'Closed Won', 'Closed Lost'], array_values(SalesEnquiry::STATUSES));
        $this->assertSame(['qualification', 'need_analysis', 'proposal', 'negotiation'], SalesEnquiry::OPEN);
        $this->assertSame(['won', 'lost'], SalesEnquiry::CLOSED);
    }

    public function test_a_phone_that_still_sends_the_old_names_is_understood(): void
    {
        $h = $this->headers($this->rep());
        $e = $this->payload(['status' => 'price_sent', 'timeline' => [
            ['at' => $this->moment('-2 hours'), 'text' => 'Opened', 'status' => 'new'],
            ['at' => $this->moment('-1 hour'), 'text' => 'Fixed a visit', 'status' => 'meeting_planned'],
        ]]);

        $this->answer($this->push($h, ['enquiries' => [$e]])->assertOk(), 'enquiries', $e['uuid']);

        $row = SalesEnquiry::where('uuid', $e['uuid'])->first();
        $this->assertSame('proposal', $row->status);
        $this->assertSame(['qualification', 'need_analysis'], array_column($row->timeline, 'status'));
    }

    public function test_a_phone_older_than_v1_15_is_sent_the_old_names_and_a_new_one_the_new(): void
    {
        $h = $this->headers($this->rep());
        $a = $this->payload(['status' => 'proposal', 'timeline' => [['at' => $this->moment('-2 hours'), 'text' => 'Opened', 'status' => 'qualification']]]);
        $b = $this->payload(['status' => 'need_analysis']);
        $c = $this->payload(['status' => 'negotiation']);
        $this->push($h, ['enquiries' => [$a, $b, $c]])->assertOk();

        $statusOf = fn (array $headers) => collect($this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json('enquiries'))->pluck('status', 'uuid')->all();

        $old = $statusOf($h + ['X-App-Version' => '1.14']);
        $this->assertSame(['price_to_send', 'meeting_planned', 'negotiation'], [$old[$a['uuid']], $old[$b['uuid']], $old[$c['uuid']]], 'the closest old name; negotiation never changed');

        $timeline = collect($this->getJson('/api/sales/v1/sync/pull', $h + ['X-App-Version' => '1.14'])->json('enquiries'))->firstWhere('uuid', $a['uuid'])['timeline'];
        $this->assertSame('new', $timeline[0]['status']);

        $new = $statusOf($h + ['X-App-Version' => '1.15']);
        $this->assertSame(['proposal', 'need_analysis', 'negotiation'], [$new[$a['uuid']], $new[$b['uuid']], $new[$c['uuid']]]);
    }

    public function test_an_unknown_stage_is_still_refused(): void
    {
        $h = $this->headers($this->rep());
        $e = $this->payload(['status' => 'maybe']);

        $answer = $this->answer($this->push($h, ['enquiries' => [$e]])->assertOk(), 'enquiries', $e['uuid']);

        $this->assertSame('rejected', $answer['status']);
        $this->assertSame(0, SalesEnquiry::count());
    }

    public function test_the_migration_renames_stored_rows_and_their_timelines(): void
    {
        $user = $this->rep()->user_id;
        $row = fn (string $status, array $timeline) => DB::table('sales_enquiries')->insert([
            'uuid' => (string) Str::uuid(), 'user_id' => $user, 'client_name' => 'JK ' . $status, 'status' => $status, 'items' => json_encode([['product' => 'Oven', 'quantity' => 1]]),
            'opened_at' => now(), 'timeline' => json_encode($timeline), 'client_updated_at' => now()->utc()->format('Y-m-d H:i:s.v'), 'created_at' => now(), 'updated_at' => now(),
        ]);
        foreach (['new', 'callback', 'meeting_planned', 'price_to_send', 'price_sent', 'negotiation', 'won', 'lost'] as $s) {
            $row($s, [['at' => now()->toIso8601String(), 'text' => 'x', 'status' => $s]]);
        }
        $before = DB::table('sales_enquiries')->where('status', 'new')->value('updated_at');

        (require database_path('migrations/2026_11_04_100000_enquiry_six_stages.php'))->up();

        $this->assertEqualsCanonicalizing(
            ['qualification', 'qualification', 'need_analysis', 'proposal', 'proposal', 'negotiation', 'won', 'lost'],
            DB::table('sales_enquiries')->pluck('status')->all()
        );
        $lines = DB::table('sales_enquiries')->pluck('timeline')->map(fn ($t) => json_decode($t, true)[0]['status'])->all();
        $this->assertEqualsCanonicalizing(['qualification', 'qualification', 'need_analysis', 'proposal', 'proposal', 'negotiation', 'won', 'lost'], $lines);
        $this->assertSame($before, DB::table('sales_enquiries')->where('status', 'qualification')->orderBy('id')->value('updated_at'), 'only a relabel: nothing counts as changed');
    }
}
