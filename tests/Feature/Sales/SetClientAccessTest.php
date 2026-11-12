<?php

namespace Tests\Feature\Sales;

use App\Models\ActivityLog;
use App\Models\User;
use App\Sales\Actions\SetClientAccess;
use App\Sales\Filament\Resources\SalesClients\Pages\EditSalesClient;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Livewire;

/** "Who sees this client" (design 6a): the one write path, the pure rules of the screen, what the phones get, the screen itself. */
class SetClientAccessTest extends SalesTestCase
{
    private function person(string $clientUuid, array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(), 'client_uuid' => $clientUuid, 'name' => 'Prof Sharma', 'position' => 'Teacher',
            'phone' => '9' . random_int(100000000, 999999999), 'email' => '', 'notes' => '', 'updated_at' => $this->moment(),
        ], $over);
    }

    private function pull(array $headers): array
    {
        return $this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json();
    }

    /** A client that the office added (nobody locked), with two people. */
    private function officeClient(string $name = 'Welham School'): SalesClient
    {
        $client = SalesClient::create(['name' => $name, 'city' => 'Dehradun', 'user_id' => $this->manager()->id]);
        foreach (['Teacher One', 'Teacher Two'] as $person) {
            SalesContact::create(['client_uuid' => $client->uuid, 'user_id' => $client->user_id, 'name' => $person, 'phone' => '9' . random_int(100000000, 999999999)]);
        }

        return $client->refresh();
    }

    private function viewerIds(SalesClient $client): array
    {
        return $client->viewers()->pluck('users.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
    }

    // ---- the action -----------------------------------------------------------------------------------------------------

    public function test_nobody_one_rep_and_many_reps(): void
    {
        $manager = $this->manager();
        $a = $this->rep('REP001', '9000000001', name: 'Rahul')->user_id;
        $b = $this->rep('REP002', '9000000002', name: 'Neha')->user_id;
        $client = $this->officeClient();
        $action = new SetClientAccess();

        $action->handle($client, [$a], $a, $manager);
        $this->assertSame($a, (int) $client->refresh()->assigned_to);
        $this->assertSame([], $this->viewerIds($client), 'the main rep is not stored twice');

        $action->handle($client, [$a, $b], $a, $manager);
        $this->assertSame([$b], $this->viewerIds($client));

        $action->handle($client, [], null, $manager);
        $this->assertNull($client->refresh()->assigned_to);
        $this->assertSame([], $this->viewerIds($client));
    }

    public function test_the_rep_who_added_the_client_is_never_stored_as_a_viewer(): void
    {
        $manager = $this->manager();
        $adder = $this->rep('REP001', '9000000001')->user_id;
        $b = $this->rep('REP002', '9000000002')->user_id;
        $client = SalesClient::create(['name' => 'Welham School', 'city' => 'Dehradun', 'user_id' => $adder]);

        (new SetClientAccess())->handle($client, [$adder, $b], $b, $manager);

        $this->assertSame([], $this->viewerIds($client), 'the added-by rep is locked in, the main rep is on the client');
        $this->assertSame(2, count(SalesInsights::clientAccessList([$client->id])[$client->id]['ids']));
    }

    public function test_an_inactive_or_unknown_rep_is_refused_and_nothing_is_written(): void
    {
        $manager = $this->manager();
        $gone = $this->rep('REP001', '9000000001', active: false)->user_id;
        $client = $this->officeClient();

        foreach ([[$gone], [999999]] as $ids) {
            try {
                (new SetClientAccess())->handle($client, $ids, null, $manager);
                $this->fail('should have been refused');
            } catch (InvalidArgumentException) {
                // expected
            }
        }
        $this->assertSame([], $this->viewerIds($client));
        $this->assertNull($client->refresh()->assigned_to);
    }

    public function test_a_change_is_logged_and_gives_the_client_and_its_people_a_new_phone_time(): void
    {
        $manager = $this->manager();
        $a = $this->rep('REP001', '9000000001', name: 'Rahul')->user_id;
        $client = $this->officeClient();
        $client->forceFill(['client_updated_at' => now()->subDay()])->saveQuietly();
        SalesContact::where('client_uuid', $client->uuid)->update(['client_updated_at' => now()->subDay()->utc()->format('Y-m-d H:i:s.v')]);
        $before = $client->refresh()->client_updated_at;

        (new SetClientAccess())->handle($client, [$a], null, $manager);   // only the viewers change: sales_client_access alone

        $this->assertTrue($client->refresh()->client_updated_at->gt($before), 'the client is newer, so the next pull carries it');
        SalesContact::where('client_uuid', $client->uuid)->get()->each(fn ($p) => $this->assertTrue($p->client_updated_at->gt($before), 'and so are its people'));

        $log = ActivityLog::where('subject_type', SalesClient::class)->where('subject_id', $client->id)->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(['Rahul'], $log->details['access']['new']);

        $count = ActivityLog::count();
        (new SetClientAccess())->handle($client, [$a], null, $manager);
        $this->assertSame($count, ActivityLog::count(), 'saving the same choice again writes nothing');
    }

    public function test_only_a_manager_of_the_clients_area_can_change_it(): void
    {
        $a = $this->rep('REP001', '9000000001')->user_id;
        $client = $this->officeClient();
        $hr = User::factory()->create(['role' => 'hr', 'is_active' => true]);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new SetClientAccess())->handle($client, [$a], $a, $hr);
    }

    public function test_the_three_bulk_actions_end_in_the_same_place(): void
    {
        $manager = $this->manager();
        $a = $this->rep('REP001', '9000000001')->user_id;
        $b = $this->rep('REP002', '9000000002')->user_id;
        $client = $this->officeClient();
        $action = new SetClientAccess();

        $action->give($client, $a, $manager);
        $action->open($client, [$b], $manager);
        $this->assertSame($a, (int) $client->refresh()->assigned_to);
        $this->assertSame([$b], $this->viewerIds($client));

        $action->close($client, [$a], $manager);   // the main rep is taken off: the next rep takes over
        $this->assertSame($b, (int) $client->refresh()->assigned_to);
        $this->assertSame([], $this->viewerIds($client));
    }

    // ---- the rules of the screen ------------------------------------------------------------------------------------------

    public function test_ticking_and_the_main_rep_rules(): void
    {
        // several: first ticked becomes main, un-ticking the main hands it to the first remaining rep, then nobody
        $s = SetClientAccess::toggle('several', [], null, 5);
        $this->assertSame([[5], 5], [$s['selected'], $s['main']]);
        $s = SetClientAccess::toggle('several', $s['selected'], $s['main'], 6);
        $this->assertSame([[5, 6], 5], [$s['selected'], $s['main']]);
        $s = SetClientAccess::toggle('several', $s['selected'], $s['main'], 5);
        $this->assertSame([[6], 6], [$s['selected'], $s['main']]);
        $s = SetClientAccess::toggle('several', $s['selected'], $s['main'], 6);
        $this->assertSame([[], null], [$s['selected'], $s['main']]);

        // starring a rep who is not ticked ticks them
        $s = SetClientAccess::makeMain('several', [5], 7);
        $this->assertSame([[5, 7], 7], [$s['selected'], $s['main']]);

        // one rep: picking another rep replaces the list; the added-by rep (locked) is outside the list
        $s = SetClientAccess::toggle('one', [5], 5, 6, 9);
        $this->assertSame([[6], 6], [$s['selected'], $s['main']]);
        $s = SetClientAccess::makeMain('one', [5], 6);
        $this->assertSame([[6], 6], [$s['selected'], $s['main']]);

        // several -> one with more than one ticked keeps the main rep only
        $s = SetClientAccess::switchMode('one', [5, 6, 7], 6);
        $this->assertSame([[6], 6], [$s['selected'], $s['main']]);

        // the default mode
        $this->assertSame('one', SetClientAccess::modeFor([]));
        $this->assertSame('one', SetClientAccess::modeFor([5]));
        $this->assertSame('several', SetClientAccess::modeFor([5, 6]));
    }

    // ---- what the phones get --------------------------------------------------------------------------------------------

    public function test_welham_school_goes_to_rahul_then_to_rahul_and_neha_then_to_nobody(): void
    {
        $manager = $this->manager();
        $rahul = $this->rep('REP001', '9000000001', name: 'Rahul');
        $neha = $this->rep('REP002', '9000000002', name: 'Neha');
        $pooja = $this->rep('REP003', '9000000003', name: 'Pooja');
        [$r, $n, $p] = [$this->headers($rahul), $this->headers($neha), $this->headers($pooja)];
        $client = $this->officeClient();
        $action = new SetClientAccess();

        $seen = fn (array $h) => [collect($this->pull($h)['clients'])->pluck('uuid')->all(), count($this->pull($h)['contacts'])];

        $action->handle($client, [$rahul->user_id], $rahul->user_id, $manager);
        $this->assertSame([[$client->uuid], 2], $seen($r));
        $this->assertSame([[], 0], $seen($n));
        $this->assertSame([[], 0], $seen($p));
        $oneScope = $this->pull($p)['scope'];

        $action->handle($client, [$rahul->user_id, $neha->user_id], $rahul->user_id, $manager);
        $this->assertSame([[$client->uuid], 2], $seen($r));
        $this->assertSame([[$client->uuid], 2], $seen($n));
        $this->assertSame([[], 0], $seen($p));
        $this->assertSame($oneScope, $this->pull($p)['scope'], 'Pooja\'s phone is not told anything changed');

        $neverTold = $this->pull($n)['scope'];
        $action->handle($client, [], null, $manager);
        $this->assertSame([[], 0], $seen($r));
        $this->assertSame([[], 0], $seen($n));
        $this->assertNotSame($neverTold, $this->pull($n)['scope'], 'the rep who was taken off is told, so the phone drops it');
    }

    public function test_the_added_by_rep_keeps_the_client_and_a_person_a_rep_added_stays_theirs(): void
    {
        $manager = $this->manager();
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $ha = $this->headers($a);
        $hb = $this->headers($b);

        $c = $this->client(['name' => 'JK Cement', 'phone' => '']);
        $this->push($ha, ['clients' => [$c]])->assertOk();
        $client = SalesClient::where('uuid', $c['uuid'])->first();
        (new SetClientAccess())->handle($client, [$b->user_id], $b->user_id, $manager);   // A added it; B sees it too
        $mine = $this->person($c['uuid']);
        $theirs = $this->person($c['uuid']);
        $this->push($ha, ['contacts' => [$mine]])->assertOk();
        $this->push($hb, ['contacts' => [$theirs]])->assertOk();

        // the office now closes it for B: A still has the client and both people it can see; B keeps only the person B added
        (new SetClientAccess())->handle($client->refresh(), [], null, $manager);
        $this->assertSame([$c['uuid']], collect($this->pull($ha)['clients'])->pluck('uuid')->all());
        $this->assertEqualsCanonicalizing([$mine['uuid'], $theirs['uuid']], collect($this->pull($ha)['contacts'])->pluck('uuid')->all());
        $this->assertSame([], $this->pull($hb)['clients']);
        $this->assertSame([$theirs['uuid']], collect($this->pull($hb)['contacts'])->pluck('uuid')->all());
    }

    public function test_a_phone_cannot_set_who_a_client_is_given_to(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $h = $this->headers($a);
        $c = $this->client(['name' => 'JK Cement', 'phone' => '']) + ['assigned_to' => $b->user_id, 'viewers' => [$b->user_id], 'assigned_to_code' => 'REP002'];

        $this->push($h, ['clients' => [$c]])->assertOk();

        $client = SalesClient::where('uuid', $c['uuid'])->first();
        $this->assertNotSame($b->user_id, (int) $client->assigned_to);
        $this->assertSame([], $this->viewerIds($client));
    }

    // ---- the screen -------------------------------------------------------------------------------------------------------

    public function test_the_screen_summary_save_and_unsaved_state(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $rahul = $this->rep('REP001', '9000000001', name: 'Rahul Verma');
        $neha = $this->rep('REP002', '9000000002', name: 'Neha Singh');
        $client = $this->officeClient();

        $page = Livewire::test(EditSalesClient::class, ['record' => $client->getRouteKey()])
            ->assertSee('Nobody will see Welham School except the office.')
            ->assertSet('mode', 'one')
            ->assertSet('details', false);
        $this->assertFalse($page->instance()->isDirty(), 'Save stays off until something changed');

        $page->call('toggleRep', $rahul->user_id)->assertSee('Only Rahul Verma will see Welham School and its 2 people.');
        $this->assertTrue($page->instance()->isDirty());
        $this->assertNull(SalesClient::find($client->id)->assigned_to, 'ticking changes nothing until Save');

        $page->call('setMode', 'several')->call('toggleRep', $neha->user_id)
            ->assertSee('2 reps will see Welham School and its 2 people: Neha Singh and Rahul Verma.')
            ->call('makeMain', $neha->user_id)->assertSet('main', $neha->user_id);

        $page->call('saveAccess');
        $this->assertSame($neha->user_id, (int) $client->refresh()->assigned_to);
        $this->assertSame([$rahul->user_id], $this->viewerIds($client));
        $this->assertFalse($page->instance()->isDirty(), 'saved: nothing waiting');

        $page->call("toggleRep", $rahul->user_id)->call("cancelChanges");
        $this->assertEqualsCanonicalizing([$neha->user_id, $rahul->user_id], $page->get("selected"));
    }

    public function test_an_id_from_the_browser_must_be_an_active_rep(): void
    {
        $this->actingAs($this->manager());
        $gone = $this->rep('REP001', '9000000001', active: false);
        $client = $this->officeClient();

        Livewire::test(EditSalesClient::class, ['record' => $client->getRouteKey()])->call('toggleRep', $gone->user_id)->assertStatus(422);
        Livewire::test(EditSalesClient::class, ['record' => $client->getRouteKey()])->call('toggleRep', 999999)->assertStatus(422);
    }

    public function test_a_person_without_the_right_gets_403_and_with_the_private_pool_off_nothing_is_written(): void
    {
        $rep = $this->rep('REP001', '9000000001');
        $client = $this->officeClient();

        $this->actingAs($rep->user);
        $this->get('/sales/sales-clients/' . $client->id . '/edit')->assertForbidden();

        $this->actingAs($this->manager());
        config(['sales.private_pool' => false]);
        Livewire::test(EditSalesClient::class, ['record' => $client->getRouteKey()])
            ->assertSee('Every rep sees every client while the private pool is off')
            ->call('toggleRep', $rep->user_id)->assertStatus(403);
        $this->assertNull($client->refresh()->assigned_to);
        $this->assertSame([], $this->viewerIds($client));
    }

    public function test_the_list_loads_in_a_bounded_number_of_queries(): void
    {
        $this->actingAs($this->manager());
        $reps = collect(range(1, 4))->map(fn ($i) => $this->rep('REP00' . $i, '90000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT)));
        $counted = function (int $total) use ($reps) {
            DB::table('sales_client_access')->delete();
            SalesClient::withTrashed()->forceDelete();
            $rows = [];
            for ($i = 0; $i < $total; $i++) {
                $rows[] = ['uuid' => (string) Str::uuid(), 'name' => 'Client ' . $i, 'city' => 'Jaipur', 'user_id' => $reps[$i % 4]->user_id, 'assigned_to' => $reps[($i + 1) % 4]->user_id,
                    'client_updated_at' => now()->utc()->format('Y-m-d H:i:s.v'), 'created_at' => now(), 'updated_at' => now()];
            }
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('sales_clients')->insert($chunk);
            }
            SalesInsights::forgetClientSets();   // rows were inserted behind the models' back
            $first = SalesClient::orderBy('id')->first();

            DB::enableQueryLog();
            DB::flushQueryLog();
            Livewire::test(EditSalesClient::class, ['record' => $first->getRouteKey()]);
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $small = $counted(50);
        $large = $counted(2000);

        $this->assertSame($small, $large, 'the same number of queries for 50 and for 2,000 clients');
        $this->assertSame(['all' => 2000, 'nobody' => 0, 'one' => 0, 'several' => 2000], SalesInsights::clientListCounts());
    }

    public function test_picking_another_client_swaps_it_in_place_and_the_lists_are_cached(): void
    {
        $this->actingAs($this->manager());
        $rahul = $this->rep('REP001', '9000000001', name: 'Rahul Verma');
        $one = SalesClient::create(['name' => 'Alpha School', 'city' => 'Jaipur', 'user_id' => $rahul->user_id]);
        $two = SalesClient::create(['name' => 'Beta College', 'city' => 'Jaipur', 'user_id' => $rahul->user_id]);
        (new SetClientAccess())->handle($two, [$rahul->user_id], $rahul->user_id, $this->manager());

        $page = Livewire::test(EditSalesClient::class, ['record' => $one->getRouteKey()])->assertSee('Alpha School');
        $page->call('toggleRep', $rahul->user_id);   // an unsaved tick

        $page->call('selectClient', $two->id);
        $this->assertSame($two->id, $page->instance()->getRecord()->id);
        $this->assertFalse($page->instance()->isDirty(), 'the choice of the other client starts clean');
        $page->assertSee('Beta College');

        // the second look at the same figures costs no query (cached), and a change clears them
        SalesInsights::clientListCounts();
        DB::enableQueryLog();
        DB::flushQueryLog();
        SalesInsights::clientListCounts();
        SalesInsights::activeReps();
        $this->assertCount(0, DB::getQueryLog());
        DB::disableQueryLog();

        SalesClient::create(['name' => 'Gamma Lab', 'city' => 'Jaipur', 'user_id' => $rahul->user_id]);
        $this->assertSame(3, SalesInsights::clientListCounts()['all']);
    }
}
