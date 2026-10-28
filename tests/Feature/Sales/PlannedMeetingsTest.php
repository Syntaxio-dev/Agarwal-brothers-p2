<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesMeetings\Pages\ListSalesMeetings;
use App\Sales\Filament\Resources\SalesRoutes\Pages\ListSalesRoutes;
use App\Sales\Filament\Resources\SalesEnquiries\Pages\ListSalesEnquiries;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesRoute;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Sales app v1.7: meetings that can be planned (status, plan, result, route), routes, and "came through" on an enquiry.
 * A phone from before that version sends none of the new fields: it must keep working and must not wipe what is stored.
 */
class PlannedMeetingsTest extends SalesTestCase
{
    private function planned(array $over = []): array
    {
        $at = $this->moment('+3 days');

        return $this->meeting(array_merge([
            'status' => 'planned',
            'meeting_at' => $at,
            'scheduled_at' => $at,
            'ended_at' => null,
            'route_uuid' => null,
            'result' => null,
            'moved_from' => null,
            'time_manual' => false,
            'discussion' => '',
            'outcome' => '',
            'next_followup_at' => null,
        ], $over));
    }

    private function route(array $stops, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'date' => now(config('sales.timezone'))->addDays(3)->toDateString(),
            'title' => 'Bhiwadi run',
            'area' => 'Bhiwadi',
            'start_time' => '10:00',
            'overnight' => false,
            'stops_order' => $stops,
            'updated_at' => $this->moment(),
        ], $over);
    }

    private function sent(array $headers, string $kind, array $record): array
    {
        return $this->answer($this->push($headers, [$kind => [$record]])->assertOk(), $kind, $record['uuid']);
    }

    private function pulled(array $headers): array
    {
        return $this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json();
    }

    // ---- planned meetings --------------------------------------------------------------------------------

    public function test_a_planned_meeting_is_stored_and_comes_back_with_all_its_fields(): void
    {
        $headers = $this->headers($this->rep());
        $m = $this->planned(['purpose' => 'Price talk', 'time_manual' => true]);

        $this->assertSame('accepted', $this->sent($headers, 'meetings', $m)['status']);

        $row = SalesMeeting::firstOrFail();
        $this->assertSame('planned', $row->status);
        $this->assertTrue($row->isOpen());
        $this->assertTrue($row->time_manual);
        $this->assertNotNull($row->scheduled_at);

        $back = collect($this->pulled($headers)['meetings'])->firstWhere('uuid', $m['uuid']);
        $this->assertSame('planned', $back['status']);
        $this->assertNotNull($back['scheduled_at']);
        $this->assertNull($back['route_uuid']);
        $this->assertNull($back['result']);
        $this->assertTrue($back['time_manual']);
    }

    public function test_a_meeting_can_be_planned_up_to_90_days_ahead_but_a_meeting_that_happened_cannot_be_in_the_future(): void
    {
        $headers = $this->headers($this->rep());

        $this->assertSame('accepted', $this->sent($headers, 'meetings', $this->planned(['meeting_at' => $this->moment('+89 days'), 'scheduled_at' => $this->moment('+89 days')]))['status']);

        $tooFar = $this->sent($headers, 'meetings', $this->planned(['meeting_at' => $this->moment('+95 days'), 'scheduled_at' => $this->moment('+95 days')]));
        $this->assertSame('INVALID', $tooFar['code']);

        // the same moment as a meeting that happened is refused, exactly as before
        $happened = $this->sent($headers, 'meetings', $this->planned(['status' => 'done', 'meeting_at' => $this->moment('+3 days'), 'scheduled_at' => null]));
        $this->assertSame('INVALID', $happened['code']);

        // an old phone's meeting (no status at all) keeps the old limit
        $old = $this->meeting(['meeting_at' => $this->moment('+3 days')]);
        $this->assertSame('INVALID', $this->sent($headers, 'meetings', $old)['code']);
        $this->assertSame(1, SalesMeeting::count());
    }

    public function test_the_new_fields_are_checked(): void
    {
        $headers = $this->headers($this->rep());

        foreach ([['status' => 'archived'], ['result' => 'maybe'], ['route_uuid' => 'not-a-uuid'], ['scheduled_at' => 'someday'], ['time_manual' => 'perhaps']] as $bad) {
            $m = $this->planned($bad);
            $this->assertSame('INVALID', $this->sent($headers, 'meetings', $m)['code'], json_encode($bad));
        }
        $this->assertSame(0, SalesMeeting::count());
    }

    public function test_a_planned_visit_that_is_written_up_becomes_done_with_a_result_and_keeps_its_plan(): void
    {
        $headers = $this->headers($this->rep());
        $plan = $this->moment('+1 hour');
        $m = $this->planned(['meeting_at' => $plan, 'scheduled_at' => $plan]);
        $this->sent($headers, 'meetings', $m);

        $done = array_merge($m, [
            'status' => 'done', 'meeting_at' => $this->moment('-1 minute'), 'result' => 'order', 'discussion' => 'Ordered one oven',
            'next_followup_at' => $this->moment('+3 days'), 'updated_at' => $this->moment('+1 second'),
        ]);
        $this->assertSame('accepted', $this->sent($headers, 'meetings', $done)['status']);

        $row = SalesMeeting::firstOrFail();
        $this->assertSame(['done', 'order'], [$row->status, $row->result]);
        $this->assertNotNull($row->scheduled_at, 'the plan is kept');
        $this->assertSame('Order', $row->resultLabel());
    }

    public function test_a_result_only_belongs_to_a_visit_that_was_done(): void
    {
        $headers = $this->headers($this->rep());
        $m = $this->planned(['result' => 'order']);
        $this->sent($headers, 'meetings', $m);

        $this->assertNull(SalesMeeting::firstOrFail()->result);
    }

    public function test_an_older_phone_does_not_wipe_the_planning_fields(): void
    {
        $headers = $this->headers($this->rep());
        // planned for later today: a phone of the old version can still take such a meeting
        $soon = $this->moment('+1 hour');
        $m = $this->planned(['purpose' => 'Demo', 'time_manual' => true, 'meeting_at' => $soon, 'scheduled_at' => $soon]);
        $this->sent($headers, 'meetings', $m);

        // an edit of the same meeting by a phone that knows none of the new fields
        $old = $this->meeting(['uuid' => $m['uuid'], 'meeting_at' => $m['meeting_at'], 'purpose' => 'Changed', 'updated_at' => $this->moment('+1 second')]);
        $this->assertSame('accepted', $this->sent($headers, 'meetings', $old)['status']);

        $row = SalesMeeting::firstOrFail();
        $this->assertSame('Changed', $row->purpose);
        $this->assertSame('planned', $row->status);
        $this->assertNotNull($row->scheduled_at);
        $this->assertTrue($row->time_manual);

        // an old phone's new meeting is an ordinary one: done
        $plain = $this->meeting();
        $this->sent($headers, 'meetings', $plain);
        $this->assertSame('done', SalesMeeting::where('uuid', $plain['uuid'])->first()->status);
    }

    // ---- routes -------------------------------------------------------------------------------------------

    public function test_a_route_is_stored_comes_back_and_lists_the_reps_stops_in_order(): void
    {
        $headers = $this->headers($this->rep());
        $a = $this->planned(['client_name' => 'Alpha Industries']);
        $b = $this->planned(['client_name' => 'Beta Chemicals']);
        $route = $this->route([$b['uuid'], $a['uuid']]);
        $a['route_uuid'] = $b['route_uuid'] = $route['uuid'];

        $first = $this->push($headers, ['meetings' => [$a, $b], 'routes' => [$route]])->assertOk();
        $this->assertSame('accepted', $this->answer($first, 'routes', $route['uuid'])['status']);
        $this->assertSame('duplicate', $this->answer($this->push($headers, ['routes' => [$route]]), 'routes', $route['uuid'])['status'], 'the same version again is a repeat');

        $row = SalesRoute::firstOrFail();
        $this->assertSame('Bhiwadi run', $row->title);
        $this->assertSame([$b['uuid'], $a['uuid']], $row->stops_order);
        $this->assertSame(['Beta Chemicals', 'Alpha Industries'], $row->stops()->pluck('client_name')->all());
        $this->assertSame(['finished' => 0, 'total' => 2], $row->progress());

        $back = collect($this->pulled($headers)['routes'])->firstWhere('uuid', $route['uuid']);
        $this->assertSame(['Bhiwadi run', 'Bhiwadi', '10:00', false], [$back['title'], $back['area'], $back['start_time'], $back['overnight']]);
        $this->assertSame([$b['uuid'], $a['uuid']], $back['stops_order']);
        $this->assertSame($route['date'], $back['date']);
        $this->assertFalse($back['deleted']);
    }

    public function test_route_rules(): void
    {
        $headers = $this->headers($this->rep());
        $stop = (string) Str::uuid();

        $bad = [
            'no stops' => $this->route([]),
            'eleven stops' => $this->route(array_map(fn () => (string) Str::uuid(), range(1, 11))),
            'the same stop twice' => $this->route([$stop, $stop]),
            'not an id' => $this->route(['nope']),
            'bad day' => $this->route([$stop], ['date' => 'tomorrow']),
            'too far ahead' => $this->route([$stop], ['date' => now()->addDays(200)->toDateString()]),
            'bad start time' => $this->route([$stop], ['start_time' => '25:61']),
            'no title' => $this->route([$stop], ['title' => '']),
            'a title that is too long' => $this->route([$stop], ['title' => str_repeat('x', 61)]),
        ];

        foreach ($bad as $why => $route) {
            $this->assertSame('INVALID', $this->sent($headers, 'routes', $route)['code'] ?? null, $why);
        }
        $this->assertSame(0, SalesRoute::count());

        $this->assertSame('accepted', $this->sent($headers, 'routes', $this->route(array_map(fn () => (string) Str::uuid(), range(1, 10))))['status'], 'ten is fine');
    }

    public function test_a_route_cannot_list_somebody_elses_meeting(): void
    {
        $mine = $this->headers($this->rep('REP001', '9000000001'));
        $theirs = $this->headers($this->rep('REP002', '9000000002'));
        $foreign = $this->planned();
        $this->sent($theirs, 'meetings', $foreign);

        $this->assertSame('INVALID', $this->sent($mine, 'routes', $this->route([$foreign['uuid']]))['code']);
        $this->assertSame(0, SalesRoute::count());
    }

    public function test_a_route_is_private_to_its_rep_and_a_newer_edit_wins(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $stop = (string) Str::uuid();
        $route = $this->route([$stop]);

        $this->assertSame('accepted', $this->sent($a, 'routes', $route)['status']);
        $this->assertSame('duplicate', $this->sent($a, 'routes', $route)['status'], 'the same version twice is a repeat');
        $this->assertSame('accepted', $this->sent($a, 'routes', array_merge($route, ['title' => 'Alwar run', 'updated_at' => $this->moment('+2 seconds')]))['status']);
        $this->assertSame('Alwar run', SalesRoute::firstOrFail()->title);
        $this->assertSame('duplicate', $this->sent($a, 'routes', array_merge($route, ['title' => 'Older', 'updated_at' => $this->moment('-1 hour')]))['status'], 'an older edit loses');

        $this->assertSame('UUID_TAKEN', $this->sent($b, 'routes', array_merge($route, ['updated_at' => $this->moment('+5 seconds')]))['code']);
        $this->assertSame([], $this->pulled($b)['routes']);
        $this->assertCount(1, $this->pulled($a)['routes']);
    }

    public function test_a_route_can_be_deleted_by_its_rep_only_and_comes_back_as_deleted(): void
    {
        $a = $this->headers($this->rep('REP001', '9000000001'));
        $b = $this->headers($this->rep('REP002', '9000000002'));
        $route = $this->route([(string) Str::uuid()]);
        $this->sent($a, 'routes', $route);

        $this->push($b, ['deleted' => ['routes' => [$route['uuid']]]])->assertOk();
        $this->assertNull(SalesRoute::firstOrFail()->deleted_at, 'somebody else cannot delete it');

        $this->push($a, ['deleted' => ['routes' => [$route['uuid']]]])->assertOk();
        $this->assertSoftDeleted('sales_routes', ['uuid' => $route['uuid']]);
        $this->assertTrue($this->pulled($a)['routes'][0]['deleted']);
    }

    public function test_a_phone_that_sends_no_routes_key_still_works(): void
    {
        $headers = $this->headers($this->rep());
        $this->push($headers, ['meetings' => [$this->meeting()]])->assertOk()->assertJsonPath('results.routes', []);
        $this->assertSame([], $this->pulled($headers)['routes']);
    }

    // ---- came through ------------------------------------------------------------------------------------

    private function enquiry(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'client_uuid' => null,
            'client_name' => 'JK Cement',
            'contact_uuid' => null,
            'contact_name' => '',
            'status' => 'new',
            'items' => [['product' => 'Muffle Furnace', 'brands' => 'Labtech', 'quantity' => 1, 'budget_paise' => null]],
            'opened_at' => $this->moment('-2 minutes'),
            'next_followup_at' => null,
            'closed_at' => null,
            'timeline' => [],
            'updated_at' => $this->moment(),
        ], $over);
    }

    public function test_an_enquiry_remembers_how_it_came_through(): void
    {
        $headers = $this->headers($this->rep());

        foreach (['call', 'walkin', 'whatsapp', 'reference'] as $source) {
            $e = $this->enquiry(['source' => $source]);
            $this->assertSame('accepted', $this->sent($headers, 'enquiries', $e)['status'], $source);
            $this->assertSame($source, SalesEnquiry::where('uuid', $e['uuid'])->first()->source);
        }

        $this->assertSame('INVALID', $this->sent($headers, 'enquiries', $this->enquiry(['source' => 'fax']))['code']);

        $back = collect($this->pulled($headers)['enquiries'])->firstWhere('uuid', SalesEnquiry::where('source', 'whatsapp')->first()->uuid);
        $this->assertSame('whatsapp', $back['source']);
        $this->assertSame('WhatsApp', SalesEnquiry::where('source', 'whatsapp')->first()->sourceLabel());
    }

    public function test_a_choice_can_be_cleared_and_an_older_phone_does_not_wipe_it(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->enquiry(['source' => 'call']);
        $this->sent($headers, 'enquiries', $e);

        // an older phone: no source key at all
        $old = $this->enquiry(['uuid' => $e['uuid'], 'client_name' => 'JK Cement Ltd', 'updated_at' => $this->moment('+1 second')]);
        $this->sent($headers, 'enquiries', $old);
        $this->assertSame('call', SalesEnquiry::firstOrFail()->source);
        $this->assertSame('JK Cement Ltd', SalesEnquiry::firstOrFail()->client_name);

        // the rep clears it on the phone: null is sent
        $this->sent($headers, 'enquiries', array_merge($old, ['source' => null, 'updated_at' => $this->moment('+2 seconds')]));
        $this->assertNull(SalesEnquiry::firstOrFail()->source);

        $plain = $this->enquiry();
        $this->sent($headers, 'enquiries', $plain);
        $this->assertNull(SalesEnquiry::where('uuid', $plain['uuid'])->first()->source);
    }

    // ---- what the manager sees ----------------------------------------------------------------------------

    public function test_the_manager_sees_status_result_route_and_the_figures(): void
    {
        $rep = $this->rep();
        $headers = $this->headers($rep);

        $a = $this->planned(['client_name' => 'Alpha Industries']);
        $b = $this->planned(['client_name' => 'Beta Chemicals']);
        $route = $this->route([$a['uuid'], $b['uuid']]);
        $a['route_uuid'] = $b['route_uuid'] = $route['uuid'];
        $done = $this->meeting(['client_name' => 'Gamma Labs', 'status' => 'done', 'scheduled_at' => $this->moment('-3 hours'), 'result' => 'order', 'meeting_at' => $this->moment('-2 hours')]);
        $missed = $this->planned(['client_name' => 'Delta Pharma', 'status' => 'missed', 'meeting_at' => $this->moment('-1 day'), 'scheduled_at' => $this->moment('-1 day')]);
        $overdue = $this->planned(['client_name' => 'Late Co', 'meeting_at' => $this->moment('-3 hours'), 'scheduled_at' => $this->moment('-3 hours')]);
        $this->push($headers, ['meetings' => [$a, $b, $done, $missed, $overdue], 'routes' => [$route]])->assertOk();

        $this->actingAs($this->manager());

        $rows = SalesMeeting::all()->keyBy('client_name');
        Livewire::test(ListSalesMeetings::class)
            ->assertCanSeeTableRecords($rows->values())
            ->assertTableColumnStateSet('status', 'planned', $rows['Alpha Industries'])
            ->assertTableColumnStateSet('result', 'order', $rows['Gamma Labs'])
            ->assertTableColumnStateSet('route_title', 'Bhiwadi run', $rows['Alpha Industries'])
            ->filterTable('status', ['missed'])->assertCanSeeTableRecords([$rows['Delta Pharma']])->assertCanNotSeeTableRecords([$rows['Alpha Industries']])
            ->resetTableFilters()
            ->filterTable('not_logged', true)->assertCanSeeTableRecords([$rows['Late Co']])->assertCanNotSeeTableRecords([$rows['Alpha Industries'], $rows['Gamma Labs']])
            ->resetTableFilters()
            ->filterTable('in_route', true)->assertCanSeeTableRecords([$rows['Alpha Industries'], $rows['Beta Chemicals']])->assertCanNotSeeTableRecords([$rows['Gamma Labs']]);

        $routeRow = SalesRoute::firstOrFail();
        Livewire::test(ListSalesRoutes::class)
            ->assertCanSeeTableRecords([$routeRow])
            ->assertTableColumnStateSet('progress', '0 of 2 done', $routeRow)
            ->assertSee('Bhiwadi run');

        $summary = SalesInsights::summary(30);
        $this->assertSame(1, $summary['meetings'], 'only the visit that happened counts as a meeting');
        $this->assertSame(2, $summary['meetings_planned']);
        $this->assertSame(1, $summary['meetings_not_logged']);
        $this->assertSame(1, $summary['meetings_missed']);
    }

    public function test_the_manager_sees_how_an_enquiry_came_through(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->enquiry(['source' => 'whatsapp']);
        $other = $this->enquiry(['client_name' => 'Plain Co']);
        $this->push($headers, ['enquiries' => [$e, $other]])->assertOk();

        $this->actingAs($this->manager());
        $rows = SalesEnquiry::all()->keyBy('client_name');

        Livewire::test(ListSalesEnquiries::class)
            ->assertCanSeeTableRecords($rows->values())
            ->assertTableColumnStateSet('source', 'whatsapp', $rows['JK Cement'])
            ->filterTable('source', 'whatsapp')->assertCanSeeTableRecords([$rows['JK Cement']])->assertCanNotSeeTableRecords([$rows['Plain Co']]);
    }

    public function test_the_routes_screen_opens_for_a_manager(): void
    {
        $this->actingAs($this->manager());
        Livewire::test(ListSalesRoutes::class)->assertSuccessful();
    }
}
