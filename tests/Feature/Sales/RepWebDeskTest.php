<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesReps\Pages\ViewSalesRep;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesMemo;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesProspectNote;
use App\Sales\Models\SalesRoute;
use App\Sales\Models\SalesTask;
use App\Sales\Models\SalesTaskFile;
use App\Sales\Models\SalesTodo;
use App\Sales\Rep\Pages\RepLogin;
use App\Sales\Rep\Support\DeskMeetings;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskWork;
use App\Sales\Support\RepAccount;
use Carbon\CarbonImmutable;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The web desk, phase 2 (27 Nov 2026): the manager decides per rep which parts are open (nothing by default), every page and every
 * write checks it, and what a rep does on the web (prospects with people and calls, meeting plans and routes, tasks, to-do, notes) is
 * written like a change from one more phone of the rep, so his phone gets it on the next pull and an older phone copy never wins.
 */
class RepWebDeskTest extends SalesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('rep');
    }

    private function pull(array $headers): array
    {
        return $this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json();
    }

    private function prospects(): DeskProspects
    {
        return app(DeskProspects::class);
    }

    private function tomorrowAt(string $time): string
    {
        return CarbonImmutable::now(config('sales.timezone'))->addDay()->format('Y-m-d') . ' ' . $time;
    }

    // ---- the manager's control -------------------------------------------------------------------------------------------------------

    public function test_a_new_rep_has_no_web_desk_until_the_manager_opens_it(): void
    {
        $profile = RepAccount::create(['name' => 'New Rep', 'employee_code' => 'AB-0100', 'phone' => '9000000100', 'password' => self::PASSWORD]);
        $this->assertNull($profile->desk_areas);

        Livewire::test(RepLogin::class)->fillForm(['email' => 'AB-0100', 'password' => self::PASSWORD])->call('authenticate')->assertHasFormErrors(['email']);
        $this->assertGuest();

        $this->actingAs($profile->user);
        $this->get('/rep/home')->assertForbidden();

        // the phone app is not touched by this
        $this->postJson('/api/sales/v1/auth/login', ['login' => 'AB-0100', 'password' => self::PASSWORD])->assertOk();
    }

    public function test_the_manager_opens_and_closes_parts_of_the_desk_and_it_is_logged(): void
    {
        $profile = RepAccount::create(['name' => 'New Rep', 'employee_code' => 'AB-0101', 'phone' => '9000000101', 'password' => self::PASSWORD]);

        Filament::setCurrentPanel('sales');
        $this->actingAs($this->manager());
        // an area that does not exist is refused by the form itself
        Livewire::test(ViewSalesRep::class, ['record' => $profile->getKey()])
            ->callAction('deskAccess', ['desk_areas' => ['prospects', 'bogus']])
            ->assertHasActionErrors();
        $this->assertNull($profile->refresh()->desk_areas);

        Livewire::test(ViewSalesRep::class, ['record' => $profile->getKey()])
            ->callAction('deskAccess', ['desk_areas' => ['tasks', 'prospects']])
            ->assertHasNoActionErrors();

        $this->assertSame(['prospects', 'tasks'], $profile->refresh()->desk_areas);
        $this->assertDatabaseHas('activity_logs', ['subject_type' => \App\Sales\Models\SalesProfile::class, 'subject_id' => $profile->getKey(), 'action' => 'updated']);

        Livewire::test(ViewSalesRep::class, ['record' => $profile->getKey()])->callAction('deskAccess', ['desk_areas' => []]);
        $this->assertNull($profile->refresh()->desk_areas);
    }

    public function test_a_rep_cannot_change_his_own_access_or_open_the_office(): void
    {
        $rep = $this->rep(desk: ['leads']);
        $this->actingAs($rep->user);

        $this->get('/sales/sales-reps/' . $rep->getKey())->assertForbidden();
        $this->get('/sales/sales-reps')->assertForbidden();
    }

    public function test_a_closed_area_is_closed_for_pages_and_for_writes(): void
    {
        $rep = $this->rep(desk: ['leads']);
        $this->actingAs($rep->user);

        $this->get('/rep/home')->assertOk();
        $this->get('/rep/leads')->assertOk();
        foreach (['/rep/prospects', '/rep/prospects/create', '/rep/meetings', '/rep/tasks', '/rep/todo', '/rep/notes'] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->expectException(HttpException::class);
        $this->prospects()->save($rep->user, null, ['name' => 'Bagru Dyes', 'territory' => 'Bagru']);
    }

    public function test_closing_leads_also_stops_the_enquiry_writes(): void
    {
        $rep = $this->rep(desk: ['notes']);
        $this->actingAs($rep->user);
        $this->get('/rep/leads')->assertForbidden();
        $this->get('/rep/enquiries')->assertForbidden();

        $client = SalesClient::create(['name' => 'JK Cement', 'city' => 'Jaipur', 'user_id' => $rep->user_id]);
        $this->expectException(HttpException::class);
        app(\App\Sales\Support\RepEnquiryService::class)->create($rep->user, ['client_uuid' => $client->uuid, 'items' => [['product' => 'Oven', 'quantity' => 1]]]);
    }

    public function test_every_open_area_renders_for_the_rep(): void
    {
        $rep = $this->rep();
        $this->actingAs($rep->user);

        foreach (['/rep/home', '/rep/expenses', '/rep/prospects', '/rep/prospects/create', '/rep/meetings', '/rep/tasks', '/rep/todo', '/rep/notes', '/rep/leads', '/rep/enquiries'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    // ---- prospects -------------------------------------------------------------------------------------------------------------------

    public function test_a_prospect_added_on_the_web_reaches_the_phone_with_its_people(): void
    {
        $rep = $this->rep();
        $p = $this->prospects()->save($rep->user, null, [
            'name' => 'Bagru Dyes', 'territory' => 'Bagru', 'source' => 'google', 'notes' => 'Big dyeing unit',
            'people' => [['name' => 'Ravi Kumar', 'position' => 'Lab head', 'phone' => '98290 55671', 'email' => 'Ravi@Example.com', 'linkedin' => 'linkedin.com/in/ravi?x=1']],
        ]);

        $this->assertSame('+919829055671', $p->people->first()->phone);
        $this->assertSame('https://linkedin.com/in/ravi', $p->people->first()->linkedin);

        $pulled = collect($this->pull($this->headers($rep))['prospects'])->firstWhere('uuid', $p->uuid);
        $this->assertSame('Bagru Dyes', $pulled['name']);
        $this->assertSame('ravi@example.com', $pulled['people'][0]['email']);
        $this->assertSame($rep->employee_code, $pulled['created_by_code']);

        $this->actingAs($rep->user);
        $this->get('/rep/prospects/' . $p->getKey())->assertOk()->assertSee('Ravi Kumar')->assertSee('https://linkedin.com/in/ravi', false);
    }

    public function test_the_team_spelling_of_a_territory_is_kept_and_a_company_is_saved_once_per_territory(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $this->prospects()->save($a->user, null, ['name' => 'Bagru Dyes', 'territory' => 'Bagru']);

        $other = $this->prospects()->save($b->user, null, ['name' => 'Shree Labs', 'territory' => ' bagru ']);
        $this->assertSame('Bagru', $other->territory);

        try {
            $this->prospects()->save($b->user, null, ['name' => 'bagru dyes.', 'territory' => 'BAGRU']);
            $this->fail('the same company in the same territory was saved twice');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('already saved in Bagru', collect($e->errors())->flatten()->first());
        }
    }

    public function test_only_the_adder_changes_a_prospect_but_any_rep_logs_a_call(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $p = $this->prospects()->save($a->user, null, ['name' => 'Bagru Dyes', 'territory' => 'Bagru']);

        foreach ([fn () => $this->prospects()->save($b->user, $p, ['name' => 'Renamed', 'territory' => 'Bagru']), fn () => $this->prospects()->delete($b->user, $p)] as $try) {
            try {
                $try();
                $this->fail('another rep changed the prospect');
            } catch (ValidationException) {
            }
        }

        $this->actingAs($b->user);
        $this->get('/rep/prospects/' . $p->getKey() . '/edit')->assertForbidden();

        $call = $this->prospects()->logCall($b->user, $p, 'call_back', 'Ring after Diwali', $this->tomorrowAt('11:00'));
        $this->assertSame($b->user_id, (int) $call->user_id);
        $this->assertSame('Bagru Dyes', $p->refresh()->name);

        $pulled = collect($this->pull($this->headers($a))['prospect_notes'])->firstWhere('uuid', $call->uuid);
        $this->assertSame('call_back', $pulled['outcome']);
        $this->assertNotNull($pulled['next_followup_at']);
    }

    public function test_a_prospect_number_that_is_already_a_client_is_refused(): void
    {
        $rep = $this->rep();
        SalesClient::create(['name' => 'JK Cement', 'city' => 'Jaipur', 'user_id' => $rep->user_id, 'phone' => '+919829055671']);

        $this->expectException(ValidationException::class);
        $this->prospects()->save($rep->user, null, ['name' => 'New Co', 'territory' => 'Behror', 'people' => [['name' => 'Asha', 'phone' => '9829055671']]]);
    }

    public function test_the_web_edit_is_newer_than_the_copy_on_the_phone(): void
    {
        $rep = $this->rep();
        $headers = $this->headers($rep);
        $uuid = (string) Str::uuid();
        $phoneTime = $this->moment('+5 minutes');   // a phone whose clock runs a little ahead
        $this->push($headers, ['prospects' => [['uuid' => $uuid, 'name' => 'Bagru Dyes', 'territory' => 'Bagru', 'people' => [], 'updated_at' => $phoneTime]]])->assertOk();

        $p = SalesProspect::where('uuid', $uuid)->firstOrFail();
        $this->prospects()->save($rep->user, $p, ['name' => 'Bagru Dyes Pvt Ltd', 'territory' => 'Bagru']);
        $this->assertSame('Bagru Dyes Pvt Ltd', $p->refresh()->name);

        // the phone sends its older copy again: the office keeps the web edit
        $answer = $this->push($headers, ['prospects' => [['uuid' => $uuid, 'name' => 'Bagru Dyes', 'territory' => 'Bagru', 'people' => [], 'updated_at' => $phoneTime]]]);
        $this->assertSame('duplicate', $this->answer($answer, 'prospects', $uuid)['status']);
        $this->assertSame('Bagru Dyes Pvt Ltd', $p->refresh()->name);
    }

    // ---- meetings --------------------------------------------------------------------------------------------------------------------

    public function test_a_planned_visit_reaches_the_phone_and_a_past_day_is_refused(): void
    {
        $rep = $this->rep();
        $client = SalesClient::create(['name' => 'JK Cement', 'city' => 'Jaipur', 'user_id' => $rep->user_id]);
        $desk = app(DeskMeetings::class);

        $m = $desk->schedule($rep->user, ['party' => 'client:' . $client->uuid, 'at' => $this->tomorrowAt('11:30'), 'purpose' => 'Demo']);
        $pulled = collect($this->pull($this->headers($rep))['meetings'])->firstWhere('uuid', $m->uuid);
        $this->assertSame('planned', $pulled['status']);
        $this->assertSame('Demo', $pulled['purpose']);
        $this->assertSame($client->uuid, $pulled['client_uuid']);
        $this->assertSame($pulled['meeting_at'], $pulled['scheduled_at']);

        $this->expectException(ValidationException::class);
        $desk->schedule($rep->user, ['party' => 'client:' . $client->uuid, 'at' => CarbonImmutable::now(config('sales.timezone'))->subDays(2)->format('Y-m-d H:i')]);
    }

    public function test_a_client_the_rep_may_not_see_cannot_be_planned(): void
    {
        $rep = $this->rep('AB-0001', '9000000001');
        $other = $this->rep('AB-0002', '9000000002');
        config(['sales.private_pool' => true]);
        $hidden = SalesClient::create(['name' => 'Hidden Co', 'city' => 'Jaipur', 'user_id' => $other->user_id]);

        if (SalesClient::query()->visibleTo($rep->user)->where('uuid', $hidden->uuid)->exists()) {
            $this->markTestSkipped('the pool is open to every rep in this set-up');
        }

        $this->expectException(ValidationException::class);
        app(DeskMeetings::class)->schedule($rep->user, ['party' => 'client:' . $hidden->uuid, 'at' => $this->tomorrowAt('10:00')]);
    }

    public function test_a_route_on_the_web_and_a_stop_moved_or_cancelled_keeps_the_route_right(): void
    {
        $rep = $this->rep();
        $c1 = SalesClient::create(['name' => 'One', 'city' => 'Bagru', 'user_id' => $rep->user_id]);
        $c2 = SalesClient::create(['name' => 'Two', 'city' => 'Bagru', 'user_id' => $rep->user_id]);
        $p = $this->prospects()->save($rep->user, null, ['name' => 'Three Co', 'territory' => 'Bagru']);
        $desk = app(DeskMeetings::class);
        $day = CarbonImmutable::now(config('sales.timezone'))->addDays(2)->toDateString();

        $route = $desk->planRoute($rep->user, $day, '10:00', ['client:' . $c1->uuid, 'client:' . $c2->uuid, 'prospect:' . $p->uuid], 'Bagru');
        $this->assertSame('Bagru run', $route->title);
        $stops = SalesMeeting::whereIn('uuid', $route->stops_order)->get()->sortBy(fn ($m) => array_search($m->uuid, $route->stops_order))->values();
        $this->assertCount(3, $stops);
        $this->assertSame(['10:00', '11:30', '13:00'], $stops->map(fn ($m) => $m->meeting_at->timezone(config('sales.timezone'))->format('H:i'))->all());
        $this->assertSame($p->uuid, $stops[2]->prospect_uuid);

        // a stop moved to another day leaves the route
        $desk->move($rep->user, $stops[0], CarbonImmutable::parse($day, config('sales.timezone'))->addDay()->format('Y-m-d') . ' 12:00');
        $this->assertNull($stops[0]->refresh()->route_uuid);
        $this->assertSame('moved', $stops[0]->status);
        $this->assertSame([$stops[1]->uuid, $stops[2]->uuid], $route->refresh()->stops_order);

        // cancelling the last two stops deletes the route
        $desk->cancel($rep->user, $stops[1]->refresh());
        $desk->cancel($rep->user, $stops[2]->refresh());
        $this->assertSoftDeleted($route->refresh());

        $pulled = $this->pull($this->headers($rep));
        $this->assertTrue(collect($pulled['routes'])->firstWhere('uuid', $route->uuid)['deleted']);
        $this->assertTrue(collect($pulled['meetings'])->firstWhere('uuid', $stops[1]->uuid)['deleted']);
    }

    public function test_a_visit_that_happened_is_not_changed_on_the_web(): void
    {
        $rep = $this->rep();
        $done = SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'client_name' => 'JK Cement', 'meeting_at' => now()->subDay(), 'status' => 'done', 'client_updated_at' => now()->subDay()]);

        $this->expectException(ValidationException::class);
        app(DeskMeetings::class)->move($rep->user, $done, $this->tomorrowAt('10:00'));
    }

    // ---- tasks -----------------------------------------------------------------------------------------------------------------------

    private function task(int $to, array $over = []): SalesTask
    {
        return SalesTask::create(array_merge(['assigned_to' => $to, 'created_by' => $this->manager()->id, 'title' => 'Quotation for JK', 'priority' => 'normal', 'status' => 'new'], $over));
    }

    public function test_a_task_update_on_the_web_reaches_the_office_and_the_phone(): void
    {
        $rep = $this->rep();
        $task = $this->task($rep->user_id);
        $version = (int) $task->refresh()->version;

        app(DeskWork::class)->updateTask($rep->user, $task, 'done', 'Sent on mail');
        $task->refresh();
        $this->assertSame('done', $task->status);
        $this->assertSame('Sent on mail', $task->rep_note);
        $this->assertGreaterThan($version, (int) $task->version);

        $pulled = collect($this->pull($this->headers($rep))['tasks'])->firstWhere('uuid', $task->uuid);
        $this->assertSame('done', $pulled['status']);

        $this->expectException(ValidationException::class);
        app(DeskWork::class)->updateTask($rep->user, $task, 'blocked', '  ');
    }

    public function test_another_reps_task_and_a_cancelled_task_cannot_be_updated(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $task = $this->task($a->user_id);

        $this->actingAs($b->user);
        $this->get('/rep/tasks/' . $task->getKey())->assertNotFound();

        $cancelled = $this->task($a->user_id, ['status' => 'cancelled']);
        $this->expectException(ValidationException::class);
        app(DeskWork::class)->updateTask($a->user, $cancelled, 'in_progress', null);
    }

    public function test_a_task_file_is_downloaded_only_by_its_rep_while_tasks_is_open(): void
    {
        Storage::fake('local');
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $task = $this->task($a->user_id);
        Storage::disk('local')->put('sales/tasks/x.pdf', '%PDF-1.4 test');
        $file = SalesTaskFile::create(['task_id' => $task->id, 'name' => 'specs.pdf', 'mime' => 'application/pdf', 'size' => 13, 'path' => 'sales/tasks/x.pdf']);

        $this->actingAs($a->user);
        $this->get('/rep/task-files/' . $file->id)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertDownload('specs.pdf');
        $this->get('/rep/tasks/' . $task->getKey())->assertOk()->assertSee('specs.pdf');

        $this->flushSession();   // another person signs in (the session guard would sign out a changed user)
        $this->actingAs($b->user);
        $this->get('/rep/task-files/' . $file->id)->assertNotFound();

        $a->update(['desk_areas' => ['notes']]);
        $this->flushSession();
        $this->actingAs($a->user->refresh());
        $this->get('/rep/task-files/' . $file->id)->assertForbidden();
    }

    // ---- expenses (view only) ------------------------------------------------------------------------------------------------------------

    public function test_a_rep_sees_only_his_own_expenses_and_bills_and_cannot_add_one(): void
    {
        Storage::fake('local');
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $mine = $this->storedExpense($a, ['note' => 'Petrol to Bagru', 'bill_type' => 'with_bill']);
        $mine->forceFill(['bill_path' => 'sales/bills/mine.jpg', 'bill_mime' => 'image/jpeg'])->save();
        Storage::disk('local')->put('sales/bills/mine.jpg', "ÿØÿ fake jpeg");
        $theirs = $this->storedExpense($b, ['note' => 'Lunch of B']);
        $theirs->forceFill(['bill_path' => 'sales/bills/theirs.jpg', 'bill_mime' => 'image/jpeg'])->save();
        Storage::disk('local')->put('sales/bills/theirs.jpg', 'x');

        $this->actingAs($a->user);
        $this->get('/rep/expenses')->assertOk()->assertSee('Petrol to Bagru')->assertDontSee('Lunch of B');
        $this->get('/rep/expenses/create')->assertNotFound();
        $this->get('/rep/bills/' . $mine->uuid)->assertOk()->assertHeader('Content-Type', 'image/jpeg')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/rep/bills/' . $theirs->uuid)->assertNotFound();

        $a->update(['desk_areas' => ['leads']]);
        $this->flushSession();
        $this->actingAs($a->user->refresh());
        $this->get('/rep/expenses')->assertForbidden();
        $this->get('/rep/bills/' . $mine->uuid)->assertForbidden();
    }

    // ---- to-do and notes -------------------------------------------------------------------------------------------------------------

    public function test_to_do_and_notes_on_the_web_reach_the_phone_and_deletes_too(): void
    {
        $rep = $this->rep();
        $work = app(DeskWork::class);

        $todo = $work->saveTodo($rep->user, null, 'Call JK about the oven', CarbonImmutable::now()->addDay()->toDateString());
        $work->setTodoDone($rep->user, $todo->refresh(), true);
        $memo = $work->saveMemo($rep->user, null, 'Bagru', "Line one\nLine two");

        $pulled = $this->pull($this->headers($rep));
        $this->assertTrue(collect($pulled['todos'])->firstWhere('uuid', $todo->uuid)['done']);
        $this->assertSame("Line one\nLine two", collect($pulled['memos'])->firstWhere('uuid', $memo->uuid)['body']);

        $work->deleteTodo($rep->user, $todo->refresh());
        $work->deleteMemo($rep->user, $memo->refresh());
        $this->assertSoftDeleted(SalesTodo::withTrashed()->where('uuid', $todo->uuid)->first());
        $this->assertSoftDeleted(SalesMemo::withTrashed()->where('uuid', $memo->uuid)->first());
    }

    public function test_a_to_do_or_note_of_another_rep_does_not_exist(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $memo = app(DeskWork::class)->saveMemo($a->user, null, null, 'Private');

        $this->expectException(HttpException::class);
        app(DeskWork::class)->saveMemo($b->user, $memo, null, 'Changed');
    }

    public function test_a_switched_off_rep_loses_the_desk_at_once(): void
    {
        $rep = $this->rep();
        $this->actingAs($rep->user);
        $this->get('/rep/notes')->assertOk();

        RepAccount::setActive($rep, false);
        $this->actingAs($rep->user->refresh());
        $this->get('/rep/notes')->assertForbidden();
    }
}
