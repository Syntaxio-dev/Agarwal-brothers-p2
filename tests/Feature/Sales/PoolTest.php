<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesPosition;
use App\Sales\Support\ClientImporter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** The shared client pool (clients + the people at them + positions) and the call log. */
class PoolTest extends SalesTestCase
{
    private function contact(string $clientUuid, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'client_uuid' => $clientUuid,
            'name' => 'Rohit Sharma',
            'position' => 'Purchase Manager',
            'phone' => '97000 11111',
            'email' => '',
            'notes' => '',
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function callRow(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'direction' => 'outgoing',
            'number' => '+919876543210',
            'client_uuid' => null,
            'contact_uuid' => null,
            'client_name' => 'JK Cement',
            'contact_name' => 'Rohit Sharma',
            'started_at' => $this->moment('-1 hour'),
            'duration_seconds' => 95,
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function pull(array $headers, ?string $since = null)
    {
        return $this->getJson('/api/sales/v1/sync/pull' . ($since ? '?since=' . urlencode($since) : ''), $headers);
    }

    // ---- the shared pool -------------------------------------------------------------

    public function test_a_client_one_rep_adds_reaches_the_other_rep_but_only_the_one_who_added_it_can_change_it(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $client = $this->client(['name' => 'JK Cement', 'city' => 'Nimbahera']);
        $person = $this->contact($client['uuid']);

        $this->push($a, ['clients' => [$client], 'contacts' => [$person]])->assertOk();

        $seen = $this->pull($b)->assertOk();
        $this->assertSame('JK Cement', $seen->json('clients.0.name'));
        $this->assertSame($person['uuid'], $seen->json('contacts.0.uuid'));
        $this->assertSame('Purchase Manager', $seen->json('contacts.0.position'));
        $this->assertSame('REP001', $seen->json('contacts.0.created_by_code'), 'the phone needs this to know who may change or delete the person');
        $this->assertNotEmpty($seen->json('contacts.0.created_by_name'), 'the other reps see who added the person');

        // B cannot change A's client or person, even with a newer time
        $this->travel(2)->seconds();
        $hack = $client + [];
        $hack['notes'] = 'Ask for Rohit after 11 am';
        $hack['updated_at'] = $this->moment();
        $refused = $this->push($b, ['clients' => [$hack], 'contacts' => [$this->contact($client['uuid'], ['uuid' => $person['uuid'], 'name' => 'Somebody Else', 'updated_at' => $this->moment()])]])->assertOk();
        $this->assertSame('NOT_ALLOWED', $this->answer($refused, 'clients', $client['uuid'])['code']);
        $this->assertSame('NOT_ALLOWED', $this->answer($refused, 'contacts', $person['uuid'])['code']);
        $this->assertNotSame('Ask for Rohit after 11 am', SalesClient::first()->notes);
        $this->assertSame('Rohit Sharma', SalesContact::first()->name);

        // A can: a newer version wins, an older one is only a duplicate
        $better = $client + [];
        $better['notes'] = 'Ask for Rohit after 11 am';
        $better['updated_at'] = $this->moment();
        $answer = $this->push($a, ['clients' => [$better]])->assertOk();
        $this->assertSame('accepted', $this->answer($answer, 'clients', $client['uuid'])['status']);
        $this->assertSame('Ask for Rohit after 11 am', SalesClient::first()->notes);

        $old = $client;
        $old['notes'] = 'old text';
        $old['updated_at'] = $this->moment('-1 hour');
        $this->assertSame('duplicate', $this->answer($this->push($a, ['clients' => [$old]])->assertOk(), 'clients', $client['uuid'])['status']);
        $this->assertSame('Ask for Rohit after 11 am', SalesClient::first()->notes);

        // the one who added it stays "added by" and "works with" does not move
        $row = SalesClient::first();
        $this->assertSame('REP001', $row->owner->salesProfile->employee_code);
        $this->assertSame($row->user_id, $row->assigned_to);
        $this->assertSame('REP001', $row->editor->salesProfile->employee_code);
    }

    public function test_a_person_without_a_company_is_stored_shown_to_the_team_and_belongs_to_the_one_who_added_them(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $alone = $this->contact('', ['client_uuid' => null, 'name' => 'Meena Joshi', 'position' => '', 'phone' => '98290 11223']);

        $this->assertSame('accepted', $this->answer($this->push($a, ['contacts' => [$alone]])->assertOk(), 'contacts', $alone['uuid'])['status']);
        $this->assertNull(SalesContact::first()->client_uuid);

        $seen = $this->pull($b)->assertOk();
        $this->assertNull($seen->json('contacts.0.client_uuid'));
        $this->assertSame('Meena Joshi', $seen->json('contacts.0.name'));
        $this->assertSame('REP001', $seen->json('contacts.0.created_by_code'));

        // another rep cannot change her, and her number is taken in the whole pool
        $this->travel(2)->seconds();
        $other = $this->contact('', ['uuid' => $alone['uuid'], 'client_uuid' => null, 'name' => 'Changed Name', 'updated_at' => $this->moment()]);
        $this->assertSame('NOT_ALLOWED', $this->answer($this->push($b, ['contacts' => [$other]])->assertOk(), 'contacts', $alone['uuid'])['code']);

        $dup = $this->contact('', ['client_uuid' => null, 'name' => 'Second Meena', 'phone' => '+91 98290 11223']);
        $this->assertSame('INVALID', $this->answer($this->push($b, ['contacts' => [$dup]])->assertOk(), 'contacts', $dup['uuid'])['code']);

        // a call with her number is a pool call even though there is no company
        $call = $this->callRow(['number' => '+919829011223', 'client_name' => '', 'contact_uuid' => $alone['uuid'], 'contact_name' => 'Meena Joshi']);
        $this->assertSame('accepted', $this->answer($this->push($b, ['calls' => [$call]])->assertOk(), 'calls', $call['uuid'])['status']);
    }

    public function test_the_phone_can_never_choose_who_a_client_is_assigned_to(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $client = $this->client();

        $this->push($this->headers($a), ['clients' => [$client + ['assigned_to' => $b->user_id, 'user_id' => $b->user_id]]])->assertOk();

        $this->assertSame($a->user_id, SalesClient::first()->assigned_to);
        $this->assertSame($a->user_id, SalesClient::first()->user_id);
    }

    public function test_only_the_one_who_added_it_can_delete_from_the_phone(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $client = $this->client();
        $person = $this->contact($client['uuid']);
        $this->push($a, ['clients' => [$client], 'contacts' => [$person]])->assertOk();

        $refused = $this->push($b, ['deleted' => ['clients' => [$client['uuid']], 'contacts' => [$person['uuid']]]])->assertOk();
        $this->assertSame('NOT_ALLOWED', $refused->json('results.deleted.clients.0.code'));
        $this->assertSame('NOT_ALLOWED', $refused->json('results.deleted.contacts.0.code'));
        $this->assertSame(1, SalesClient::count());
        $this->assertSame(1, SalesContact::count());

        $done = $this->push($a, ['deleted' => ['clients' => [$client['uuid']]]])->assertOk();
        $this->assertSame('accepted', $done->json('results.deleted.clients.0.status'));
        $this->assertSame(0, SalesClient::count());
        $this->assertSame(0, SalesContact::count(), 'the people go with the client');

        // and the other phone is told, for the client and for the person
        $this->travel(2)->seconds();
        $pull = $this->pull($b)->assertOk();
        $this->assertTrue($pull->json('clients.0.deleted'));
        $this->assertTrue($pull->json('contacts.0.deleted'));
    }

    public function test_a_guest_gets_json_even_without_the_accept_header(): void
    {
        $this->get('/api/sales/v1/me')->assertStatus(401)->assertJsonPath('code', 'UNAUTHENTICATED');
        $this->get('/api/sales/v1/sync/pull')->assertStatus(401)->assertJsonPath('code', 'UNAUTHENTICATED');
    }

    // ---- contacts and positions ----------------------------------------------------------

    public function test_contact_rules(): void
    {
        $headers = $this->headers($this->rep());
        $client = $this->client();

        $bad = $this->push($headers, ['clients' => [$client], 'contacts' => [
            $this->contact($client['uuid'], ['name' => 'X']),
            $this->contact($client['uuid'], ['phone' => '12']),
            $this->contact($client['uuid'], ['email' => 'not-an-email']),
            $this->contact('not-a-uuid'),
            $this->contact($client['uuid'], ['position' => str_repeat('p', 101)]),
        ]])->assertOk();

        foreach (range(0, 4) as $i) {
            $this->assertSame('rejected', $bad->json("results.contacts.$i.status"), "contact $i");
            $this->assertSame('INVALID', $bad->json("results.contacts.$i.code"));
        }
        $this->assertSame(0, SalesContact::count());
    }

    public function test_a_new_position_joins_the_shared_list_and_the_list_comes_back_on_every_pull(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $client = $this->client();

        $this->assertContains('Purchase Manager', $this->pull($a)->json('positions'));

        $answer = $this->push($a, ['positions' => ['R&D Head', 'purchase manager', '<script>', 'x'], 'clients' => [$client], 'contacts' => [$this->contact($client['uuid'], ['position' => 'Plant Chemist'])]])->assertOk();
        $this->assertSame('accepted', $answer->json('results.positions.0.status'));
        $this->assertSame('duplicate', $answer->json('results.positions.1.status'), 'the list is not case sensitive');
        $this->assertSame('rejected', $answer->json('results.positions.2.status'));
        $this->assertSame('rejected', $answer->json('results.positions.3.status'));

        $names = $this->pull($b, $this->moment('+1 hour'))->json('positions');
        $this->assertContains('R&D Head', $names);
        $this->assertContains('Plant Chemist', $names, 'a position typed on a person is added too');
        $this->assertSame(1, SalesPosition::where('name', 'Purchase Manager')->count());
    }

    // ---- meetings carry the people met ---------------------------------------------------------

    public function test_a_meeting_remembers_who_was_met(): void
    {
        $headers = $this->headers($this->rep());
        $client = $this->client();
        $p1 = $this->contact($client['uuid']);
        $p2 = $this->contact($client['uuid'], ['name' => 'Anita Gupta', 'phone' => '91234 56789']);
        $meeting = $this->meeting(['client_uuid' => $client['uuid'], 'contact_uuids' => [$p1['uuid'], $p2['uuid'], $p1['uuid']]]);

        $this->push($headers, ['clients' => [$client], 'contacts' => [$p1, $p2], 'meetings' => [$meeting]])->assertOk();

        $pulled = $this->pull($headers)->json('meetings.0.contact_uuids');
        $this->assertEqualsCanonicalizing([$p1['uuid'], $p2['uuid']], $pulled);

        $bad = $this->push($headers, ['meetings' => [$this->meeting(['contact_uuids' => ['nope']])]])->assertOk();
        $this->assertSame('INVALID', $bad->json('results.meetings.0.code'));
    }

    // ---- calls ---------------------------------------------------------------------------------

    public function test_calls_are_stored_once_and_stay_private_to_the_rep(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $call = $this->callRow();

        // the call is with a number of the pool (the client's main number, the same one the call uses)
        $this->push($a, ['clients' => [$this->client(['phone' => '98765 43210'])]])->assertOk();
        $first = $this->push($a, ['calls' => [$call]])->assertOk();
        $again = $this->push($a, ['calls' => [$call]])->assertOk();

        $this->assertSame('accepted', $this->answer($first, 'calls', $call['uuid'])['status']);
        $this->assertSame('duplicate', $this->answer($again, 'calls', $call['uuid'])['status']);
        $this->assertSame(1, SalesCall::count());
        $this->assertSame(95, SalesCall::first()->duration_seconds);

        $this->assertSame($call['uuid'], $this->pull($a)->json('calls.0.uuid'), 'a new phone gets its own call log back');
        $this->assertSame([], $this->pull($b)->json('calls'));

        $taken = $this->push($b, ['calls' => [$call]])->assertOk();
        $this->assertSame('UUID_TAKEN', $this->answer($taken, 'calls', $call['uuid'])['code']);
    }

    public function test_call_rules(): void
    {
        $headers = $this->headers($this->rep());

        $bad = $this->push($headers, ['calls' => [
            $this->callRow(['direction' => 'conference']),
            $this->callRow(['number' => '9876543210']),                 // must already be E.164
            $this->callRow(['duration_seconds' => -1]),
            $this->callRow(['duration_seconds' => 90000]),
            $this->callRow(['started_at' => now()->addDays(5)->toIso8601String()]),
            $this->callRow(['uuid' => 'x']),
        ]])->assertOk();

        foreach (range(0, 5) as $i) {
            $this->assertSame('rejected', $bad->json("results.calls.$i.status"), "call $i");
        }
        $this->assertSame(0, SalesCall::count());
    }

    // ---- the admin side keeps the phones in step -----------------------------------------------

    public function test_an_edit_made_in_the_admin_reaches_the_phone_even_if_the_phone_clock_is_ahead(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $client = $this->client(['updated_at' => $this->moment('+0 seconds')]);
        $this->push($headers, ['clients' => [$client]])->assertOk();
        $first = $this->pull($headers)->assertOk();

        $this->travel(5)->seconds();
        $row = SalesClient::first();
        $row->update(['assigned_to' => null, 'city' => 'Jaipur']);   // what the admin form does: no phone time given

        $next = $this->pull($headers, $first->json('cursor'))->assertOk();
        $this->assertSame('Jaipur', $next->json('clients.0.city'));
        $this->assertGreaterThan(strtotime($client['updated_at']), strtotime($next->json('clients.0.updated_at')), 'the phone sees it as newer');
    }

    public function test_the_importer_builds_clients_and_people_and_skips_known_numbers(): void
    {
        $manager = $this->manager();
        $rep = $this->rep();
        Storage::fake('local');

        $csv = "Client,Place,Contact,Position,Phone,Email\n"
            . "JK Cement,Nimbahera,Rohit Sharma,Purchase Manager,98765 43210,rohit@jk.example\n"
            . "JK Cement,Nimbahera,Anita Gupta,Lab Head,91234 56789,\n"
            . "Shree Cement,Beawar,Mohan Lal,Quality Chief,99999 11111,\n"
            . "JK Cement,Nimbahera,Copy Cat,Purchase Manager,98765 43210,\n"
            . "Bad Co,Jaipur,Somebody,Other,123,\n"
            . ",Jaipur,No Name,Other,98989 89898,\n";
        $path = Storage::disk('local')->path('import.csv');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, $csv);

        $summary = ClientImporter::fromCsv($path, $rep->user_id, $manager);

        $this->assertSame(6, $summary['rows']);
        $this->assertSame(2, $summary['clients_created']);
        $this->assertSame(2, $summary['clients_reused']);
        $this->assertSame(3, $summary['contacts_created']);
        $this->assertCount(1, $summary['duplicates']);
        $this->assertStringContainsString('Rohit Sharma', $summary['duplicates'][0]);
        $this->assertCount(2, $summary['skipped']);

        $this->assertSame(2, SalesClient::count());
        $this->assertSame(3, SalesContact::count());
        $this->assertSame([$rep->user_id], SalesClient::pluck('assigned_to')->unique()->values()->all());
        $this->assertSame('+919876543210', SalesContact::where('name', 'Rohit Sharma')->value('phone_normalized'));
        $this->assertTrue(SalesPosition::where('name', 'Quality Chief')->exists());

        // importing the same file again changes nothing
        $again = ClientImporter::fromCsv($path, $rep->user_id, $manager);
        $this->assertSame(0, $again['clients_created']);
        $this->assertSame(0, $again['contacts_created']);
        $this->assertSame(3, SalesContact::count());
    }

    public function test_importer_refuses_a_file_without_a_client_column(): void
    {
        Storage::fake('local');
        $path = Storage::disk('local')->path('bad.csv');
        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, "Foo,Bar\n1,2\n");

        $summary = ClientImporter::fromCsv($path, null, $this->manager());

        $this->assertSame(0, $summary['rows']);
        $this->assertNotEmpty($summary['skipped']);
        $this->assertSame(0, SalesClient::count());
    }
}
