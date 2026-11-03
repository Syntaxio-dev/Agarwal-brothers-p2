<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use Illuminate\Support\Str;

/** App v1.10: a rep sees only their own clients and people, plus the clients the manager opens for them; the exact spot of a client. */
class RepVisibilityTest extends SalesTestCase
{
    private const JAIPUR = ['lat' => 26.9124, 'lng' => 75.7873];

    private function person(string $clientUuid, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'client_uuid' => $clientUuid,
            'name' => 'Prof Sharma',
            'position' => 'Professor',
            'phone' => '9' . random_int(100000000, 999999999),
            'email' => '',
            'notes' => '',
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function pull(array $headers): array
    {
        return $this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json();
    }

    private function sent(array $headers, string $kind, array $record)
    {
        return $this->answer($this->push($headers, [$kind => [$record]])->assertOk(), $kind, $record['uuid']);
    }

    public function test_a_rep_sees_only_what_is_theirs_and_the_manager_sees_all(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $profileB = $this->rep('REP002', '9000000002');
        $b = $this->headers($profileB);

        $mine = $this->client(['name' => 'JK Cement', 'phone' => '']);
        $theirs = $this->client(['name' => 'Ultratech', 'phone' => '']);
        $this->sent($a, 'clients', $mine);
        $this->sent($b, 'clients', $theirs);
        $p = $this->person($mine['uuid']);
        $this->sent($a, 'contacts', $p);

        $seenByA = collect($this->pull($a)['clients'])->pluck('uuid')->all();
        $seenByB = collect($this->pull($b)['clients'])->pluck('uuid')->all();

        $this->assertSame([$mine['uuid']], $seenByA);
        $this->assertSame([$theirs['uuid']], $seenByB);
        $this->assertSame([$p['uuid']], collect($this->pull($a)['contacts'])->pluck('uuid')->all());
        $this->assertSame([], $this->pull($b)['contacts'], 'the people of a client B cannot see stay hidden');

        // the manager's admin queries are not scoped
        $this->assertSame(2, SalesClient::count());
    }

    public function test_a_client_given_to_a_rep_is_theirs_to_see(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $profileB = $this->rep('REP002', '9000000002');
        $b = $this->headers($profileB);
        $c = $this->client(['name' => 'JK Cement', 'phone' => '']);
        $this->sent($a, 'clients', $c);

        SalesClient::where('uuid', $c['uuid'])->first()->update(['assigned_to' => $profileB->user_id]);

        $this->assertSame([$c['uuid']], collect($this->pull($b)['clients'])->pluck('uuid')->all());
    }

    public function test_the_manager_opens_a_client_with_all_its_people_for_a_rep_and_closes_it_again(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $profileB = $this->rep('REP002', '9000000002');
        $b = $this->headers($profileB);
        $iit = $this->client(['name' => 'IIT Jodhpur', 'phone' => '']);
        $this->sent($a, 'clients', $iit);
        $p1 = $this->person($iit['uuid'], ['name' => 'Prof One']);
        $p2 = $this->person($iit['uuid'], ['name' => 'Prof Two']);
        $this->push($a, ['contacts' => [$p1, $p2]])->assertOk();

        $before = $this->pull($b);
        $this->assertSame([], $before['clients']);

        SalesClient::where('uuid', $iit['uuid'])->first()->viewers()->attach($profileB->user_id);

        $after = $this->pull($b);
        $this->assertSame([$iit['uuid']], collect($after['clients'])->pluck('uuid')->all());
        $this->assertEqualsCanonicalizing([$p1['uuid'], $p2['uuid']], collect($after['contacts'])->pluck('uuid')->all(), 'all the teachers come with the client');
        $this->assertNotSame($before['scope'], $after['scope'], 'the phone is told that what it may see changed');

        SalesClient::where('uuid', $iit['uuid'])->first()->viewers()->detach($profileB->user_id);
        $closed = $this->pull($b);
        $this->assertSame($before['scope'], $closed['scope'], 'closing it brings the fingerprint back, so the phone drops the client');
    }

    public function test_a_person_cannot_be_added_to_a_client_the_rep_cannot_see(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $c = $this->client(['name' => 'JK Cement', 'phone' => '']);
        $this->sent($a, 'clients', $c);

        $answer = $this->sent($b, 'contacts', $this->person($c['uuid']));

        $this->assertSame('rejected', $answer['status']);
        $this->assertSame('NOT_ALLOWED', $answer['code']);
        $this->assertSame(0, SalesContact::count());
    }

    public function test_a_number_saved_by_another_rep_does_not_tell_whose_it_is(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $c = $this->client(['name' => 'Secret Buyer Co', 'phone' => '98765 43210']);
        $this->sent($a, 'clients', $c);

        $answer = $this->sent($b, 'clients', $this->client(['name' => 'Another Co', 'phone' => '98765 43210']));

        $this->assertSame('rejected', $answer['status']);
        $this->assertStringNotContainsString('Secret Buyer', $answer['message']);
    }

    public function test_the_exact_spot_comes_from_the_rep_until_the_manager_sets_it(): void
    {
        $headers = $this->headers($this->rep());
        $c = $this->client(self::JAIPUR + ['phone' => '', 'location_at' => $this->moment()]);
        $this->assertSame('accepted', $this->sent($headers, 'clients', $c)['status']);

        $stored = SalesClient::where('uuid', $c['uuid'])->first();
        $this->assertSame(26.9124, $stored->lat);
        $this->assertSame('rep', $stored->location_source);

        // the rep may correct their own pin
        $this->sent($headers, 'clients', array_merge($c, ['lat' => 26.92, 'lng' => 75.79, 'updated_at' => $this->moment('+1 minute')]));
        $this->assertSame(26.92, $stored->fresh()->lat);

        // the manager sets it: from then on it is the office's and a phone cannot move it
        $stored->fresh()->update(['lat' => 26.5, 'lng' => 75.5]);
        $this->assertSame('office', $stored->fresh()->location_source);
        $this->sent($headers, 'clients', array_merge($c, ['lat' => 10.0, 'lng' => 10.0, 'updated_at' => $this->moment('+2 minutes')]));
        $this->assertSame(26.5, $stored->fresh()->lat);

        $back = collect($this->pull($headers)['clients'])->firstWhere('uuid', $c['uuid']);
        $this->assertSame('office', $back['location_source']);
    }

    public function test_a_spot_that_is_not_a_place_is_refused(): void
    {
        $headers = $this->headers($this->rep());

        foreach ([['lat' => 120, 'lng' => 75], ['lat' => 26.9, 'lng' => 200], ['lat' => 26.9], ['lat' => 0, 'lng' => 0]] as $bad) {
            $this->assertSame('rejected', $this->sent($headers, 'clients', $this->client($bad + ['phone' => '']))['status'], json_encode($bad));
        }
    }
}
