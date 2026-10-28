<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Filament\Resources\SalesReps\Pages\CreateSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\ListSalesReps;
use App\Sales\Filament\Resources\SalesReps\Pages\RepSignInReady;
use App\Sales\Filament\Resources\SalesReps\Pages\ViewSalesRep;
use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesProfile;
use App\Sales\Models\SalesRoute;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\SalesInsights;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The Reps screens (design 3a): the roster, one rep's profile and adding a rep in two steps. Days are business days (Asia/Kolkata).
 * 2026-10-26 is a Monday; 2026-10-25 is a Sunday.
 */
class RepsScreensTest extends SalesTestCase
{
    private function at(string $dateTime): CarbonImmutable
    {
        return CarbonImmutable::parse($dateTime, config('sales.timezone'));
    }

    private function viewerOf(array $access): User
    {
        config(['roles.test_viewer' => ['label' => 'Test viewer', 'description' => 'x', 'access' => $access]]);

        return User::factory()->create(['role' => 'test_viewer', 'is_active' => true]);
    }

    private function synced(SalesProfile $rep, \DateTimeInterface $at): SalesProfile
    {
        $rep->forceFill(['last_sync_at' => CarbonImmutable::instance($at)->utc(), 'device_model' => 'Pixel 7', 'app_version' => '1.7.2'])->save();

        return $rep;
    }

    private function visit(SalesProfile $rep, \DateTimeInterface $at, string $status = 'planned', array $over = []): SalesMeeting
    {
        return SalesMeeting::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => $rep->user_id,
            'client_name' => 'Client ' . Str::random(4),
            'meeting_at' => CarbonImmutable::instance($at)->utc(),
            'status' => $status,
            'scheduled_at' => $status === 'done' ? null : CarbonImmutable::instance($at)->utc(),
            'client_updated_at' => now(),
        ], $over));
    }

    private function enquiry(SalesProfile $rep, array $over = []): SalesEnquiry
    {
        return SalesEnquiry::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => $rep->user_id,
            'client_name' => 'JK Cement',
            'status' => 'new',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => null]],
            'opened_at' => now()->subDays(3),
            'client_updated_at' => now(),
        ], $over));
    }

    private function report(SalesProfile $rep, string $date): void
    {
        SalesDailyReport::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'report_date' => $date, 'notes' => 'ok', 'figures' => [], 'submitted_at' => now(), 'client_updated_at' => now()]);
    }

    // ---- next free employee code ---------------------------------------------------------------------------

    public function test_the_next_employee_code(): void
    {
        $this->assertSame('AB-0001', SalesInsights::nextEmployeeCode(), 'no rep yet');

        $this->rep('REP001', '9000000001');   // another shape: ignored
        $this->rep('AB-12X', '9000000002');   // does not match ^AB-(\d+)$: ignored
        $this->assertSame('AB-0001', SalesInsights::nextEmployeeCode());

        $this->rep('AB-0010', '9000000003');
        $this->rep('ab-0412', '9000000004');  // lower case counts
        $this->assertSame('AB-0413', SalesInsights::nextEmployeeCode(), 'the highest plus one, gaps are not filled');

        $this->rep('AB-09999', '9000000005'); // a longer number keeps its width
        $this->assertSame('AB-10000', SalesInsights::nextEmployeeCode());
    }

    public function test_the_add_form_offers_the_next_code_and_has_no_password_box(): void
    {
        $this->actingAs($this->manager());
        $this->rep('AB-0501', '9000000001');

        Livewire::test(CreateSalesRep::class)
            ->assertFormSet(['employee_code' => 'AB-0502'])
            ->assertFormFieldDoesNotExist('password');
    }

    // ---- roster -----------------------------------------------------------------------------------------------

    public function test_the_roster_says_where_each_rep_is_and_why_somebody_needs_a_word(): void
    {
        $now = $this->at('2026-10-26 19:00');   // Monday evening, after the report hour
        $field = $this->synced($this->rep('AB-0001', '9000000001', name: 'In Field'), $now->subMinutes(10));
        $this->visit($field, $this->at('2026-10-26 20:00'));   // a visit still open
        $this->report($field, '2026-10-26');

        $office = $this->synced($this->rep('AB-0002', '9000000002', name: 'In Office'), $now->subHour());   // no report, no visit
        $stale = $this->synced($this->rep('AB-0003', '9000000003', name: 'Stale Phone'), $this->at('2026-10-25 22:00'));
        $busy = $this->synced($this->rep('AB-0004', '9000000004', name: 'Busy Rep'), $now->subHour());
        $this->report($busy, '2026-10-26');
        $this->storedExpense($busy, ['status' => 'pending']);
        $this->storedExpense($busy, ['status' => 'pending']);
        $this->enquiry($busy, ['next_followup_at' => $now->subDays(2)->utc()]);
        $off = $this->rep('AB-0005', '9000000005', active: false, name: 'Gone Rep');

        $rows = SalesInsights::repRoster($now)->keyBy('code');

        $this->assertSame('field', $rows['AB-0001']['state']);
        $this->assertSame('In field', $rows['AB-0001']['status_line']);
        $this->assertSame([], $rows['AB-0001']['attention'], 'synced, reported, nothing late');

        $this->assertSame('office', $rows['AB-0002']['state']);
        $this->assertSame('Office · report not sent', $rows['AB-0002']['status_line']);
        $this->assertSame(['report' => 'Report not sent'], $rows['AB-0002']['attention']);

        $this->assertSame('stale', $rows['AB-0003']['state']);
        $this->assertSame('Not synced today', $rows['AB-0003']['status_line']);
        $this->assertArrayHasKey('sync', $rows['AB-0003']['attention']);

        $this->assertSame(['pending' => '2 pending', 'late' => '1 late'], $rows['AB-0004']['attention']);
        $this->assertSame(2, $rows['AB-0004']['pending']);

        $this->assertSame('off', $rows['AB-0005']['state']);
        $this->assertStringStartsWith('Switched off', $rows['AB-0005']['status_line']);
        $this->assertSame([], $rows['AB-0005']['attention'], 'a rep who is switched off never "needs attention"');

        $this->assertEqualsCanonicalizing([$stale->user_id, $office->user_id, $busy->user_id], array_keys(SalesInsights::repsNeedingAttention($now)));
        $this->assertNotContains($off->user_id, array_keys(SalesInsights::repsNeedingAttention($now)));
    }

    public function test_one_pending_expense_is_not_a_reason_and_a_sunday_needs_no_report(): void
    {
        $sunday = $this->at('2026-10-25 19:30');
        $rep = $this->synced($this->rep('AB-0001', '9000000001'), $sunday->subHour());
        $this->storedExpense($rep, ['status' => 'pending']);

        $this->assertSame([], SalesInsights::repRoster($sunday)->first()['attention']);

        $morning = $this->at('2026-10-26 10:00');   // a Monday before the report hour
        $this->synced($rep, $morning->subHour());
        $this->assertSame([], SalesInsights::repRoster($morning)->first()['attention']);
    }

    public function test_a_late_night_visit_belongs_to_its_own_business_day(): void
    {
        $rep = $this->synced($this->rep('AB-0001', '9000000001'), $this->at('2026-10-26 23:00'));
        $this->visit($rep, $this->at('2026-10-26 23:30'), 'done', ['client_name' => 'Late Night Co']);   // 18:00 UTC, the same day
        $this->visit($rep, $this->at('2026-10-27 00:10'), 'planned', ['client_name' => 'After Midnight Co']);   // 18:40 UTC on the 26th

        $profile = SalesInsights::repProfile($rep->user_id, 30, $this->at('2026-10-26 23:50'));

        $this->assertSame(['Late Night Co'], array_column($profile['visits'], 'client'), 'today holds only what is today in India');
        $this->assertSame('11:30 pm', $profile['visits'][0]['time']);
    }

    public function test_the_profile_compares_with_the_days_before(): void
    {
        $now = $this->at('2026-10-26 12:00');
        $rep = $this->synced($this->rep('AB-0001', '9000000001'), $now);

        // spent: 1000 now, 500 before => +100 %, and for money up is bad
        $this->storedExpense($rep, ['amount_paise' => 100000, 'expense_date' => '2026-10-20']);
        $this->storedExpense($rep, ['amount_paise' => 50000, 'expense_date' => '2026-09-10']);
        // meetings done: 2 now, none before => "new"
        $this->visit($rep, $this->at('2026-10-20 11:00'), 'done');
        $this->visit($rep, $this->at('2026-10-21 11:00'), 'done');

        $k = SalesInsights::repProfile($rep->user_id, 30, $now)['kpis'];

        $this->assertSame(100, $k['spent']['delta']);
        $this->assertSame('alert', $k['spent']['tone']);
        $this->assertSame(100000, $k['spent']['paise']);
        $this->assertSame('new', $k['meetings']['delta']);
        $this->assertNull($k['calls']['delta'], 'nothing now and nothing before shows nothing');
        $this->assertTrue($k['meetings']['best']);

        $this->storedExpense($rep, ['amount_paise' => 10000, 'expense_date' => '2026-10-21']);   // now 1100
        $this->storedExpense($rep, ['amount_paise' => 95000, 'expense_date' => '2026-09-12']);  // before 1450
        $down = SalesInsights::repProfile($rep->user_id, 30, $now)['kpis']['spent'];
        $this->assertSame(-24, $down['delta']);
        $this->assertSame('ok', $down['tone'], 'spending less is good');
    }

    public function test_the_report_streak_marks_sundays_as_not_working_days(): void
    {
        $now = $this->at('2026-10-26 10:00');   // Monday
        $rep = $this->synced($this->rep('AB-0001', '9000000001'), $now);
        $this->report($rep, '2026-10-24');      // Saturday: sent
        // Friday the 23rd: missed; Sunday the 25th: not a working day; Monday today: not yet

        $streak = SalesInsights::repProfile($rep->user_id, 30, $now)['report']['streak'];
        $byDay = collect($streak)->keyBy('date');

        $this->assertSame('sent', $byDay['Sat 24 Oct']['state']);
        $this->assertSame('off', $byDay['Sun 25 Oct']['state']);
        $this->assertSame('missed', $byDay['Fri 23 Oct']['state']);
        $this->assertSame('today', $byDay['Mon 26 Oct']['state']);
        $this->assertCount(14, $streak);
    }

    public function test_only_the_reps_own_visits_and_route_show_in_the_profile(): void
    {
        $now = $this->at('2026-10-26 12:00');
        $a = $this->synced($this->rep('AB-0001', '9000000001', name: 'Rep A'), $now);
        $b = $this->synced($this->rep('AB-0002', '9000000002', name: 'Rep B'), $now);
        $mine = $this->visit($a, $this->at('2026-10-26 10:00'), 'done', ['client_name' => 'Mine']);
        $theirs = $this->visit($b, $this->at('2026-10-26 11:00'), 'planned', ['client_name' => 'Theirs']);
        $route = SalesRoute::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'route_date' => '2026-10-26', 'title' => 'Bhiwadi run', 'area' => 'Bhiwadi', 'start_time' => '10:00', 'stops_order' => [$mine->uuid, $theirs->uuid], 'client_updated_at' => now()]);
        $mine->update(['route_uuid' => $route->uuid]);
        $theirs->update(['route_uuid' => $route->uuid]);   // a stop that points at A's route but is B's visit

        $pa = SalesInsights::repProfile($a->user_id, 30, $now);
        $pb = SalesInsights::repProfile($b->user_id, 30, $now);

        $this->assertSame(['Mine'], array_column($pa['visits'], 'client'));
        $this->assertSame(['Theirs'], array_column($pb['visits'], 'client'));
        $this->assertSame('Bhiwadi run', $pa['route_title']);
        $this->assertNull($pb['route_title'], 'B does not own that route');
    }

    public function test_the_roster_asks_the_database_the_same_number_of_times_for_5_and_50_reps(): void
    {
        $now = $this->at('2026-10-26 19:00');
        $make = function (int $from, int $to) use ($now) {
            for ($i = $from; $i <= $to; $i++) {
                $rep = $this->synced($this->rep(sprintf('AB-%04d', $i), sprintf('90000%05d', $i)), $now->subMinutes($i));
                $this->visit($rep, $this->at('2026-10-26 15:00'));
                $this->storedExpense($rep, ['status' => 'pending']);
                $this->enquiry($rep);
                SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'assigned_to' => $rep->user_id, 'name' => 'C' . $i, 'client_updated_at' => now()]);
            }
        };
        $count = function () use ($now) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            SalesInsights::repRoster($now);
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $make(1, 5);
        $five = $count();
        $make(6, 50);
        $this->assertSame($five, $count(), 'one set of grouped queries, not one per rep');
        $this->assertLessThan(16, $five);
    }

    // ---- roster page ------------------------------------------------------------------------------------------

    public function test_the_roster_draws_for_a_manager_with_every_permission(): void
    {
        $a = $this->synced($this->rep('AB-0001', '9876543210', name: 'Ravi Sharma'), now());
        $this->storedExpense($a, ['amount_paise' => 482300]);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(ListSalesReps::class)
            ->assertSee('Ravi Sharma')->assertSee('AB-0001')->assertSee('+91 98765 43210')->assertSee('1 rep · 1 active · 0 switched off')
            ->assertSee('Needs attention')->assertSee('Pixel 7')
            ->assertSee('₹4.82K');
    }

    public function test_a_manager_with_only_the_reps_key_sees_no_money_and_no_expenses(): void
    {
        $a = $this->synced($this->rep('AB-0001', '9876543210', name: 'Ravi Sharma'), now());
        $this->storedExpense($a, ['amount_paise' => 482300, 'status' => 'pending']);
        $this->storedExpense($a, ['amount_paise' => 100000, 'status' => 'pending']);
        $this->actingAs($this->viewerOf(['sales-reps']));

        $page = Livewire::test(ListSalesReps::class)->assertSee('Ravi Sharma')->assertSee('Last sync')->assertDontSee('₹')->assertDontSee('Spent')->assertDontSee('Pending')->assertDontSee('2 pending');
        $html = $page->html();
        $this->assertStringNotContainsString('4.82', $html);

        $profile = Livewire::test(ViewSalesRep::class, ['record' => $a->getKey()])->assertSee('Ravi Sharma')->assertDontSee('₹')->assertDontSee('Expenses waiting')->assertDontSee('Spent');
        $this->assertStringNotContainsString('4.82', $profile->html());
        $profile->call('setTab', 'expenses')->assertSet('tab', 'overview');
        Livewire::test(ViewSalesRep::class, ['record' => $a->getKey()])->call('approveExpense', SalesExpense::first()->id)->assertForbidden();
        $this->assertSame(2, SalesExpense::where('status', 'pending')->count());
    }

    public function test_a_user_without_the_reps_key_cannot_open_the_screens(): void
    {
        $a = $this->rep();
        $this->actingAs($this->viewerOf(['sales-meetings']));

        $this->get(SalesRepResource::getUrl('index'))->assertForbidden();
        $this->get(SalesRepResource::getUrl('view', ['record' => $a]))->assertForbidden();
        $this->get(SalesRepResource::getUrl('create'))->assertForbidden();

        // a field rep never reaches the panel at all
        $this->actingAs($a->user)->get('/admin/sales-reps')->assertForbidden();
    }

    public function test_the_chips_and_the_search_narrow_the_roster(): void
    {
        $this->synced($this->rep('AB-0001', '9876543210', name: 'Ravi Sharma'), now());
        $this->rep('AB-0002', '9123456780', active: false, name: 'Gone Person');
        $this->actingAs($this->manager());

        Livewire::test(ListSalesReps::class)
            ->call('setFilter', 'off')->assertSee('Gone Person')->assertDontSee('Ravi Sharma')
            ->call('setFilter', 'active')->assertSee('Ravi Sharma')->assertDontSee('Gone Person')
            ->call('setFilter', 'nonsense')->assertSet('filter', 'all')
            ->set('search', '91234 56780')->assertSee('Gone Person')->assertDontSee('Ravi Sharma')
            ->set('search', 'ab-0001')->assertSee('Ravi Sharma')->assertDontSee('Gone Person');
    }

    public function test_the_switch_asks_first_then_signs_the_phone_out_and_logs_it(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $rep = $this->rep();
        $this->headers($rep);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        Livewire::test(ListSalesReps::class)
            ->call('toggleActive', $rep->id)
            ->assertActionMounted('switchOff')
            ->callMountedAction();

        $this->assertFalse($rep->fresh()->is_active);
        $this->assertFalse($rep->user->fresh()->is_active);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertTrue(DB::table('activity_logs')->where('subject_type', SalesProfile::class)->where('subject_id', $rep->id)->exists(), 'the switch is in the activity log');

        // and on again needs no question
        Livewire::test(ListSalesReps::class)->call('toggleActive', $rep->id);
        $this->assertTrue($rep->fresh()->is_active);
    }

    // ---- profile ----------------------------------------------------------------------------------------------

    public function test_the_profile_opens_the_tab_named_in_the_address(): void
    {
        $rep = $this->synced($this->rep('AB-0001', '9876543210', name: 'Ravi Sharma'), now());
        $this->actingAs($this->manager());

        Livewire::withQueryParams(['tab' => 'account'])->test(ViewSalesRep::class, ['record' => $rep->getKey()])
            ->assertSet('tab', 'account')->assertSee('It cannot be changed.')->assertSee('Switch off Ravi Sharma');
        Livewire::withQueryParams(['tab' => 'clients'])->test(ViewSalesRep::class, ['record' => $rep->getKey()])->assertSee('No client is given to this rep yet.');
        Livewire::test(ViewSalesRep::class, ['record' => $rep->getKey()])->call('setTab', 'activity')->assertSet('tab', 'activity')->call('setTab', 'bogus')->assertSet('tab', 'overview');
    }

    public function test_the_profile_shows_the_day_the_phone_and_the_waiting_expenses(): void
    {
        $rep = $this->synced($this->rep('AB-0001', '9876543210', name: 'Ravi Sharma'), now());
        $rep->forceFill(['app_version' => '1.5'])->save();   // older than config('sales.min_app_version')
        $this->visit($rep, now(config('sales.timezone'))->startOfDay()->addHours(9), 'done', ['client_name' => 'Acme Labs']);
        $this->storedExpense($rep, ['amount_paise' => 120000, 'status' => 'pending', 'bill_type' => 'without_bill']);
        $this->enquiry($rep, ['next_followup_at' => now()->subDays(6)]);
        $this->actingAs($this->manager());

        Livewire::test(ViewSalesRep::class, ['record' => $rep->getKey()])
            ->assertSee('Acme Labs')->assertSee('1 of 1 done')->assertSee('Pixel 7')->assertSee('Update needed')->assertSee('No bill')->assertSee('6 d late')->assertSee('Expenses waiting')
            ->assertSee('Reset password')->assertSee('Edit details');
    }

    public function test_approving_from_the_profile_uses_the_shared_rules(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $mine = $this->storedExpense($a, ['status' => 'pending']);
        $other = $this->storedExpense($b, ['status' => 'pending']);

        Livewire::test(ViewSalesRep::class, ['record' => $a->getKey()])->call('approveExpense', $mine->id);
        $this->assertSame('approved', $mine->fresh()->status);
        $this->assertSame($manager->id, $mine->fresh()->reviewed_by);
        $this->assertSame(1, DB::table('activity_logs')->where('subject_type', SalesExpense::class)->where('subject_id', $mine->id)->count(), 'the audit trail is written');

        Livewire::test(ViewSalesRep::class, ['record' => $a->getKey()])->call('approveExpense', $other->id)->assertNotFound();
        $this->assertSame('pending', $other->fresh()->status, 'an expense of another rep is not approved through this profile');
    }

    public function test_resetting_the_password_from_the_profile_signs_every_phone_out(): void
    {
        $this->actingAs($this->manager());
        $rep = $this->rep();
        $this->headers($rep);

        Livewire::test(ViewSalesRep::class, ['record' => $rep->getKey()])->callAction('resetPassword');

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->postJson('/api/sales/v1/auth/login', ['login' => 'REP001', 'password' => self::PASSWORD])->assertStatus(401);
    }

    // ---- add a rep ------------------------------------------------------------------------------------------

    public function test_adding_a_rep_ends_with_the_sign_in_details_and_they_work(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);

        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'Anita Verma', 'employee_code' => 'AB-0777', 'phone' => '98765 43210'])
            ->call('create')
            ->assertHasNoFormErrors();

        $profile = SalesProfile::where('employee_code', 'AB-0777')->firstOrFail();
        $this->assertSame('+919876543210', $profile->phone);

        $ready = Livewire::test(RepSignInReady::class, ['record' => $profile->getKey()]);
        $password = $ready->get('password');
        $this->assertSame(10, strlen($password));
        $ready->assertSee('Anita Verma can sign in')->assertSee('AB-0777')->assertSee($password)->assertSee('The password is shown only now.');

        // the message, the WhatsApp link built from the normalised number, nothing in a URL
        $message = $ready->instance()->message();
        $this->assertSame('Hi Anita Verma, your AB Sales app login. Employee code: AB-0777. Password: ' . $password . '. Sign in with the code or this phone number.', $message);
        $link = $ready->viewData('whatsapp');
        $this->assertStringStartsWith('https://wa.me/919876543210?text=', $link);
        $this->assertStringContainsString(rawurlencode($password), $link);
        $this->assertStringNotContainsString($password, SalesRepResource::getUrl('view', ['record' => $profile]));
        $this->assertStringNotContainsString($password, SalesRepResource::getUrl('ready', ['record' => $profile]));

        // Done stays shut until the box is ticked
        $ready->call('done')->assertNoRedirect();
        $ready->set('given', true)->call('done')->assertRedirect(SalesRepResource::getUrl('view', ['record' => $profile]));

        // the password really works on the phone's sign-in, and a reload does not show it again
        auth()->logout();
        $this->postJson('/api/sales/v1/auth/login', ['login' => 'AB-0777', 'password' => $password])->assertOk();
        $this->actingAs($manager);
        Livewire::test(RepSignInReady::class, ['record' => $profile->getKey()])->assertRedirect(SalesRepResource::getUrl('view', ['record' => $profile]));
    }

    public function test_the_password_never_reaches_the_log_or_the_database_in_the_clear(): void
    {
        $this->actingAs($this->manager());
        $log = storage_path('logs/laravel.log');
        $before = is_file($log) ? filesize($log) : 0;

        Livewire::test(CreateSalesRep::class)->fillForm(['name' => 'Anita Verma', 'employee_code' => 'AB-0778', 'phone' => '98765 43211'])->call('create');
        $profile = SalesProfile::where('employee_code', 'AB-0778')->firstOrFail();
        $password = Livewire::test(RepSignInReady::class, ['record' => $profile->getKey()])->get('password');

        $this->assertNotSame($password, $profile->user->fresh()->password);
        $this->assertFalse(DB::table('activity_logs')->where('details', 'like', '%' . $password . '%')->exists());
        if (is_file($log)) {
            clearstatcache();
            $new = filesize($log) > $before ? substr((string) file_get_contents($log), $before) : '';
            $this->assertStringNotContainsString($password, $new);
        }
    }

    public function test_the_other_manager_cannot_read_a_password_made_for_someone_else(): void
    {
        $this->actingAs($this->manager());
        Livewire::test(CreateSalesRep::class)->fillForm(['name' => 'Anita Verma', 'employee_code' => 'AB-0779', 'phone' => '98765 43212'])->call('create');
        $profile = SalesProfile::where('employee_code', 'AB-0779')->firstOrFail();

        // another session (no stored password) lands on the profile instead of the secret screen
        session()->flush();
        $this->actingAs($this->manager());
        Livewire::test(RepSignInReady::class, ['record' => $profile->getKey()])->assertRedirect(SalesRepResource::getUrl('view', ['record' => $profile]));
    }

    public function test_the_phone_is_shown_in_one_readable_spelling(): void
    {
        $this->assertSame('+91 98290 55671', PhoneNumber::display('+919829055671'));
        $this->assertSame('', PhoneNumber::display(null));
        $this->assertSame('+14155550100', PhoneNumber::display('+14155550100'));
    }

    public function test_where_a_rep_is_today_is_not_shown_without_the_meetings_right(): void
    {
        $rep = $this->synced($this->rep('AB-0001', '9876543210', name: 'Ravi Sharma'), now());
        $open = $this->visit($rep, now(config('sales.timezone'))->addHours(2)->startOfHour(), 'planned');
        $route = SalesRoute::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'route_date' => now(config('sales.timezone'))->toDateString(), 'title' => 'Jodhpur secret run', 'area' => 'Jodhpur', 'start_time' => '10:00', 'stops_order' => [$open->uuid], 'client_updated_at' => now()]);
        $open->update(['route_uuid' => $route->uuid]);

        $this->actingAs($this->viewerOf(['sales-reps']));
        $page = Livewire::test(ListSalesReps::class);
        $this->assertStringNotContainsString('Jodhpur', $page->html());
        $this->assertStringNotContainsString('In field', $page->html());

        $profile = Livewire::test(ViewSalesRep::class, ['record' => $rep->getKey()]);
        $this->assertStringNotContainsString('Jodhpur', $profile->html());
        $this->assertStringNotContainsString('In field', $profile->html());
        $this->assertNull($profile->viewData('p')['route_title']);
        $this->assertNull($profile->viewData('p')['visits']);

        // with the right the same rep is "In field" on that route
        $this->actingAs($this->viewerOf(['sales-reps', 'sales-meetings']));
        Livewire::test(ListSalesReps::class)->assertSee('In field · Jodhpur secret run');
    }

    public function test_searching_a_code_does_not_match_phone_numbers_by_its_digits(): void
    {
        $this->synced($this->rep('AB-0412', '9000000001', name: 'Code Match'), now());
        $this->synced($this->rep('AB-0001', '9904120002', name: 'Phone Has 0412'), now());
        $this->actingAs($this->manager());

        Livewire::test(ListSalesReps::class)
            ->set('search', 'AB-0412')->assertSee('Code Match')->assertDontSee('Phone Has 0412')
            ->set('search', '04120')->assertSee('Phone Has 0412')->assertDontSee('Code Match');
    }

    public function test_an_enquiry_due_earlier_today_is_late_today_not_one_day_late(): void
    {
        $now = $this->at('2026-10-26 15:00');
        $rep = $this->synced($this->rep('AB-0001', '9876543210'), $now);
        $this->enquiry($rep, ['next_followup_at' => $this->at('2026-10-26 13:00')->utc()]);
        $this->enquiry($rep, ['client_name' => 'Older', 'next_followup_at' => $this->at('2026-10-23 10:00')->utc()]);

        $top = collect(SalesInsights::repProfile($rep->user_id, 30, $now)['enquiries']['top']);

        $this->assertSame(3, $top->firstWhere('client', 'Older')['late_days']);
        $same = $top->firstWhere('client', 'JK Cement');
        $this->assertTrue($same['late']);
        $this->assertSame(0, $same['late_days']);
    }

    // ---- the order of the roster (drag a row) ---------------------------------------------------------------------

    public function test_a_row_dragged_to_a_new_place_is_remembered(): void
    {
        $a = $this->rep('AB-0001', '9000000001', name: 'Ayush');
        $b = $this->rep('AB-0002', '9000000002', name: 'Bela');
        $c = $this->rep('AB-0003', '9000000003', name: 'Test Rep');
        $this->actingAs($this->manager());

        $this->assertSame([$a->id, $b->id, $c->id], SalesInsights::repRoster()->pluck('id')->all(), 'not placed yet: by employee code');

        Livewire::test(ListSalesReps::class)->call('moveRep', $c->id, 0);
        $this->assertSame([$c->id, $a->id, $b->id], SalesInsights::repRoster()->pluck('id')->all());

        Livewire::test(ListSalesReps::class)->call('moveRep', $c->id, 2);   // to the end
        $this->assertSame([$a->id, $b->id, $c->id], SalesInsights::repRoster()->pluck('id')->all());

        // a position outside the list is put at the end; an unknown rep changes nothing
        Livewire::test(ListSalesReps::class)->call('moveRep', $a->id, 99)->call('moveRep', 999999, 0);
        $this->assertSame([$b->id, $c->id, $a->id], SalesInsights::repRoster()->pluck('id')->all());

        // a rep added later comes after the placed ones
        $d = $this->rep('AB-0000', '9000000004', name: 'New Rep');
        $this->assertSame($d->id, SalesInsights::repRoster()->last()['id']);

        // the page shows the handles only on the whole list in the custom order
        Livewire::test(ListSalesReps::class)->assertSee('Drag a row by its handle')->assertSee('Drag Ayush to a new place')
            ->call('setFilter', 'active')->assertDontSee('Drag a row by its handle')
            ->call('setFilter', 'all')->set('search', 'ay')->assertDontSee('Drag a row by its handle');
    }

    public function test_the_order_is_not_changed_while_the_list_is_filtered_or_sorted_another_way(): void
    {
        $a = $this->rep('AB-0001', '9000000001');
        $b = $this->rep('AB-0002', '9000000002');
        $this->actingAs($this->manager());

        Livewire::test(ListSalesReps::class)->call('setFilter', 'active')->call('moveRep', $b->id, 0);
        Livewire::test(ListSalesReps::class)->set('search', 'AB')->call('moveRep', $b->id, 0);
        Livewire::test(ListSalesReps::class)->call('sortBy', 'name')->call('moveRep', $b->id, 0);

        $this->assertSame([$a->id, $b->id], SalesInsights::repRoster()->pluck('id')->all());

        $this->actingAs($this->viewerOf(['sales-meetings']));
        $this->get(SalesRepResource::getUrl('index'))->assertForbidden();
    }

    // ---- delete a rep ----------------------------------------------------------------------------------------------

    private function recordsOf(SalesProfile $rep): void
    {
        $uid = $rep->user_id;
        $expense = $this->storedExpense($rep, ['bill_type' => 'with_bill']);
        $expense->forceFill(['bill_path' => "sales/bills/{$uid}/{$expense->uuid}.jpg", 'bill_mime' => 'image/jpeg'])->save();
        Storage::disk('local')->put($expense->bill_path, 'jpegbytes');
        $this->visit($rep, $this->at('2026-10-26 10:00'), 'done');
        SalesRoute::create(['uuid' => (string) Str::uuid(), 'user_id' => $uid, 'route_date' => '2026-10-26', 'title' => 'Run', 'area' => 'Alwar', 'start_time' => '10:00', 'stops_order' => [], 'client_updated_at' => now()]);
        $this->enquiry($rep);
        $this->report($rep, '2026-10-26');
        \App\Sales\Models\SalesCall::create(['uuid' => (string) Str::uuid(), 'user_id' => $uid, 'direction' => 'outgoing', 'number' => '+919000000099', 'started_at' => now(), 'duration_seconds' => 5, 'client_updated_at' => now()]);
        \App\Sales\Models\SalesScript::create(['uuid' => (string) Str::uuid(), 'user_id' => $uid, 'product_name' => 'Muffle Furnace', 'body' => 'Say hello', 'client_updated_at' => now()]);
        \App\Sales\Models\SalesProductRequest::create(['uuid' => (string) Str::uuid(), 'user_id' => $uid, 'product_name' => 'Muffle Furnace', 'body' => 'Add a note', 'client_updated_at' => now()]);
        $client = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $uid, 'name' => 'Pool Client', 'client_updated_at' => now()]);
        \App\Sales\Models\SalesContact::create(['uuid' => (string) Str::uuid(), 'client_uuid' => $client->uuid, 'user_id' => $uid, 'name' => 'Pool Person', 'client_updated_at' => now()]);
    }

    public function test_a_rep_who_is_switched_off_can_be_deleted_with_everything_they_own(): void
    {
        Storage::fake('local');
        $manager = $this->manager();
        $this->actingAs($manager);
        $rep = $this->rep('AB-0001', '9000000001', name: 'Test Rep');
        $other = $this->rep('AB-0002', '9000000002', name: 'Keeper');
        $this->recordsOf($rep);
        $this->storedExpense($other);
        $this->headers($rep);
        $billPath = SalesExpense::where('user_id', $rep->user_id)->value('bill_path');
        $userId = $rep->user_id;

        // an active rep cannot be deleted: switch off first
        Livewire::test(ListSalesReps::class)->mountAction('deleteRep', ['id' => $rep->id])->setActionData(['confirm' => 'AB-0001'])->callMountedAction();
        $this->assertNotNull(SalesProfile::find($rep->id));

        \App\Sales\Support\RepAccount::setActive($rep->fresh(), false);

        // the window says what goes, and needs the employee code
        $page = Livewire::test(ListSalesReps::class)->mountAction('deleteRep', ['id' => $rep->id]);
        $description = (string) $page->instance()->getMountedAction()->getModalDescription();
        $this->assertStringContainsString('1 expenses', $description);
        $this->assertStringContainsString('1 meetings', $description);
        $this->assertStringContainsString('shared pool', $description);

        $page->setActionData(['confirm' => 'WRONG'])->callMountedAction()->assertHasActionErrors(['confirm']);
        $this->assertNotNull(SalesProfile::find($rep->id), 'a wrong code deletes nothing');

        $page->setActionData(['confirm' => 'ab-0001'])->callMountedAction()->assertHasNoActionErrors();

        $this->assertNull(SalesProfile::find($rep->id));
        $this->assertNull(User::find($userId));
        foreach ([SalesExpense::class, SalesMeeting::class, SalesRoute::class, SalesEnquiry::class, SalesDailyReport::class, \App\Sales\Models\SalesCall::class, \App\Sales\Models\SalesScript::class, \App\Sales\Models\SalesProductRequest::class] as $model) {
            $q = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($model)) ? $model::withTrashed() : $model::query();
            $this->assertSame(0, $q->where('user_id', $userId)->count(), $model);
        }
        Storage::disk('local')->assertMissing($billPath);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // the shared pool stays, in the manager's name; other reps are untouched
        $this->assertSame($manager->id, SalesClient::where('name', 'Pool Client')->value('user_id'));
        $this->assertSame($manager->id, \App\Sales\Models\SalesContact::where('name', 'Pool Person')->value('user_id'));
        $this->assertNotNull(SalesProfile::find($other->id));
        $this->assertSame(1, SalesExpense::where('user_id', $other->user_id)->count());
        $this->assertTrue(DB::table('activity_logs')->where('action', 'deleted')->where('user_id', $manager->id)->exists(), 'the delete is in the activity log');
    }

    public function test_the_profile_offers_delete_only_after_the_rep_is_switched_off(): void
    {
        $this->actingAs($this->manager());
        $rep = $this->rep('AB-0001', '9000000001', name: 'Test Rep');

        Livewire::withQueryParams(['tab' => 'account'])->test(ViewSalesRep::class, ['record' => $rep->getKey()])->assertSee('switch them off first')->assertDontSee('for good');

        \App\Sales\Support\RepAccount::setActive($rep->fresh(), false);
        Livewire::withQueryParams(['tab' => 'account'])->test(ViewSalesRep::class, ['record' => $rep->getKey()])->assertSee('Delete Test Rep for good')
            ->mountAction('deleteRep')->setActionData(['confirm' => 'AB-0001'])->callMountedAction()->assertRedirect(SalesRepResource::getUrl('index'));

        $this->assertNull(SalesProfile::find($rep->id));
    }
}
