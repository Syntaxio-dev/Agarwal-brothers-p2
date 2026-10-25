<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Things an independent review found: each one is a rule the server itself must keep, whatever the app does. */
class ReviewFixesTest extends SalesTestCase
{
    private function person(string $clientUuid, string $phone, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'client_uuid' => $clientUuid, 'name' => 'Rohit Sharma', 'position' => '',
            'phone' => $phone, 'email' => '', 'notes' => '', 'updated_at' => $this->moment(),
        ], $over);
    }

    private function callTo(string $number, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'direction' => 'outgoing', 'number' => $number, 'client_uuid' => null, 'contact_uuid' => null,
            'client_name' => 'JK', 'contact_name' => null, 'started_at' => $this->moment('-1 hour'), 'duration_seconds' => 30, 'updated_at' => $this->moment(),
        ], $over);
    }

    public function test_a_phone_clock_that_is_ahead_is_refused_with_a_clear_message(): void
    {
        $headers = $this->headers($this->rep());
        $future = $this->moment('+2 days');

        $a = $this->push($headers, [
            'expenses' => [$this->expense(['updated_at' => $future])],
            'clients' => [$this->client(['updated_at' => $future])],
            'meetings' => [$this->meeting(['updated_at' => $future])],
        ])->assertOk();

        foreach (['expenses', 'clients', 'meetings'] as $kind) {
            $this->assertSame('rejected', $a->json("results.$kind.0.status"), $kind);
            $this->assertStringContainsString('clock', $a->json("results.$kind.0.message"));
        }
        $this->assertSame(0, SalesClient::count());
    }

    public function test_the_same_number_cannot_be_saved_twice_even_by_two_phones_that_were_offline(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $one = $this->client(['phone' => '']);
        $two = $this->client(['phone' => '']);

        $this->push($a, ['clients' => [$one, $two], 'contacts' => [$this->person($one['uuid'], '98111 22233')]])->assertOk();
        $second = $this->push($b, ['contacts' => [$this->person($two['uuid'], '+91 98111-22233', ['name' => 'Copy'])]])->assertOk();

        $this->assertSame('rejected', $second->json('results.contacts.0.status'));
        $this->assertSame('INVALID', $second->json('results.contacts.0.code'));
        $this->assertStringContainsString('already saved under Rohit Sharma', $second->json('results.contacts.0.message'));
        $this->assertSame(1, SalesContact::count());

        // the same goes for a client's own number
        $third = $this->push($b, ['clients' => [$this->client(['phone' => '9811122233'])]])->assertOk();
        $this->assertSame('rejected', $third->json('results.clients.0.status'));

        // editing the person who owns the number is fine
        $own = SalesContact::first();
        $edit = $this->person($one['uuid'], '98111 22233', ['uuid' => $own->uuid, 'name' => 'Rohit S', 'updated_at' => $this->moment('+1 second')]);
        $this->travel(2)->seconds();
        $this->assertSame('accepted', $this->push($a, ['contacts' => [$edit]])->assertOk()->json('results.contacts.0.status'));
    }

    public function test_the_office_keeps_the_promise_that_only_pool_numbers_are_in_the_call_log(): void
    {
        $headers = $this->headers($this->rep());
        $client = $this->client(['phone' => '']);
        $person = $this->person($client['uuid'], '98111 22233');

        // a stranger's number: refused, nothing stored
        $refused = $this->push($headers, ['calls' => [$this->callTo('+919000011111')]])->assertOk();
        $this->assertSame('rejected', $refused->json('results.calls.0.status'));
        $this->assertSame(0, SalesCall::count());

        // a pool number: accepted
        $mine = $this->callTo('+919811122233');
        $this->assertSame('accepted', $this->push($headers, ['clients' => [$client], 'contacts' => [$person], 'calls' => [$mine]])->assertOk()->json('results.calls.0.status'));

        // the person is deleted later: the old call is still a call with a saved number (a retry is a repeat, a new phone gets it back)
        SalesContact::first()->delete();
        $this->assertSame('duplicate', $this->push($headers, ['calls' => [$mine]])->assertOk()->json('results.calls.0.status'));
        $again = $this->callTo('+919811122233', ['started_at' => $this->moment('-30 minutes')]);
        $this->assertSame('accepted', $this->push($headers, ['calls' => [$again]])->assertOk()->json('results.calls.0.status'));
    }

    public function test_a_pull_page_never_stops_in_the_middle_of_one_second(): void
    {
        config(['sales.max_pull_records' => 3]);
        $profile = $this->rep();
        $headers = $this->headers($profile);

        // seven clients all stamped with the same (old) second: the cursor must still get past them
        $old = now()->subMinutes(10)->startOfSecond();
        foreach (range(1, 7) as $i) {
            $c = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'name' => "Client $i", 'client_updated_at' => now()]);
            DB::table('sales_clients')->where('id', $c->id)->update(['updated_at' => $old]);
        }

        $seen = [];
        $since = null;
        for ($round = 0; $round < 5; $round++) {
            $page = $this->getJson('/api/sales/v1/sync/pull' . ($since ? '?since=' . urlencode($since) : ''), $headers)->assertOk();
            foreach ($page->json('clients') as $row) {
                $seen[$row['uuid']] = true;
            }
            if (! $page->json('has_more')) {
                break;
            }
            $since = $page->json('cursor');
        }

        $this->assertCount(7, $seen, 'everything arrived');
        $this->assertLessThan(4, $round + 1, 'and it did not take a page per row');
    }
}
