<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesMeeting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** App v1.8: the saved spot of a client, the check-in of a visit and its selfie. */
class CheckinTest extends SalesTestCase
{
    private const JAIPUR = ['lat' => 26.9124, 'lng' => 75.7873];

    private function jpeg(): string
    {
        return "\xFF\xD8\xFF\xE0" . random_bytes(20);
    }

    private function checkin(array $spot, array $over = []): array
    {
        return array_merge(['checkin_at' => $this->moment('-5 minutes'), 'checkin_lat' => $spot['lat'], 'checkin_lng' => $spot['lng'], 'checkin_accuracy_m' => 12, 'has_selfie' => true], $over);
    }

    private function sent(array $headers, string $kind, array $record)
    {
        return $this->answer($this->push($headers, [$kind => [$record]])->assertOk(), $kind, $record['uuid']);
    }

    /** The manager sets a client's spot in the admin (a phone cannot). */
    private function officeSpot(string $uuid, array $spot): void
    {
        SalesClient::where('uuid', $uuid)->firstOrFail()->forceFill($spot)->save();
    }

    public function test_the_manager_spot_reaches_the_phones_as_a_newer_version(): void
    {
        $headers = $this->headers($this->rep());
        $c = $this->client();
        $this->sent($headers, 'clients', $c);
        $before = SalesClient::where('uuid', $c['uuid'])->first()->client_updated_at;

        $this->officeSpot($c['uuid'], self::JAIPUR);

        $after = SalesClient::where('uuid', $c['uuid'])->first()->client_updated_at;
        $this->assertTrue($after->gt($before), 'a phone only takes an office change that is newer');
    }

    public function test_a_pasted_map_link_or_numbers_become_a_spot(): void
    {
        $want = ['lat' => 26.9124, 'lng' => 75.7873];

        $this->assertSame($want, SalesClient::parseSpot('26.9124, 75.7873'));
        $this->assertSame($want, SalesClient::parseSpot('https://www.google.com/maps/place/Jaipur/@26.9124,75.7873,17z/data=x'));
        $this->assertSame($want, SalesClient::parseSpot('https://www.google.com/maps?q=26.9124,75.7873'));
        $this->assertSame($want, SalesClient::parseSpot('https://maps.google.com/x/data=!3d26.9124!4d75.7873'));
        foreach (['', 'hello', '120.5, 75.1', '0.000, 0.000', 'https://maps.app.goo.gl/abc123'] as $bad) {
            $this->assertNull(SalesClient::parseSpot($bad), $bad);
        }
    }

    public function test_the_office_works_out_the_distance_itself(): void
    {
        $headers = $this->headers($this->rep());
        $c = $this->client();
        $this->sent($headers, 'clients', $c);
        $this->officeSpot($c['uuid'], self::JAIPUR);

        $near = $this->meeting(['client_uuid' => $c['uuid']] + $this->checkin(['lat' => 26.9126, 'lng' => 75.7875]));
        $away = $this->meeting(['client_uuid' => $c['uuid']] + $this->checkin(['lat' => 26.9324, 'lng' => 75.7873]));
        $this->sent($headers, 'meetings', $near);
        $this->sent($headers, 'meetings', $away);

        $this->assertSame('verified', SalesMeeting::where('uuid', $near['uuid'])->first()->checkinState());
        $this->assertSame('away', SalesMeeting::where('uuid', $away['uuid'])->first()->checkinState());
        $this->assertGreaterThan(2000, SalesMeeting::where('uuid', $away['uuid'])->value('checkin_distance_m'));
        $this->assertNotNull(SalesMeeting::where('uuid', $away['uuid'])->value('checkin_received_at'));

        // a distance the phone claims is ignored
        $liar = $this->meeting(['client_uuid' => $c['uuid'], 'checkin_distance_m' => 1] + $this->checkin(['lat' => 26.9324, 'lng' => 75.7873]));
        $this->sent($headers, 'meetings', $liar);
        $this->assertGreaterThan(2000, SalesMeeting::where('uuid', $liar['uuid'])->value('checkin_distance_m'));
    }

    public function test_a_client_without_a_spot_gives_no_distance_until_the_manager_sets_one(): void
    {
        $headers = $this->headers($this->rep());
        $c = $this->client();
        $this->sent($headers, 'clients', $c);
        $m = $this->meeting(['client_uuid' => $c['uuid']] + $this->checkin(self::JAIPUR));
        $this->sent($headers, 'meetings', $m);

        $this->assertSame('no_client_location', SalesMeeting::where('uuid', $m['uuid'])->first()->checkinState());

        $this->officeSpot($c['uuid'], self::JAIPUR);
        $this->assertSame('verified', SalesMeeting::where('uuid', $m['uuid'])->first()->checkinState());
    }

    public function test_a_bad_check_in_is_refused(): void
    {
        $headers = $this->headers($this->rep());

        $this->assertSame('rejected', $this->sent($headers, 'meetings', $this->meeting($this->checkin(['lat' => 200, 'lng' => 5])))['status']);
        $this->assertSame('rejected', $this->sent($headers, 'meetings', $this->meeting($this->checkin(self::JAIPUR, ['checkin_at' => $this->moment('+2 hours')])))['status']);
        $this->assertSame('rejected', $this->sent($headers, 'meetings', $this->meeting(['checkin_at' => $this->moment('-1 minute')]))['status'], 'a time without a spot');
    }

    public function test_the_selfie_is_stored_privately_and_only_its_owner_can_read_it(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $other = $this->headers($this->rep('REP002', '9000000002'));
        $m = $this->meeting($this->checkin(self::JAIPUR));
        $this->sent($headers, 'meetings', $m);
        $bytes = $this->jpeg();

        $this->postJson('/api/sales/v1/uploads/selfie', ['meeting_uuid' => $m['uuid'], 'data_base64' => base64_encode($bytes)], $headers)->assertOk();

        $meeting = SalesMeeting::first();
        $this->assertSame("sales/selfies/{$profile->user_id}/{$m['uuid']}.jpg", $meeting->selfie_path);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $back = $this->getJson("/api/sales/v1/meetings/{$m['uuid']}/selfie", $headers)->assertOk();
        $this->assertSame($bytes, base64_decode($back->json('data_base64')));
        $this->getJson("/api/sales/v1/meetings/{$m['uuid']}/selfie", $other)->assertNotFound();
        $this->postJson('/api/sales/v1/uploads/selfie', ['meeting_uuid' => $m['uuid'], 'data_base64' => base64_encode($bytes)], $other)->assertNotFound();

        $pulled = collect($this->getJson('/api/sales/v1/sync/pull', $headers)->json('meetings'))->firstWhere('uuid', $m['uuid']);
        $this->assertTrue($pulled['has_selfie']);
    }

    public function test_only_real_pictures_are_taken_and_a_meeting_needs_a_check_in(): void
    {
        Storage::fake('local');
        $headers = $this->headers($this->rep());
        $with = $this->meeting($this->checkin(self::JAIPUR));
        $without = $this->meeting();
        $this->sent($headers, 'meetings', $with);
        $this->sent($headers, 'meetings', $without);

        $this->postJson('/api/sales/v1/uploads/selfie', ['meeting_uuid' => $with['uuid'], 'data_base64' => base64_encode('<?php echo 1;')], $headers)->assertStatus(422);
        $this->postJson('/api/sales/v1/uploads/selfie', ['meeting_uuid' => $without['uuid'], 'data_base64' => base64_encode($this->jpeg())], $headers)->assertStatus(422);
        $this->postJson('/api/sales/v1/uploads/selfie', ['meeting_uuid' => (string) Str::uuid(), 'data_base64' => base64_encode($this->jpeg())], $headers)->assertNotFound();
    }

    public function test_deleting_the_meeting_deletes_the_selfie(): void
    {
        Storage::fake('local');
        $headers = $this->headers($this->rep());
        $m = $this->meeting($this->checkin(self::JAIPUR));
        $this->sent($headers, 'meetings', $m);
        $this->postJson('/api/sales/v1/uploads/selfie', ['meeting_uuid' => $m['uuid'], 'data_base64' => base64_encode($this->jpeg())], $headers)->assertOk();
        $path = SalesMeeting::first()->selfie_path;
        Storage::disk('local')->assertExists($path);

        $this->push($headers, ['deleted' => ['meetings' => [$m['uuid']]]])->assertOk();

        Storage::disk('local')->assertMissing($path);
    }
}
