<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SyncPushTest extends SalesTestCase
{
    public function test_new_records_are_accepted_and_saved_for_the_rep(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $e = $this->expense();
        $c = $this->client();
        $m = $this->meeting(['client_uuid' => $c['uuid']]);

        $response = $this->push($headers, ['expenses' => [$e], 'clients' => [$c], 'meetings' => [$m], 'app_version' => '1.3', 'device_model' => 'Pixel 7'])->assertOk();

        $this->assertSame('accepted', $this->answer($response, 'expenses', $e['uuid'])['status']);
        $this->assertSame('accepted', $this->answer($response, 'clients', $c['uuid'])['status']);
        $this->assertSame('accepted', $this->answer($response, 'meetings', $m['uuid'])['status']);

        $saved = SalesExpense::where('uuid', $e['uuid'])->first();
        $this->assertSame($profile->user_id, $saved->user_id);
        $this->assertSame(45000, $saved->amount_paise);
        $this->assertSame('pending', $saved->status);
        $this->assertSame('+919876543210', SalesClient::where('uuid', $c['uuid'])->first()->phone_normalized);
        $this->assertSame($c['uuid'], SalesMeeting::where('uuid', $m['uuid'])->first()->client_uuid);

        $profile->refresh();
        $this->assertNotNull($profile->last_sync_at);
        $this->assertSame('1.3', $profile->app_version);
        $this->assertDatabaseHas('sales_sync_batches', ['user_id' => $profile->user_id, 'received' => 3, 'accepted' => 3, 'rejected' => 0]);
    }

    public function test_sending_the_same_records_again_changes_nothing(): void
    {
        $headers = $this->headers($this->rep());
        $payload = ['expenses' => [$this->expense()], 'clients' => [$this->client()], 'meetings' => [$this->meeting()]];

        $this->push($headers, $payload)->assertOk();
        $again = $this->push($headers, $payload)->assertOk();

        foreach (['expenses', 'clients', 'meetings'] as $kind) {
            $this->assertSame('duplicate', $again->json("results.$kind.0.status"), $kind);
        }
        $this->assertSame(1, SalesExpense::count());
        $this->assertSame(1, SalesClient::count());
        $this->assertSame(1, SalesMeeting::count());
    }

    public function test_a_newer_change_is_applied_and_an_older_one_is_ignored(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->expense(['updated_at' => $this->moment('-1 hour')]);
        $this->push($headers, ['expenses' => [$e]])->assertOk();

        $newer = $this->push($headers, ['expenses' => [array_merge($e, ['amount_paise' => 99900, 'updated_at' => $this->moment()])]]);
        $this->assertSame('accepted', $newer->json('results.expenses.0.status'));
        $this->assertSame(99900, SalesExpense::first()->amount_paise);

        $older = $this->push($headers, ['expenses' => [array_merge($e, ['amount_paise' => 100, 'updated_at' => $this->moment('-2 hours')])]]);
        $this->assertSame('duplicate', $older->json('results.expenses.0.status'));
        $this->assertSame(99900, SalesExpense::first()->amount_paise);
    }

    public function test_bad_records_are_rejected_one_by_one_and_the_good_ones_still_saved(): void
    {
        $headers = $this->headers($this->rep());
        $tooLate = CarbonImmutable::now(config('sales.timezone'))->addDays(3)->toDateString();

        $bad = [
            'zero' => $this->expense(['amount_paise' => 0]),
            'negative' => $this->expense(['amount_paise' => -500]),
            'huge' => $this->expense(['amount_paise' => 100000001]),
            'float' => $this->expense(['amount_paise' => 12.5]),
            'future' => $this->expense(['expense_date' => $tooLate]),
            'ancient' => $this->expense(['expense_date' => '2019-12-31']),
            'category' => $this->expense(['category' => 'gold']),
            'note' => $this->expense(['note' => str_repeat('x', 1001)]),
            'uuid' => $this->expense(['uuid' => 'not-a-uuid']),
            'time' => $this->expense(['updated_at' => 'yesterday-ish']),
        ];
        $good = $this->expense();

        $response = $this->push($headers, ['expenses' => array_merge(array_values($bad), [$good])])->assertOk();

        foreach ($bad as $why => $record) {
            $one = collect($response->json('results.expenses'))->first(fn ($r) => $r['index'] === array_search($why, array_keys($bad)));
            $this->assertSame('rejected', $one['status'], $why);
            $this->assertSame('INVALID', $one['code'], $why);
        }
        $this->assertSame('accepted', $this->answer($response, 'expenses', $good['uuid'])['status']);
        $this->assertSame(1, SalesExpense::count());
        $this->assertDatabaseHas('sales_sync_batches', ['received' => 11, 'accepted' => 1, 'rejected' => 10]);
    }

    public function test_client_and_meeting_rules(): void
    {
        $headers = $this->headers($this->rep());
        $meeting = $this->meeting();

        $response = $this->push($headers, [
            'clients' => [
                $this->client(['name' => 'A']),                      // too short
                $this->client(['phone' => '12']),                    // not a phone number
                $this->client(['notes' => str_repeat('n', 1001)]),
            ],
            'meetings' => [
                $this->meeting(['next_followup_at' => $this->moment('-1 day'), 'meeting_at' => $this->moment('-1 hour')]),   // follow-up before the meeting
                $this->meeting(['meeting_at' => $this->moment('+5 days')]),                                                   // too far ahead
                $this->meeting(['client_name' => '']),
                $this->meeting(['client_uuid' => 'nope']),
                $meeting,
            ],
        ])->assertOk();

        foreach (range(0, 2) as $i) {
            $this->assertSame('rejected', $response->json("results.clients.$i.status"), "client $i");
        }
        foreach (range(0, 3) as $i) {
            $this->assertSame('rejected', $response->json("results.meetings.$i.status"), "meeting $i");
        }
        $this->assertSame('accepted', $this->answer($response, 'meetings', $meeting['uuid'])['status']);
    }

    public function test_the_phone_can_never_set_the_status(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->expense(['status' => 'approved', 'reviewed_by' => 1, 'reject_reason' => 'x']);

        $this->push($headers, ['expenses' => [$e]])->assertOk();

        $saved = SalesExpense::first();
        $this->assertSame('pending', $saved->status);
        $this->assertNull($saved->reviewed_by);
    }

    public function test_a_reviewed_expense_is_locked(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $e = $this->expense(['updated_at' => $this->moment('-1 hour')]);
        $this->push($headers, ['expenses' => [$e]])->assertOk();
        SalesExpense::first()->approve($this->manager());

        $edit = $this->push($headers, ['expenses' => [array_merge($e, ['amount_paise' => 1, 'updated_at' => $this->moment()])]]);
        $this->assertSame('LOCKED', $edit->json('results.expenses.0.code'));

        $delete = $this->push($headers, ['deleted' => ['expenses' => [$e['uuid']]]]);
        $this->assertSame('LOCKED', $delete->json('results.deleted.expenses.0.code'));

        $this->assertSame(45000, SalesExpense::first()->amount_paise);
    }

    public function test_an_id_that_belongs_to_another_rep_is_refused_and_nothing_changes(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $mine = $this->expense(['note' => 'A secret']);
        $this->push($this->headers($a), ['expenses' => [$mine]])->assertOk();

        $attack = $this->push($this->headers($b), [
            'expenses' => [array_merge($mine, ['amount_paise' => 1, 'note' => 'hijack', 'updated_at' => $this->moment('+5 minutes')])],
            'deleted' => ['expenses' => [$mine['uuid']]],
        ]);

        $this->assertSame('UUID_TAKEN', $attack->json('results.expenses.0.code'));
        $saved = SalesExpense::where('uuid', $mine['uuid'])->first();
        $this->assertSame($a->user_id, $saved->user_id);
        $this->assertSame('A secret', $saved->note);
        $this->assertSame(45000, $saved->amount_paise);
        $this->assertNull($saved->deleted_at, 'rep B must not be able to delete rep A\'s record');
    }

    public function test_deleting_works_and_repeats_are_harmless(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->expense(['has_bill' => true]);
        $c = $this->client();
        $m = $this->meeting();
        $this->push($headers, ['expenses' => [$e], 'clients' => [$c], 'meetings' => [$m]])->assertOk();

        $gone = ['deleted' => ['expenses' => [$e['uuid']], 'clients' => [$c['uuid']], 'meetings' => [$m['uuid']]]];
        $first = $this->push($headers, $gone)->assertOk();
        $second = $this->push($headers, $gone)->assertOk();

        foreach (['expenses', 'clients', 'meetings'] as $kind) {
            $this->assertSame('accepted', $first->json("results.deleted.$kind.0.status"));
            $this->assertSame('accepted', $second->json("results.deleted.$kind.0.status"));
        }
        $this->assertSame(0, SalesExpense::count());
        $this->assertSame(1, SalesExpense::withTrashed()->count(), 'a delete is a soft delete: the history stays');

        // an unknown id is "already gone"; a deleted record sent again is ignored, not brought back
        $this->push($headers, ['deleted' => ['clients' => [(string) Str::uuid()]]])->assertOk()->assertJsonPath('results.deleted.clients.0.status', 'accepted');
        $this->assertSame('duplicate', $this->push($headers, ['expenses' => [$e]])->json('results.expenses.0.status'));
        $this->assertSame(0, SalesExpense::count());
    }

    public function test_telling_the_server_there_is_no_bill_removes_the_stored_one(): void
    {
        Storage::fake('local');
        $headers = $this->headers($this->rep());
        $e = $this->expense(['has_bill' => true, 'updated_at' => $this->moment('-1 hour')]);
        $this->push($headers, ['expenses' => [$e]])->assertOk();

        $expense = SalesExpense::first();
        Storage::disk('local')->put('sales/bills/x.jpg', 'jpeg');
        $expense->forceFill(['bill_path' => 'sales/bills/x.jpg', 'bill_mime' => 'image/jpeg'])->save();

        $this->push($headers, ['expenses' => [array_merge($e, ['has_bill' => false, 'updated_at' => $this->moment()])]])->assertOk();

        $this->assertNull($expense->refresh()->bill_path);
        Storage::disk('local')->assertMissing('sales/bills/x.jpg');
    }

    public function test_a_push_has_a_size_limit_and_needs_a_valid_shape(): void
    {
        $headers = $this->headers($this->rep());
        $limit = config('sales.max_push_records');

        $this->push($headers, ['expenses' => array_fill(0, $limit + 1, $this->expense())])->assertStatus(422)->assertJsonPath('code', 'VALIDATION_FAILED');
        $this->push($headers, ['expenses' => 'nope'])->assertStatus(422);
        $this->push($headers, ['expenses' => ['not-an-object', 5]])->assertOk()->assertJsonPath('results.expenses.0.status', 'rejected');
        $this->push($headers, [])->assertOk();

        $this->postJson('/api/sales/v1/sync/push', [])->assertStatus(401);
    }
}
