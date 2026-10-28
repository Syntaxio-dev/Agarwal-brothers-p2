<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Filament\Resources\SalesExpenses\Pages\ListSalesExpenses;
use App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\ExpenseApproval;
use App\Sales\Support\SalesInsights;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * The Expenses review queue (design 4a): the four groups of what waits, the flags, the counts of a period, the decisions (one, group, ticked)
 * and the contract with the phone, which does not change. 2026-10-26 is a Monday.
 */
class ExpensesQueueTest extends SalesTestCase
{
    private function viewerOf(array $access): User
    {
        config(['roles.test_viewer' => ['label' => 'Test viewer', 'description' => 'x', 'access' => $access]]);

        return User::factory()->create(['role' => 'test_viewer', 'is_active' => true]);
    }

    /** A waiting expense of a rep with the answers an app of today gives. */
    private function waiting(SalesProfile $rep, string $kind = 'ready', array $over = []): SalesExpense
    {
        $e = $this->storedExpense($rep, array_merge(match ($kind) {
            'ready', 'photo' => ['bill_type' => 'with_bill', 'work_area' => 'local'],
            'nobill' => ['bill_type' => 'without_bill', 'work_area' => 'local'],
            default => [],   // older: no answers
        }, $over));

        if ($kind === 'ready') {
            $e->forceFill(['bill_path' => "sales/bills/{$e->user_id}/{$e->uuid}.jpg", 'bill_mime' => 'image/jpeg'])->save();
        }

        return $e;
    }

    private function at(string $dateTime): CarbonImmutable
    {
        return CarbonImmutable::parse($dateTime, config('sales.timezone'));
    }

    // ---- the four groups --------------------------------------------------------------------------------------------

    public function test_what_waits_is_sorted_into_four_groups_oldest_first(): void
    {
        $rep = $this->rep('AB-0001', '9000000001');
        $rep->forceFill(['app_version' => '1.5'])->save();
        $newer = $this->waiting($rep, 'ready', ['expense_date' => '2026-10-20', 'amount_paise' => 200000]);
        $older = $this->waiting($rep, 'ready', ['expense_date' => '2026-10-10', 'amount_paise' => 100000]);
        $nobill = $this->waiting($rep, 'nobill');
        $photo = $this->waiting($rep, 'photo');      // says it has a bill, the photo is still on the phone
        $oldApp = $this->waiting($rep, 'older');      // did not say
        $done = $this->waiting($rep, 'ready');
        $done->approve($this->manager());

        $groups = collect(SalesInsights::waitingGroups())->keyBy('key');

        $this->assertSame(['ready', 'nobill', 'photo', 'older'], $groups->keys()->all(), 'the order of the groups');
        $this->assertSame([$older->id, $newer->id], $groups['ready']['rows']->pluck('id')->all(), 'oldest first');
        $this->assertSame(2, $groups['ready']['count']);
        $this->assertSame(300000, $groups['ready']['paise']);
        $this->assertSame([$nobill->id], $groups['nobill']['rows']->pluck('id')->all());
        $this->assertSame([$photo->id], $groups['photo']['rows']->pluck('id')->all(), 'with_bill but no photo is NOT ready');
        $this->assertSame([$oldApp->id], $groups['older']['rows']->pluck('id')->all(), 'no bill answer = older app');
        $this->assertStringContainsString('1.5', $groups['older']['text'], 'the older group names the app version');
        $this->assertTrue($groups['ready']['can_approve_all']);
        $this->assertFalse($groups['nobill']['can_approve_all'] || $groups['photo']['can_approve_all'] || $groups['older']['can_approve_all']);
        $this->assertNotContains($done->id, collect($groups)->flatMap(fn ($g) => $g['rows']->pluck('id'))->all(), 'a reviewed expense is not in the queue');
    }

    public function test_the_filters_narrow_the_queue_and_the_search_finds_a_rep_or_a_note(): void
    {
        $a = $this->rep('AB-0001', '9000000001', name: 'Ravi Sharma');
        $b = $this->rep('AB-0002', '9000000002', name: 'Sunita Rao');
        $fuel = $this->waiting($a, 'ready', ['category' => 'fuel', 'note' => 'Petrol Alwar']);
        $food = $this->waiting($b, 'nobill', ['category' => 'food', 'work_area' => 'outstation']);

        $ids = fn (array $f) => SalesInsights::expenseQuery($f, SalesExpense::PENDING)->pluck('id')->all();

        $this->assertSame([$fuel->id], $ids(['rep' => $a->user_id]));
        $this->assertSame([$food->id], $ids(['category' => 'food']));
        $this->assertSame([$food->id], $ids(['area' => 'outstation']));
        $this->assertSame([$food->id], $ids(['bill' => 'without_bill']));
        $this->assertSame([$fuel->id], $ids(['q' => 'alwar']), 'search in the note');
        $this->assertSame([$food->id], $ids(['q' => 'Sunita']), 'search by the rep name');
        $this->assertSame([], $ids(['q' => '100%']), 'a percent sign is text, not a wildcard');
    }

    // ---- flags ---------------------------------------------------------------------------------------------------

    public function test_an_amount_three_times_the_usual_is_flagged_only_with_enough_history(): void
    {
        $rep = $this->rep('AB-0001', '9000000001', name: 'Ravi Sharma');
        $day = now(config('sales.timezone'))->toDateString();

        foreach ([80000, 90000, 100000, 100000, 120000] as $i => $paise) {   // five earlier travel expenses: the middle is 1000
            $this->storedExpense($rep, ['category' => 'travel', 'amount_paise' => $paise, 'expense_date' => now()->subDays($i + 1)->toDateString(), 'status' => 'approved']);
        }
        $this->storedExpense($rep, ['category' => 'food', 'amount_paise' => 1, 'expense_date' => $day, 'status' => 'approved']);   // another category: ignored
        $big = $this->waiting($rep, 'ready', ['category' => 'travel', 'amount_paise' => 340000]);
        $normal = $this->waiting($rep, 'ready', ['category' => 'travel', 'amount_paise' => 290000]);

        $flags = SalesInsights::expenseFlags($big);
        $this->assertSame('higher', $flags[0]['key']);
        $this->assertStringContainsString('3.4 times', $flags[0]['text']);
        $this->assertSame([], SalesInsights::expenseFlags($normal), 'under three times the middle: no flag');

        // fewer than five earlier ones: no flag, however big
        $other = $this->rep('AB-0002', '9000000002');
        foreach ([100000, 100000, 100000, 100000] as $i => $paise) {
            $this->storedExpense($other, ['category' => 'travel', 'amount_paise' => $paise, 'expense_date' => now()->subDays($i + 1)->toDateString(), 'status' => 'approved']);
        }
        $this->assertSame([], SalesInsights::expenseFlags($this->waiting($other, 'ready', ['category' => 'travel', 'amount_paise' => 900000])));
    }

    public function test_other_waiting_expenses_of_the_same_outstation_trip_are_pointed_out(): void
    {
        $rep = $this->rep('AB-0001', '9000000001');
        $one = $this->waiting($rep, 'ready', ['work_area' => 'outstation', 'expense_date' => '2026-10-24']);
        $this->waiting($rep, 'nobill', ['work_area' => 'outstation', 'expense_date' => '2026-10-25']);
        $this->waiting($rep, 'ready', ['work_area' => 'outstation', 'expense_date' => '2026-10-23']);
        $this->waiting($rep, 'ready', ['work_area' => 'local', 'expense_date' => '2026-10-24']);   // local: not the trip

        $flags = SalesInsights::expenseFlags($one);

        $this->assertSame('trip', $flags[0]['key']);
        $this->assertStringContainsString('2 more expenses', $flags[0]['text']);
        $this->assertLessThanOrEqual(2, count($flags));
    }

    // ---- period, counts, context ----------------------------------------------------------------------------------

    public function test_the_period_and_its_counts_follow_business_days(): void
    {
        $rep = $this->rep('AB-0001', '9000000001');
        $manager = $this->manager();
        foreach (['2026-09-30' => 1, '2026-10-01' => 2, '2026-10-31' => 3, '2026-11-01' => 4] as $date => $paise) {
            $e = $this->storedExpense($rep, ['expense_date' => $date, 'amount_paise' => $paise * 100]);
            $e->approve($manager);
        }
        $waiting = $this->waiting($rep, 'ready', ['expense_date' => '2026-09-05']);   // waiting counts every month

        $p = SalesInsights::expensePeriod('2026-10');
        $this->assertSame('2026-10-01', $p['from']->toDateString());
        $this->assertSame('2026-10-31', $p['to']->toDateString());
        $this->assertSame('October 2026', $p['label']);

        $c = SalesInsights::expenseCounts($p['from'], $p['to']);
        $this->assertSame(2, $c['approved'], 'the first and the last day of the month count, the day before and after do not');
        $this->assertSame(2, $c['all']);
        $this->assertSame(1, $c['waiting']);
        $this->assertNotNull($waiting);

        $ctx = SalesInsights::repExpenseContext($rep->user_id, $p['from'], $p['to']);
        $this->assertSame(500, $ctx['spend_paise']);
        $this->assertSame(2, $ctx['approved']);
        $this->assertSame(1, $ctx['waiting']);

        // a month that is not a month falls back to this one; a custom range wins and is put in order
        $this->assertSame(now(config('sales.timezone'))->format('F Y'), SalesInsights::expensePeriod('nonsense')['label']);
        $custom = SalesInsights::expensePeriod('2026-10', '2026-10-20', '2026-10-05');
        $this->assertTrue($custom['custom']);
        $this->assertSame('2026-10-05', $custom['from']->toDateString());
    }

    // ---- the page -------------------------------------------------------------------------------------------------

    public function test_the_queue_page_opens_for_a_manager_and_not_without_the_right(): void
    {
        $rep = $this->rep();
        $this->waiting($rep, 'ready', ['note' => 'Taxi to the plant', 'amount_paise' => 340000]);

        $this->actingAs($this->viewerOf(['sales-meetings']));
        $this->get(SalesExpenseResource::getUrl('index'))->assertForbidden();

        $this->actingAs($rep->user)->get('/admin/sales-expenses')->assertForbidden();

        $this->actingAs($this->manager());
        $this->get(SalesExpenseResource::getUrl('index'))->assertOk();
        Livewire::test(ListSalesExpenses::class)->assertSee('Expenses')->assertSee('Ready to approve')->assertSee('Taxi to the plant')->assertSee('₹3,400')->assertSee('1 waiting');
    }

    public function test_tab_filters_period_and_selection_live_in_the_address(): void
    {
        $rep = $this->rep();
        $e = $this->waiting($rep, 'ready');
        $this->actingAs($this->manager());

        Livewire::withQueryParams(['tab' => 'approved', 'rep' => (string) $rep->user_id, 'category' => 'fuel', 'area' => 'local', 'bill' => 'with_bill', 'period' => '2026-09', 'q' => 'taxi', 'expense' => $e->uuid])
            ->test(ListSalesExpenses::class)
            ->assertSet('tab', 'approved')->assertSet('rep', (string) $rep->user_id)->assertSet('category', 'fuel')->assertSet('area', 'local')
            ->assertSet('bill', 'with_bill')->assertSet('period', '2026-09')->assertSet('search', 'taxi')->assertSet('expense', $e->uuid);

        // whatever the address says, unknown values fall back
        Livewire::withQueryParams(['tab' => 'nonsense', 'rep' => 'x', 'category' => 'rm -rf', 'period' => 'never'])->test(ListSalesExpenses::class)->assertSee('Ready to approve');

        // nothing waiting: the default tab is All
        SalesExpense::query()->forceDelete();
        Livewire::test(ListSalesExpenses::class)->assertSee('Nothing here')->assertDontSee('Ready to approve');
    }

    public function test_picking_a_row_shows_its_detail_and_the_bill_only_through_the_authorised_route(): void
    {
        Storage::fake('local');
        $rep = $this->rep('AB-0001', '9876543210', name: 'Ravi Sharma');
        $with = $this->waiting($rep, 'ready', ['note' => 'Client visit', 'amount_paise' => 340000, 'category' => 'travel']);
        Storage::disk('local')->put($with->bill_path, 'jpegbytes');
        $missing = $this->waiting($rep, 'ready', ['note' => 'Lost photo', 'amount_paise' => 15000]);   // a path in the table, no file on the disk
        $nobill = $this->waiting($rep, 'nobill', ['note' => 'Auto fare']);
        $this->actingAs($this->manager());

        $page = Livewire::test(ListSalesExpenses::class)->call('selectExpense', $with->uuid);
        $html = $page->html();
        $this->assertStringContainsString(route('sales.bill', $with->uuid), $html, 'the photo is an <img> on the authorised route');
        $this->assertStringContainsString('alt="Bill photo, Travel, ₹3,400,', $html);
        $this->assertStringNotContainsString('data:image', $html, 'never inlined');
        $this->assertStringNotContainsString('/storage/', $html, 'never a public link');
        $this->assertStringContainsString('Open full size', $html);

        $page->call('selectExpense', $missing->uuid)->assertSee('Photo not here yet')->assertDontSee('Open full size');
        $page->call('selectExpense', $nobill->uuid)->assertSee('The rep said there is no bill.')->assertSee('Approve');

        $page->call('selectExpense', 'not-a-uuid')->assertSet('expense', $nobill->uuid);   // an unknown row is ignored
    }

    public function test_the_bill_route_shows_the_photo_to_a_manager_and_to_nobody_else(): void
    {
        Storage::fake('local');
        $e = $this->waiting($this->rep(), 'ready');
        Storage::disk('local')->put($e->bill_path, 'jpegbytes');

        $this->get(route('sales.bill', $e->uuid))->assertForbidden();
        $this->actingAs($this->manager())->get(route('sales.bill', $e->uuid))->assertOk();
    }

    // ---- decisions ------------------------------------------------------------------------------------------------

    public function test_approving_one_writes_the_same_log_and_moves_to_the_next_row(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $rep = $this->rep();
        $first = $this->waiting($rep, 'ready', ['expense_date' => '2026-10-01']);
        $second = $this->waiting($rep, 'ready', ['expense_date' => '2026-10-02']);

        $page = Livewire::test(ListSalesExpenses::class)->call('selectExpense', $first->uuid)->call('approveOne', $first->uuid);

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame($manager->id, $first->fresh()->reviewed_by);
        $this->assertNotNull($first->fresh()->reviewed_at);
        $this->assertSame($second->uuid, $page->get('expense'), 'the pane moves to the next waiting row');
        $this->assertSame(1, DB::table('activity_logs')->where('user_id', $manager->id)->where('subject_type', SalesExpense::class)->where('subject_id', $first->id)->count());

        // a second click on an expense that is already decided does nothing and does not fail
        $page->call('approveOne', $first->uuid);
        $this->assertSame(1, DB::table('activity_logs')->where('subject_id', $first->id)->where('subject_type', SalesExpense::class)->count());
        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_rejecting_needs_a_reason_of_3_to_500_characters(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $e = $this->waiting($this->rep(), 'nobill');

        foreach (['', 'ab', str_repeat('x', 501)] as $bad) {
            Livewire::test(ListSalesExpenses::class)->mountAction('reject', ['uuid' => $e->uuid])->setActionData(['reason' => $bad])->callMountedAction()->assertHasActionErrors(['reason']);
            $this->assertSame('pending', $e->fresh()->status, 'no valid reason, no rejection');
        }

        Livewire::test(ListSalesExpenses::class)->mountAction('reject', ['uuid' => $e->uuid])->setActionData(['reason' => 'Bill is missing'])->callMountedAction()->assertHasNoActionErrors();

        $e = $e->fresh();
        $this->assertSame('rejected', $e->status);
        $this->assertSame('Bill is missing', $e->reject_reason);
        $this->assertSame($manager->id, $e->reviewed_by);
        $this->assertTrue(DB::table('activity_logs')->where('subject_type', SalesExpense::class)->where('subject_id', $e->id)->exists());

        // already rejected: a second reject changes nothing
        Livewire::test(ListSalesExpenses::class)->mountAction('reject', ['uuid' => $e->uuid])->setActionData(['reason' => 'Another reason'])->callMountedAction();
        $this->assertSame('Bill is missing', $e->fresh()->reject_reason);
    }

    public function test_the_reject_window_offers_quick_reasons(): void
    {
        $this->actingAs($this->manager());
        $e = $this->waiting($this->rep('AB-0001', '9000000001', name: 'Ravi Sharma'), 'nobill');

        $page = Livewire::test(ListSalesExpenses::class)->mountAction('reject', ['uuid' => $e->uuid]);
        $action = $page->instance()->getMountedAction();

        $this->assertSame('Reject this expense', $action->getModalHeading());
        $this->assertStringContainsString('Ravi Sharma', (string) $action->getModalDescription());
        foreach (['Bill is missing', 'Bill is not readable', 'Amount looks too high', 'Wrong category', 'Wrong date'] as $text) {
            $this->assertContains($text, array_values(__('sales_expenses.reasons')));
        }
        $this->assertStringContainsString('adds a new expense if they have the bill', __('sales_expenses.locked_note', ['name' => 'Ravi Sharma']));
    }

    public function test_approve_all_takes_only_expenses_with_a_bill_and_a_photo(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $rep = $this->rep();
        $ready1 = $this->waiting($rep, 'ready', ['amount_paise' => 700000]);
        $ready2 = $this->waiting($rep, 'ready', ['amount_paise' => 720000]);
        $nobill = $this->waiting($rep, 'nobill');
        $photo = $this->waiting($rep, 'photo');
        $older = $this->waiting($rep, 'older');
        $own = $this->waiting($rep, 'ready');
        $own->forceFill(['user_id' => $manager->id])->save();   // the manager's own expense: policy refuses it

        $page = Livewire::test(ListSalesExpenses::class)->mountAction('approveAll');
        $this->assertSame('Approve 2 expenses worth ₹14,200?', $page->instance()->getMountedAction()->getModalHeading(), 'the question shows what will be approved (not the own one)');

        // one more arrives while the question is open: it was not in the question, so it stays
        $late = $this->waiting($rep, 'ready');
        $page->callMountedAction();

        $this->assertSame(['approved', 'approved'], [$ready1->fresh()->status, $ready2->fresh()->status]);
        foreach ([$nobill, $photo, $older, $own, $late] as $untouched) {
            $this->assertSame('pending', $untouched->fresh()->status);
        }
        $this->assertSame(2, DB::table('activity_logs')->where('user_id', $manager->id)->where('subject_type', SalesExpense::class)->count());
    }

    public function test_ticked_rows_approve_only_what_is_safe_and_skip_the_rest(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $rep = $this->rep();
        $ready = $this->waiting($rep, 'ready');
        $nobill = $this->waiting($rep, 'nobill');
        $done = $this->waiting($rep, 'ready');
        $done->reject($manager, 'No bill');

        $result = ExpenseApproval::approveWithBill($manager, [$ready->id, $nobill->id, $done->id, $ready->id]);

        $this->assertSame(['approved' => 1, 'skipped' => 2], $result);
        $this->assertSame('approved', $ready->fresh()->status);
        $this->assertSame('pending', $nobill->fresh()->status, 'a no-bill expense is never approved in bulk');
        $this->assertSame('rejected', $done->fresh()->status, 'a decision already made is not overwritten');

        Livewire::test(ListSalesExpenses::class)->set('selected', [$nobill->uuid])->mountAction('approveSelected')->callMountedAction();
        $this->assertSame('pending', $nobill->fresh()->status);
    }

    public function test_nothing_is_decided_without_the_right_or_on_ones_own_expense(): void
    {
        $manager = $this->manager();
        $mine = $this->waiting($this->rep(), 'ready');
        $mine->forceFill(['user_id' => $manager->id])->save();
        $other = $this->waiting($this->rep('AB-0002', '9000000002'), 'ready');

        $this->actingAs($manager);
        Livewire::test(ListSalesExpenses::class)->call('approveOne', $mine->uuid);
        $this->assertSame('pending', $mine->fresh()->status, 'never one\'s own expense');

        $this->actingAs($this->viewerOf(['sales-reps']));
        $this->get(SalesExpenseResource::getUrl('index'))->assertForbidden();
        $this->assertSame('pending', $other->fresh()->status);
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        ExpenseApproval::approve(auth()->user(), $other->id);
    }

    // ---- the contract with the phone ---------------------------------------------------------------------------------

    public function test_the_phone_gets_the_decision_and_can_no_longer_change_the_expense(): void
    {
        $manager = $this->manager();
        $rep = $this->rep();
        $headers = $this->headers($rep);
        $approved = $this->expense(['bill_type' => 'with_bill', 'work_area' => 'local', 'updated_at' => $this->moment('-1 hour')]);
        $rejected = $this->expense(['bill_type' => 'without_bill', 'work_area' => 'local', 'updated_at' => $this->moment('-1 hour')]);
        $this->push($headers, ['expenses' => [$approved, $rejected]])->assertOk();

        $this->actingAs($manager);
        Livewire::test(ListSalesExpenses::class)->call('approveOne', SalesExpense::where('uuid', $approved['uuid'])->value('uuid'));
        Livewire::test(ListSalesExpenses::class)->mountAction('reject', ['uuid' => $rejected['uuid']])->setActionData(['reason' => 'Bill is missing'])->callMountedAction();
        auth()->logout();

        $pull = collect($this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json('expenses'))->keyBy('uuid');
        $this->assertSame('approved', $pull[$approved['uuid']]['status']);
        $this->assertNotNull($pull[$approved['uuid']]['reviewed_at']);
        $this->assertSame('rejected', $pull[$rejected['uuid']]['status']);
        $this->assertSame('Bill is missing', $pull[$rejected['uuid']]['reject_reason']);

        $edit = $this->push($headers, ['expenses' => [array_merge($approved, ['amount_paise' => 1, 'updated_at' => $this->moment()])]]);
        $this->assertSame('LOCKED', $edit->json('results.expenses.0.code'));
        $this->assertSame(45000, SalesExpense::where('uuid', $approved['uuid'])->value('amount_paise'));
    }

    // ---- bounded queries ---------------------------------------------------------------------------------------------

    public function test_the_page_asks_the_database_the_same_number_of_times_for_10_and_200_waiting_expenses(): void
    {
        $reps = [$this->rep('AB-0001', '9000000001'), $this->rep('AB-0002', '9000000002'), $this->rep('AB-0003', '9000000003')];
        $make = function (int $n) use ($reps) {
            for ($i = 0; $i < $n; $i++) {
                $this->waiting($reps[$i % 3], ['ready', 'nobill', 'photo', 'older'][$i % 4], ['expense_date' => now()->subDays($i % 20)->toDateString()]);
            }
        };
        $this->actingAs($this->manager());
        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            Livewire::test(ListSalesExpenses::class);
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $make(10);
        $few = $count();
        $make(190);
        $many = $count();

        $this->assertSame($few, $many, 'no query per row');
    }
}
