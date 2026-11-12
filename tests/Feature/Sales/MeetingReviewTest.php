<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Filament\Resources\SalesMeetings\Pages\ReviewSalesMeetings;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesRoute;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

/** The visit review (design 7a): the proof of a visit, the list with its chips and filters, the one write, and the privacy of the selfie. */
class MeetingReviewTest extends SalesTestCase
{
    private function visit(int $userId, array $over = []): SalesMeeting
    {
        return SalesMeeting::create(array_merge([
            'uuid' => (string) Str::uuid(), 'user_id' => $userId, 'client_name' => 'Sharma Scientific', 'meeting_at' => now()->subHours(3),
            'status' => 'done', 'purpose' => 'Demo', 'client_updated_at' => now(),
        ], $over));
    }

    private function checkedIn(int $userId, ?int $metres, array $over = []): SalesMeeting
    {
        return $this->visit($userId, array_merge([
            'checkin_at' => now()->subHours(3), 'checkin_received_at' => now()->subHours(3)->addMinutes(2), 'checkin_lat' => 26.9, 'checkin_lng' => 75.8,
            'checkin_accuracy_m' => 12, 'checkin_distance_m' => $metres, 'selfie_path' => 'sales/selfies/x/y.jpg', 'selfie_mime' => 'image/jpeg',
        ], $over));
    }

    /** A user who may open Meetings but nothing else (no clients, reps or enquiries). */
    private function viewer(): User
    {
        config(['roles.meeting_viewer' => ['label' => 'Meeting viewer', 'description' => 'test', 'access' => ['sales-meetings']]]);

        return User::factory()->create(['role' => 'meeting_viewer', 'is_active' => true]);
    }

    // ---- the proof ------------------------------------------------------------------------------------------------------------------

    public function test_the_proof_states_and_words(): void
    {
        $rep = $this->rep()->user_id;

        $at0 = SalesInsights::meetingProof($this->checkedIn($rep, 0));
        $this->assertSame(['verified', 'At the client: 0 m from the saved spot', 'At client'], [$at0['state'], $at0['line'], $at0['chip']['text']]);

        $edge = SalesInsights::meetingProof($this->checkedIn($rep, 300));
        $this->assertSame('verified', $edge['state'], 'exactly 300 m is still at the client');

        $away = SalesInsights::meetingProof($this->checkedIn($rep, 301));
        $this->assertSame(['away', '301 m from the client\'s saved spot', 'Away'], [$away['state'], $away['line'], $away['chip']['text']]);

        $far = SalesInsights::meetingProof($this->checkedIn($rep, 1400));
        $this->assertSame('1.4 km from the client\'s saved spot', $far['line']);

        $noSpot = SalesInsights::meetingProof($this->checkedIn($rep, null));
        $this->assertSame('no_client_location', $noSpot['state']);
        $this->assertStringContainsString('no saved location', $noSpot['line']);
        $this->assertNull($noSpot['scale']);

        $none = SalesInsights::meetingProof($this->visit($rep));
        $this->assertSame('none', $none['state']);
        $this->assertStringContainsString('did not check in', $none['empty']);

        $this->assertStringContainsString('Not visited yet', SalesInsights::meetingProof($this->visit($rep, ['status' => 'planned', 'meeting_at' => now()->addDay()]))['empty']);
        $this->assertStringContainsString('Marked missed', SalesInsights::meetingProof($this->visit($rep, ['status' => 'missed']))['empty']);
        $late = SalesInsights::meetingProof($this->visit($rep, ['status' => 'planned', 'meeting_at' => now()->subHours(3)]));
        $this->assertStringContainsString('nothing was written', $late['empty']);
        $this->assertStringNotContainsString('over a day', $late['empty']);
        $this->assertStringContainsString('over a day', SalesInsights::meetingProof($this->visit($rep, ['status' => 'planned', 'meeting_at' => now()->subDays(2)]))['empty']);
    }

    public function test_the_scale_position(): void
    {
        $this->assertSame(1.8, SalesInsights::scalePosition(18));
        $this->assertSame(30.0, SalesInsights::scalePosition(300), 'the green zone ends at 300 m');
        $this->assertGreaterThan(30.0, SalesInsights::scalePosition(301));
        $this->assertLessThan(95.0, SalesInsights::scalePosition(1999));
        $this->assertSame(98.0, SalesInsights::scalePosition(5000), 'far away: pinned at the end');
        $this->assertSame(98.0, SalesInsights::scalePosition(2000));
    }

    public function test_the_clock_gap_alert(): void
    {
        $rep = $this->rep()->user_id;
        $at = now()->subHours(2);

        $ten = SalesInsights::meetingProof($this->checkedIn($rep, 10, ['checkin_at' => $at, 'checkin_received_at' => (clone $at)->addMinutes(10)]));
        $eleven = SalesInsights::meetingProof($this->checkedIn($rep, 10, ['checkin_at' => $at, 'checkin_received_at' => (clone $at)->addMinutes(11)]));
        $early = SalesInsights::meetingProof($this->checkedIn($rep, 10, ['checkin_at' => $at, 'checkin_received_at' => (clone $at)->subMinutes(15)]));

        $this->assertFalse($ten['clock_gap']);
        $this->assertTrue($eleven['clock_gap']);
        $this->assertTrue($early['clock_gap'], 'a phone clock that runs ahead counts too');
    }

    public function test_not_logged(): void
    {
        $rep = $this->rep()->user_id;
        $proof = fn (array $over) => SalesInsights::meetingProof($this->visit($rep, $over))['status'];

        $this->assertSame('planned', $proof(['status' => 'planned', 'meeting_at' => now()->subMinutes(59)]));
        $this->assertSame('not_logged', $proof(['status' => 'planned', 'meeting_at' => now()->subMinutes(61)]));
        $this->assertSame('not_logged', $proof(['status' => 'moved', 'meeting_at' => now()->subHours(5)]));
        $this->assertSame('done', $proof(['status' => 'done', 'meeting_at' => now()->subDays(9)]), 'a done visit is never "not logged"');

        $old = new SalesMeeting(['status' => null, 'meeting_at' => now()->subDays(9), 'client_name' => 'Old']);
        $this->assertSame('done', SalesInsights::meetingProof($old)['status'], 'an old meeting with no status counts as done');
    }

    // ---- list, chips, filters -----------------------------------------------------------------------------------------------------

    public function test_chips_filters_and_search_give_the_same_rows_as_the_table(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Rahul Verma')->user_id;
        $b = $this->rep('REP002', '9000000002', name: 'Neha Singh')->user_id;
        $near = $this->checkedIn($a, 40, ['client_name' => 'Near Co', 'result' => 'order']);
        $away = $this->checkedIn($b, 900, ['client_name' => 'Far Co']);
        $none = $this->visit($a, ['client_name' => 'None Co', 'meeting_at' => now()->subDays(3)]);
        $late = $this->visit($b, ['client_name' => 'Late Co', 'status' => 'planned', 'meeting_at' => now()->subHours(4)]);

        $ids = fn (array $f) => SalesInsights::meetingQuery($f)->pluck('uuid')->sort()->values()->all();
        $same = fn (array $models) => collect($models)->pluck('uuid')->sort()->values()->all();

        $this->assertSame($same([$near, $away, $none, $late]), $ids([]));
        $this->assertSame($same([$away]), $ids(['chips' => ['away']]));
        $this->assertSame($same([$none, $late]), $ids(['chips' => ['none']]));
        $this->assertSame($same([$late]), $ids(['chips' => ['not_logged']]));
        $this->assertSame($same([$late]), $ids(['chips' => ['none', 'not_logged']]), 'chips combine');
        $this->assertSame($same([$near, $none]), $ids(['rep' => $a]));
        $this->assertSame($same([$near]), $ids(['rep' => $a, 'result' => 'order']));
        $this->assertSame($same([$near]), $ids(['checkin' => 'verified']));
        $this->assertSame($same([$late]), $ids(['statuses' => ['planned']]));
        $this->assertSame($same([$away]), $ids(['search' => 'Neha', 'chips' => ['away']]), 'the rep\'s name is searched too');
        $this->assertSame($same([$none]), $ids(['from' => now()->subDays(4)->toDateString(), 'until' => now()->subDays(2)->toDateString()]));

        // the chips count what the filters leave, whatever chip is on
        $this->assertSame(['all' => 2, 'away' => 1, 'none' => 1, 'not_logged' => 1], SalesInsights::meetingChipCounts(['rep' => $b]));
        $this->assertSame(['all' => 4, 'away' => 1, 'none' => 2, 'not_logged' => 1], SalesInsights::meetingChipCounts(['chips' => ['away']]));
    }

    public function test_the_export_has_the_same_rows_and_no_selfie(): void
    {
        $rep = $this->rep()->user_id;
        $this->checkedIn($rep, 40, ['client_name' => 'Near Co']);
        $this->visit($rep, ['client_name' => 'None Co']);

        ob_start();
        SalesExporter::meetings(SalesInsights::meetingQuery(['chips' => ['none']]), 'csv')->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('None Co', $csv);
        $this->assertStringNotContainsString('Near Co', $csv);
        $this->assertStringNotContainsString('selfie', strtolower($csv));
    }

    // ---- selfie privacy -------------------------------------------------------------------------------------------------------------

    public function test_the_list_asks_for_no_selfie_and_the_open_visit_asks_for_its_own(): void
    {
        $this->actingAs($this->manager());
        $rep = $this->rep()->user_id;
        $first = $this->checkedIn($rep, 40, ['meeting_at' => now()->subHour()]);
        $second = $this->checkedIn($rep, 40, ['meeting_at' => now()->subHours(5)]);

        $html = Livewire::test(ReviewSalesMeetings::class)->html();
        $this->assertStringContainsString(route('sales.selfie', $first->uuid), $html, 'the open (newest) visit shows its selfie');
        $this->assertStringNotContainsString(route('sales.selfie', $second->uuid), $html, 'the other rows of the list do not');

        $html = Livewire::test(ReviewSalesMeetings::class)->call('select', $second->uuid)->html();
        $this->assertStringContainsString(route('sales.selfie', $second->uuid), $html);
        $this->assertStringNotContainsString(route('sales.selfie', $first->uuid), $html);
    }

    public function test_a_check_in_without_a_picture_says_it_is_on_its_way(): void
    {
        $this->actingAs($this->manager());
        $this->checkedIn($this->rep()->user_id, 40, ['selfie_path' => null]);

        Livewire::test(ReviewSalesMeetings::class)->assertSee('Selfie not received yet. It follows when the phone has signal.');
    }

    public function test_the_selfie_address_needs_the_meetings_area(): void
    {
        $rep = $this->rep();
        $visit = $this->checkedIn($rep->user_id, 40);

        $this->get('/sales/selfies/' . $visit->uuid)->assertForbidden();   // a guest is refused
        $this->actingAs($rep->user)->get('/sales/selfies/' . $visit->uuid)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'hr', 'is_active' => true]))->get('/sales/selfies/' . $visit->uuid)->assertForbidden();
    }

    // ---- the one write ------------------------------------------------------------------------------------------------------------------

    public function test_use_as_client_location(): void
    {
        $this->actingAs($this->manager());
        $rep = $this->rep()->user_id;
        $client = SalesClient::create(['name' => 'Sharma Scientific', 'city' => 'Jaipur', 'user_id' => $rep]);
        $visit = $this->checkedIn($rep, null, ['client_uuid' => $client->uuid, 'checkin_lat' => 26.9124, 'checkin_lng' => 75.7873]);

        Livewire::test(ReviewSalesMeetings::class)->assertActionVisible('useAsClientSpot')->callAction('useAsClientSpot');

        $client->refresh();
        $this->assertEqualsWithDelta(26.9124, $client->lat, 0.0001);
        $this->assertSame(0, (int) $visit->refresh()->checkin_distance_m, 'the old check-in is measured again');

        // the client has a spot now: the button is gone
        Livewire::test(ReviewSalesMeetings::class)->assertActionHidden('useAsClientSpot');
    }

    public function test_a_viewer_without_the_clients_area_cannot_save_a_spot_and_sees_no_other_links(): void
    {
        $rep = $this->rep();
        $client = SalesClient::create(['name' => 'Sharma Scientific', 'city' => 'Jaipur', 'user_id' => $rep->user_id]);
        $this->checkedIn($rep->user_id, null, ['client_uuid' => $client->uuid]);

        $this->actingAs($this->viewer());
        $page = Livewire::test(ReviewSalesMeetings::class)->assertActionHidden('useAsClientSpot')
            ->assertDontSee('Open client')->assertDontSee('Enquiries of this client')->assertDontSee('Message ');
        $this->assertNull($client->refresh()->lat);

        $this->actingAs($this->manager());
        Livewire::test(ReviewSalesMeetings::class)->assertSee('Open client')->assertSee('Enquiries of this client')->assertSee('Message Rep');
    }

    // ---- the visit ---------------------------------------------------------------------------------------------------------------------

    public function test_a_route_shows_only_when_it_belongs_to_the_same_rep(): void
    {
        $a = $this->rep('REP001', '9000000001')->user_id;
        $b = $this->rep('REP002', '9000000002')->user_id;
        $stop = $this->visit($a, ['route_uuid' => $uuid = (string) Str::uuid()]);
        $route = SalesRoute::create(['uuid' => $uuid, 'user_id' => $a, 'route_date' => now()->toDateString(), 'title' => 'Alwar run', 'stops_order' => [(string) Str::uuid(), $stop->uuid], 'client_updated_at' => now()]);

        $detail = SalesInsights::meetingReviewDetail($stop->uuid);
        $this->assertSame(['title' => 'Alwar run', 'stop' => 2, 'of' => 2], $detail['route']);

        $route->forceFill(['user_id' => $b])->save();
        $this->assertNull(SalesInsights::meetingReviewDetail($stop->uuid)['route'], 'a route of another rep is never shown');
    }

    public function test_keyboard_moves_and_the_visit_review_is_the_sidebar_entry(): void
    {
        $this->actingAs($this->manager());
        $rep = $this->rep()->user_id;
        $new = $this->visit($rep, ['meeting_at' => now()->subHour(), 'client_name' => 'Newest Co']);
        $old = $this->visit($rep, ['meeting_at' => now()->subHours(6), 'client_name' => 'Oldest Co']);

        $page = Livewire::test(ReviewSalesMeetings::class)->assertSee('Newest Co');
        $page->call('move', 1);
        $this->assertSame($old->uuid, $page->get('visit'));
        $page->call('move', -1);
        $this->assertSame($new->uuid, $page->get('visit'));

        $page->call('toggleChip', 'none')->call('toggleChip', 'away');
        $this->assertSame(['none', 'away'], $page->get('chips'));
        $page->call('clearChips');
        $this->assertSame([], $page->get('chips'));

        $this->assertStringEndsWith('/sales/sales-meetings/review', \App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource::getNavigationUrl());
        $this->get('/sales/sales-meetings')->assertOk();   // the table is still there
    }

    // ---- cost ----------------------------------------------------------------------------------------------------------------------------

    public function test_the_list_and_the_panel_load_in_a_bounded_number_of_queries(): void
    {
        $this->actingAs($this->manager());
        $reps = collect(range(1, 3))->map(fn ($i) => $this->rep('REP00' . $i, '90000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT)));

        $count = function (int $total) use ($reps) {
            SalesMeeting::withTrashed()->forceDelete();
            $rows = [];
            for ($i = 0; $i < $total; $i++) {
                $rows[] = ['uuid' => (string) Str::uuid(), 'user_id' => $reps[$i % 3]->user_id, 'client_name' => 'Client ' . $i, 'client_uuid' => null, 'status' => 'done',
                    'meeting_at' => now()->subMinutes($i)->utc()->format('Y-m-d H:i:s'), 'checkin_at' => $i % 2 ? now()->utc()->format('Y-m-d H:i:s') : null, 'checkin_distance_m' => $i % 2 ? ($i % 700) : null,
                    'client_updated_at' => now()->utc()->format('Y-m-d H:i:s.v'), 'created_at' => now(), 'updated_at' => now()];
            }
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('sales_meetings')->insert($chunk);
            }
            DB::enableQueryLog();
            DB::flushQueryLog();
            Livewire::test(ReviewSalesMeetings::class);
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $this->assertSame($count(50), $count(2000), 'the same number of queries for 50 and for 2,000 visits');
    }
}
