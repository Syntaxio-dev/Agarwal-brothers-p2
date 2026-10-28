<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Filament\Pages\SalesManagement;
use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesProfile;
use App\Sales\Models\SalesRoute;
use App\Sales\Support\Money;
use App\Sales\Support\SalesInsights;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The "Today first" Sales management page: where everybody is today, what needs a word, every figure against the period before, and the
 * approve actions. Days are business days (Asia/Kolkata): a meeting at 11:30 pm local is that day's, whatever the date is in UTC.
 */
class SalesManagementPageTest extends SalesTestCase
{
    private function tz(): string
    {
        return config('sales.timezone');
    }

    /** A local moment today (or on `$date`) at hh:mm. */
    private function local(string $time = '12:00', ?string $date = null): CarbonImmutable
    {
        return CarbonImmutable::parse(($date ?? CarbonImmutable::now($this->tz())->toDateString()) . ' ' . $time, $this->tz());
    }

    private function meeting_(SalesProfile $rep, CarbonImmutable $at, array $over = []): SalesMeeting
    {
        $status = $over['status'] ?? 'planned';

        return SalesMeeting::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => $rep->user_id,
            'client_name' => 'Client ' . Str::random(4),
            'meeting_at' => $at->utc(),
            'status' => $status,
            'scheduled_at' => $status === 'done' ? null : $at->utc(),
            'client_updated_at' => now(),
        ], $over));
    }

    private function enquiry_(SalesProfile $rep, array $over = []): SalesEnquiry
    {
        return SalesEnquiry::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => $rep->user_id,
            'client_name' => 'JK Cement',
            'status' => 'new',
            'items' => [['product' => 'Muffle Furnace', 'brands' => '', 'quantity' => 1, 'budget_paise' => null]],
            'opened_at' => now(),
            'client_updated_at' => now(),
        ], $over));
    }

    private function synced(SalesProfile $rep, ?CarbonImmutable $at = null): SalesProfile
    {
        $rep->forceFill(['last_sync_at' => ($at ?? now())->utc()])->save();

        return $rep;
    }

    /** A waiting expense that says it has a bill AND whose photo has reached the office (the only kind "approve all" takes). */
    private function photoExpense(SalesProfile $profile, array $over = []): SalesExpense
    {
        $e = $this->storedExpense($profile, $over);
        $e->forceFill(['bill_path' => "sales/bills/{$e->user_id}/{$e->uuid}.jpg", 'bill_mime' => 'image/jpeg'])->save();

        return $e;
    }

    // ---- money -------------------------------------------------------------------------------------------------

    public function test_short_money_reads_like_the_design(): void
    {
        $cases = [
            0 => '₹0', 85000 => '₹850', 99900 => '₹999', 6200000 => '₹62K', 6250000 => '₹62.5K', 48230000 => '₹4.82L', 386000000 => '₹38.6L',
            12000000000 => '₹12Cr', 120000000 => '₹12L', 9996000 => '₹1L', 100000 => '₹1K', -6200000 => '-₹62K',
        ];

        foreach ($cases as $paise => $text) {
            $this->assertSame($text, Money::short($paise), (string) $paise);
        }
        $this->assertSame('₹1.2Cr', Money::short(1_200_000_000));
        $this->assertSame('₹4.82L', Money::short(48_230_000));
    }

    // ---- change against the period before -------------------------------------------------------------------

    public function test_compare_gives_percent_new_nothing_and_whether_it_is_good_news(): void
    {
        $up = SalesInsights::compare(110, 100);
        $this->assertSame([10, 'up', 'ok'], [$up['delta'], $up['dir'], $up['tone']]);

        $spendUp = SalesInsights::compare(110, 100, upIsGood: false);
        $this->assertSame('alert', $spendUp['tone'], 'more spending is not good news');
        $this->assertSame('ok', SalesInsights::compare(50, 100, upIsGood: false)['tone']);

        $down = SalesInsights::compare(50, 100);
        $this->assertSame([-50, 'down', 'alert'], [$down['delta'], $down['dir'], $down['tone']]);

        $this->assertSame('new', SalesInsights::compare(7, 0)['delta'], 'nothing before: "new", no arrow');
        $this->assertSame('flat', SalesInsights::compare(7, 0)['dir']);
        $this->assertNull(SalesInsights::compare(0, 0)['delta'], 'both zero: nothing to show');
        $this->assertSame(-100, SalesInsights::compare(0, 5)['delta']);
        $this->assertSame([0, 'flat', 'none'], [SalesInsights::compare(5, 5)['delta'], SalesInsights::compare(5, 5)['dir'], SalesInsights::compare(5, 5)['tone']]);
        $this->assertNull(SalesInsights::compare(null, 5)['delta'], 'no value (nothing decided): nothing to compare');
        $this->assertSame('new', SalesInsights::compare(40, null)['delta']);
        $this->assertSame(33, SalesInsights::compare(4, 3)['delta'], 'rounded to a whole percent');
    }

    public function test_the_win_rate_needs_decided_enquiries(): void
    {
        $rep = $this->rep();
        $this->assertNull(SalesInsights::winRate(30)['rate'], 'nothing decided: no rate');
        $this->assertSame(0, SalesInsights::winRate(30)['decided']);

        $this->enquiry_($rep, ['status' => 'new']);   // open: not decided
        $this->enquiry_($rep, ['status' => 'won', 'closed_at' => now()]);
        $this->enquiry_($rep, ['status' => 'won', 'closed_at' => now()]);
        $this->enquiry_($rep, ['status' => 'lost', 'closed_at' => now()]);
        $this->enquiry_($rep, ['status' => 'won', 'closed_at' => now()->subDays(60)]);   // outside the window

        $this->assertSame(['won' => 2, 'lost' => 1, 'decided' => 3, 'rate' => 67], SalesInsights::winRate(30));
    }

    public function test_the_six_figures_are_compared_with_the_days_just_before(): void
    {
        $rep = $this->rep();
        $in = fn (int $daysAgo) => now()->subDays($daysAgo);   // 0..29 = this window, 30..59 = the one before

        $this->storedExpense($rep, ['amount_paise' => 200000, 'expense_date' => $in(2)->setTimezone($this->tz())->toDateString()]);
        $this->storedExpense($rep, ['amount_paise' => 100000, 'expense_date' => $in(40)->setTimezone($this->tz())->toDateString()]);

        foreach ([1, 2, 3] as $d) {
            $this->meeting_($rep, $this->local('11:00')->subDays($d), ['status' => 'done']);
        }
        foreach ([35, 36] as $d) {
            $this->meeting_($rep, $this->local('11:00')->subDays($d), ['status' => 'done']);
        }
        $this->meeting_($rep, $this->local('11:00')->subDays(2), ['status' => 'planned']);   // not a meeting that happened

        foreach ([1, 2] as $d) {
            $this->enquiry_($rep, ['opened_at' => $in($d)]);
        }
        $this->enquiry_($rep, ['opened_at' => $in(45)]);

        foreach ([3 => 'won', 4 => 'won', 5 => 'won', 6 => 'lost', 40 => 'won', 41 => 'lost'] as $d => $status) {
            $this->enquiry_($rep, ['status' => $status, 'opened_at' => $in($d + 1), 'closed_at' => $in($d)]);
        }

        foreach ([1, 2, 50] as $d) {
            SalesCall::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'direction' => 'outgoing', 'number' => '+919876543210', 'started_at' => $in($d), 'duration_seconds' => 60, 'client_updated_at' => now()]);
        }

        $c = SalesInsights::summaryCompared(30);

        $this->assertSame([200000, 100000, 100, 'alert'], [$c['spent']['value'], $c['spent']['prev'], $c['spent']['delta'], $c['spent']['tone']]);
        $this->assertSame([3, 2, 50], [$c['meetings']['value'], $c['meetings']['prev'], $c['meetings']['delta']]);
        $this->assertSame([6, 3, 100], [$c['enquiries']['value'], $c['enquiries']['prev'], $c['enquiries']['delta']]);
        $this->assertSame([3, 1], [$c['won']['value'], $c['won']['prev']]);
        $this->assertSame([75, 50, 50], [$c['win_rate']['value'], $c['win_rate']['prev'], $c['win_rate']['delta']]);
        $this->assertSame([3, 4], [$c['win_rate']['won'], $c['win_rate']['decided']]);
        $this->assertSame([2, 1, 100], [$c['calls']['value'], $c['calls']['prev'], $c['calls']['delta']]);

        $without = SalesInsights::summaryCompared(30, withMoney: false);
        $this->assertArrayNotHasKey('spent', $without, 'no money for a manager who may not see expenses');
        $this->assertArrayHasKey('win_rate', $without);
    }

    // ---- the sparklines and the business day ----------------------------------------------------------------

    public function test_the_series_has_fourteen_days_and_counts_a_late_meeting_on_its_own_business_day(): void
    {
        $rep = $this->rep();
        $today = $this->local('00:00');

        // 11:30 pm yesterday is yesterday's, 12:10 am today is today's: in UTC both are "yesterday"
        $this->meeting_($rep, $today->subDay()->setTime(23, 30), ['status' => 'done']);
        $this->meeting_($rep, $today->setTime(0, 0, 10), ['status' => 'done']);
        $this->storedExpense($rep, ['amount_paise' => 5000, 'expense_date' => $today->toDateString()]);

        $series = SalesInsights::series(14);

        foreach (['dates', 'spent', 'meetings', 'enquiries', 'won', 'win_rate', 'calls'] as $key) {
            $this->assertCount(14, $series[$key], $key);
        }
        $this->assertSame($today->toDateString(), $series['dates'][13], 'today is last');
        $this->assertSame($today->subDays(13)->toDateString(), $series['dates'][0]);
        $this->assertSame([1, 1], [$series['meetings'][12], $series['meetings'][13]], 'one on each business day');
        $this->assertSame(5000, $series['spent'][13]);
        $this->assertSame(0, array_sum($series['win_rate']), 'nothing decided: a flat line');
    }

    public function test_the_rolling_win_rate_looks_at_seven_days(): void
    {
        $rep = $this->rep();
        $today = $this->local('12:00');

        $this->enquiry_($rep, ['status' => 'won', 'closed_at' => $today->subDays(1)->utc()]);
        $this->enquiry_($rep, ['status' => 'lost', 'closed_at' => $today->subDays(1)->utc()]);
        $this->enquiry_($rep, ['status' => 'won', 'closed_at' => $today->subDays(9)->utc()]);

        $series = SalesInsights::series(14);
        $this->assertSame(50, $series['win_rate'][12], 'yesterday: 1 of 2 in the 7 days to then');
        $this->assertSame(50, $series['win_rate'][13]);
        $this->assertSame(100, $series['win_rate'][4], '9 days ago: one won in its week');
        $this->assertSame(100, $series['win_rate'][11], 'a week with nothing decided carries the last known rate instead of dipping to 0%');
    }

    // ---- today in the field -----------------------------------------------------------------------------------

    public function test_today_in_the_field_draws_each_reps_day(): void
    {
        $now = $this->local('11:00');
        $a = $this->synced($this->rep('REP001', '9000000001', name: 'Rahul Verma'), $now->subMinutes(5));
        $b = $this->synced($this->rep('REP002', '9000000002', name: 'Sunita Rao'), $now->subDay());   // not synced today
        $c = $this->synced($this->rep('REP003', '9000000003', name: 'Dev Patel'), $now->subMinutes(30));

        $m1 = $this->meeting_($a, $this->local('09:00'), ['status' => 'done', 'client_name' => 'Sharma Scientific']);
        $m2 = $this->meeting_($a, $this->local('11:30'), ['client_name' => 'Bhiwadi Pharma']);
        $m3 = $this->meeting_($a, $this->local('14:00'), ['client_name' => 'Alone Labs']);
        $route = SalesRoute::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'route_date' => $now->toDateString(), 'title' => 'Bhiwadi run', 'start_time' => '09:00', 'stops_order' => [$m1->uuid, $m2->uuid], 'client_updated_at' => now()]);
        $m1->update(['route_uuid' => $route->uuid]);
        $m2->update(['route_uuid' => $route->uuid]);

        // a route of ANOTHER rep that names Rahul's third meeting must not join it
        $foreign = SalesRoute::create(['uuid' => (string) Str::uuid(), 'user_id' => $c->user_id, 'route_date' => $now->toDateString(), 'title' => 'Not yours', 'start_time' => '09:00', 'stops_order' => [$m3->uuid], 'client_updated_at' => now()]);
        $m3->update(['route_uuid' => $foreign->uuid]);

        $this->meeting_($c, $this->local('10:00'), ['status' => 'missed', 'client_name' => 'Missed Co']);

        $field = SalesInsights::todayField($now);
        $reps = collect($field['reps'])->keyBy('code');

        $this->assertSame(['low' => 480, 'high' => 1140], ['low' => $field['axis']['low'], 'high' => $field['axis']['high']], 'the default axis is 8 AM to 7 PM');
        $this->assertSame(27.27, $field['axis']['now_pct'], '11:00 on a 660 minute axis');
        $this->assertSame('8 AM', $field['axis']['hours'][0]['label']);

        $blocks = collect($reps['REP001']['blocks'])->keyBy('client');
        $this->assertSame(['done', 'next', 'planned'], [$blocks['Sharma Scientific']['state'], $blocks['Bhiwadi Pharma']['state'], $blocks['Alone Labs']['state']]);
        $this->assertSame(9.09, $blocks['Sharma Scientific']['left'], '9:00 is 60 minutes in');
        $this->assertSame(9.09, $blocks['Sharma Scientific']['width'], 'an hour when the phone does not know the end');
        $this->assertSame('11:30 am', $blocks['Bhiwadi Pharma']['time']);

        $this->assertCount(1, $reps['REP001']['lines'], "one route, the other rep's route does not count");
        $this->assertSame(['Bhiwadi run', 2], [$reps['REP001']['lines'][0]['title'], $reps['REP001']['lines'][0]['stops']]);
        $this->assertNull($blocks['Alone Labs']['route_uuid']);

        $this->assertSame(['field', 'In field · Bhiwadi run'], [$reps['REP001']['state'], $reps['REP001']['status_line']]);
        $this->assertSame('stale', $reps['REP002']['state']);
        $this->assertStringStartsWith('Not synced since yesterday ', $reps['REP002']['status_line']);
        $this->assertTrue($reps['REP002']['no_plan']);
        $this->assertSame(['office', 'Office'], [$reps['REP003']['state'], $reps['REP003']['status_line']], 'nothing left to do today');

        $t = $field['tiles'];
        $this->assertSame(['total' => 4, 'done' => 1, 'to_go' => 2, 'missed' => 1], $t['meetings']);
        $this->assertSame(['total' => 1, 'on_the_way' => 1], $t['routes'], 'one stop done and one to go: on the way');
        $this->assertSame(['in' => 0, 'total' => 3], ['in' => $t['reports']['in'], 'total' => $t['reports']['total']]);
        $this->assertSame(['in' => 2, 'total' => 3, 'missing' => ['Sunita Rao']], $t['synced']);
    }

    public function test_the_axis_widens_for_an_early_or_late_meeting_but_stops_at_6_and_10(): void
    {
        $now = $this->local('12:00');
        $rep = $this->synced($this->rep(), $now);

        $this->meeting_($rep, $this->local('06:30'));
        $this->meeting_($rep, $this->local('19:30'));
        $field = SalesInsights::todayField($now);
        $this->assertSame([360, 1260], [$field['axis']['low'], $field['axis']['high']], '6 AM to 9 PM covers both');

        $this->meeting_($rep, $this->local('04:00'));
        $this->meeting_($rep, $this->local('23:30'));
        $field = SalesInsights::todayField($now);
        $this->assertSame([360, 1320], [$field['axis']['low'], $field['axis']['high']], 'never earlier than 6 AM or later than 10 PM');
        foreach ($field['reps'][0]['blocks'] as $b) {
            $this->assertGreaterThanOrEqual(0, $b['left']);
            $this->assertLessThanOrEqual(100.01, $b['left'] + $b['width'], 'a block never runs off the axis');
        }
    }

    public function test_the_known_end_of_a_meeting_sets_its_width_and_a_meeting_at_11_30_pm_stays_on_its_day(): void
    {
        $day = $this->local('12:00');
        $rep = $this->synced($this->rep(), $day);

        $this->meeting_($rep, $this->local('10:00'), ['client_name' => 'Long One', 'ended_at' => $this->local('12:00')->utc()]);
        $this->meeting_($rep, $this->local('23:30'), ['client_name' => 'Late One']);
        $this->meeting_($rep, $this->local('00:10', $day->addDay()->toDateString()), ['client_name' => 'Tomorrow One']);

        $blocks = collect(SalesInsights::todayField($day)['reps'][0]['blocks'])->keyBy('client');
        $this->assertSame(14.29, $blocks['Long One']['width'], 'two hours of a 14 hour axis (8 AM to 10 PM, widened by the 11:30 pm meeting)');
        $this->assertTrue($blocks->has('Late One'), '11:30 pm local is today');
        $this->assertFalse($blocks->has('Tomorrow One'), '12:10 am local tomorrow is not today, although in UTC it is the same date');

        $tomorrow = SalesInsights::todayField($day->addDay()->setTime(12, 0))['reps'][0]['blocks'];
        $this->assertSame(['Tomorrow One'], array_column($tomorrow, 'client'));
    }

    // ---- needs a word -----------------------------------------------------------------------------------------

    public function test_a_client_booked_twice_today_by_two_reps_is_flagged_but_not_for_one_rep(): void
    {
        $now = $this->local('11:00', '2026-10-28');
        $a = $this->synced($this->rep('REP001', '9000000001', name: 'Rahul Verma'), $now);
        $b = $this->synced($this->rep('REP002', '9000000002', name: 'Sunita Rao'), $now);
        $client = (string) Str::uuid();

        $this->meeting_($a, $this->local('11:00', '2026-10-28'), ['client_uuid' => $client, 'client_name' => 'Sharma Scientific']);
        $this->meeting_($b, $this->local('14:30', '2026-10-28'), ['client_uuid' => $client, 'client_name' => 'Sharma Scientific']);

        $flags = collect(SalesInsights::flags($now));
        $double = $flags->firstWhere('key', 'double_booked');
        $this->assertSame('Sharma Scientific is booked twice today', $double['title']);
        $this->assertSame('Rahul Verma at 11:00 am and Sunita Rao at 2:30 pm.', $double['text']);

        // the same rep twice, a missed one, another client: no clash
        $other = (string) Str::uuid();
        $this->meeting_($a, $this->local('09:00', '2026-10-28'), ['client_uuid' => $other]);
        $this->meeting_($a, $this->local('16:00', '2026-10-28'), ['client_uuid' => $other]);
        $third = (string) Str::uuid();
        $this->meeting_($a, $this->local('10:00', '2026-10-28'), ['client_uuid' => $third]);
        $this->meeting_($b, $this->local('12:00', '2026-10-28'), ['client_uuid' => $third, 'status' => 'missed']);

        $this->assertCount(1, array_filter(SalesInsights::flags($now), fn ($f) => $f['key'] === 'double_booked'), 'still only the one client');
    }

    public function test_a_missed_visit_is_flagged_until_something_is_planned_with_that_client_again(): void
    {
        $now = $this->local('11:00', '2026-10-28');
        $a = $this->synced($this->rep('REP001', '9000000001', name: 'Rahul Verma'), $now);
        $this->meeting_($a, $this->local('15:00', '2026-10-27'), ['status' => 'missed', 'client_uuid' => $client = (string) Str::uuid(), 'client_name' => 'Bhiwadi Pharma']);
        $this->meeting_($a, $this->local('10:00', '2026-10-28'), ['status' => 'missed', 'client_uuid' => $other = (string) Str::uuid(), 'client_name' => 'Alwar Steel']);
        $this->meeting_($a, $this->local('10:00', '2026-10-20'), ['status' => 'missed', 'client_name' => 'Too Old']);   // before yesterday: not flagged

        $missed = array_values(array_filter(SalesInsights::flags($now), fn ($f) => $f['key'] === 'missed'));
        $this->assertCount(2, $missed);
        $this->assertSame(['Rahul Verma missed Bhiwadi Pharma', 'Rahul Verma missed Alwar Steel'], array_column($missed, 'title'));
        $this->assertStringContainsString('yesterday at 3:00 pm', $missed[0]['text']);
        $this->assertStringContainsString('today at 10:00 am', $missed[1]['text']);

        // planned again with Bhiwadi Pharma (later, the same rep): that one is no longer a problem
        $this->meeting_($a, $this->local('11:00', '2026-10-30'), ['client_uuid' => $client, 'client_name' => 'Bhiwadi Pharma']);
        $this->assertSame(['Rahul Verma missed Alwar Steel'], array_column(array_values(array_filter(SalesInsights::flags($now), fn ($f) => $f['key'] === 'missed')), 'title'));

        // another rep's plan with the same client does not help
        $b = $this->synced($this->rep('REP002', '9000000002'), $now);
        $this->meeting_($b, $this->local('12:00', '2026-10-30'), ['client_uuid' => $other, 'client_name' => 'Alwar Steel']);
        $this->assertCount(1, array_filter(SalesInsights::flags($now), fn ($f) => $f['key'] === 'missed'));
    }

    public function test_a_rep_with_no_plan_is_flagged_after_ten_on_a_working_day_only(): void
    {
        $wednesday = $this->local('11:00', '2026-10-28');
        $a = $this->synced($this->rep('REP001', '9000000001', name: 'Rahul Verma'), $wednesday);
        $b = $this->synced($this->rep('REP002', '9000000002', name: 'Sunita Rao'), $wednesday);
        $this->meeting_($b, $this->local('10:00', '2026-10-28'));   // Sunita has a plan
        $this->meeting_($a, $this->local('16:00', '2026-10-26'), ['status' => 'done']);

        $noPlan = fn ($now) => array_values(array_filter(SalesInsights::flags($now), fn ($f) => $f['key'] === 'no_plan'));

        $flags = $noPlan($wednesday);
        $this->assertCount(1, $flags);
        $this->assertSame('Rahul Verma has no plan today', $flags[0]['title']);
        $this->assertStringContainsString('Last logged visit 26 Oct', $flags[0]['text']);

        $this->assertSame([], $noPlan($this->local('09:30', '2026-10-28')), 'not before 10:00');
        $this->assertSame([], $noPlan($this->local('11:00', '2026-11-01')), 'not on a Sunday');
        $this->assertCount(2, $noPlan($this->local('11:00', '2026-10-31')), 'a Saturday is a working day (and nobody has a plan then)');

        $c = $this->synced($this->rep('REP003', '9000000003', name: 'New Rep'), $wednesday);
        $this->assertStringContainsString('No visit logged yet', collect($noPlan($wednesday))->firstWhere('title', 'New Rep has no plan today')['text']);
    }

    public function test_phones_that_have_not_synced_and_the_evening_reports_are_flagged(): void
    {
        $evening = $this->local('19:00', '2026-10-28');
        $a = $this->synced($this->rep('REP001', '9000000001', name: 'Rahul Verma'), $evening->subDay());
        $b = $this->synced($this->rep('REP002', '9000000002', name: 'Sunita Rao'), $evening->subMinutes(20));
        $c = $this->rep('REP003', '9000000003', name: 'Never Synced');
        SalesDailyReport::create(['uuid' => (string) Str::uuid(), 'user_id' => $b->user_id, 'report_date' => '2026-10-28', 'notes' => 'ok', 'figures' => [], 'submitted_at' => now(), 'client_updated_at' => now()]);

        $by = fn ($now, $key) => array_values(array_filter(SalesInsights::flags($now), fn ($f) => $f['key'] === $key));

        $stale = $by($evening, 'not_synced');
        $this->assertSame(["Rahul Verma's phone has not synced", "Never Synced's phone has not synced"], array_column($stale, 'title'));
        $this->assertStringStartsWith('Last sync yesterday', $stale[0]['text']);
        $this->assertSame('It has never synced.', $stale[1]['text']);

        $reports = $by($evening, 'reports');
        $this->assertCount(1, $reports);
        $this->assertStringContainsString('Rahul Verma', $reports[0]['text']);
        $this->assertStringContainsString('Never Synced', $reports[0]['text']);
        $this->assertStringNotContainsString('Sunita Rao', $reports[0]['text'], 'she has reported');

        $this->assertSame([], $by($this->local('17:59', '2026-10-28'), 'reports'), 'before 6 pm nothing about reports');
        $this->assertNotNull($a);
        $this->assertNotNull($c);
    }

    public function test_the_flags_come_in_a_fixed_order_and_at_most_six(): void
    {
        $now = $this->local('19:00', '2026-10-28');
        $reps = [];
        foreach (range(1, 6) as $i) {
            $reps[$i] = $this->synced($this->rep('REP00' . $i, '90000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT), name: "Rep Number $i"), $now->subDay());
        }
        $client = (string) Str::uuid();
        $this->meeting_($reps[1], $this->local('11:00', '2026-10-28'), ['client_uuid' => $client, 'client_name' => 'Twice Co']);
        $this->meeting_($reps[2], $this->local('12:00', '2026-10-28'), ['client_uuid' => $client, 'client_name' => 'Twice Co']);
        $this->meeting_($reps[3], $this->local('10:00', '2026-10-28'), ['status' => 'missed', 'client_name' => 'Missed Co']);

        $flags = SalesInsights::flags($now);

        $this->assertCount(6, $flags, 'at most six');
        $this->assertSame(['double_booked', 'missed'], array_slice(array_column($flags, 'key'), 0, 2), 'booked twice first, then missed');
        $keys = array_column($flags, 'key');
        $this->assertTrue(array_search('no_plan', $keys) > array_search('missed', $keys));
        $this->assertSame(['double_booked', 'missed', 'no_plan', 'no_plan', 'no_plan', 'not_synced'], $keys);
    }

    // ---- sources, results, not logged ---------------------------------------------------------------------------

    public function test_enquiry_sources_count_the_window_and_show_not_told_only_when_there_is_one(): void
    {
        $rep = $this->rep();
        $empty = SalesInsights::enquirySources(30);
        $this->assertSame(0, $empty['total']);
        $this->assertSame(['call', 'walkin', 'whatsapp', 'reference'], array_column($empty['items'], 'key'), 'no "Not told" row without one');

        $this->enquiry_($rep, ['source' => 'whatsapp']);
        $this->enquiry_($rep, ['source' => 'whatsapp']);
        $this->enquiry_($rep, ['source' => 'call']);
        $this->enquiry_($rep, ['source' => null]);
        $this->enquiry_($rep, ['source' => 'walkin', 'opened_at' => now()->subDays(60)]);   // outside the window

        $sources = SalesInsights::enquirySources(30);
        $items = collect($sources['items'])->keyBy('key');

        $this->assertSame(4, $sources['total']);
        $this->assertSame([2, 50], [$items['whatsapp']['count'], $items['whatsapp']['percent']]);
        $this->assertSame(0, $items['walkin']['count']);
        $this->assertSame(['Not told', 1], [$items['unknown']['label'], $items['unknown']['count']]);
        $this->assertSame(5, SalesInsights::enquirySources(90)['total'], 'a longer window sees the older one too');
    }

    public function test_visit_results_are_counted_for_visits_that_were_done(): void
    {
        $rep = $this->rep();
        $this->assertSame(0, SalesInsights::meetingResults(30)['total']);

        foreach (['order', 'order', 'price_asked', null] as $result) {
            $this->meeting_($rep, $this->local('11:00')->subDay(), ['status' => 'done', 'result' => $result]);
        }
        $this->meeting_($rep, $this->local('11:00')->subDay(), ['status' => 'planned']);   // not done
        $this->meeting_($rep, $this->local('11:00')->subDays(60), ['status' => 'done', 'result' => 'order']);   // outside the window

        $r = SalesInsights::meetingResults(30);
        $items = collect($r['items'])->keyBy('key');

        $this->assertSame([4, 2], [$r['total'], $r['max']]);
        $this->assertSame([2, 1, 0], [$items['order']['count'], $items['price_asked']['count'], $items['demo_booked']['count']]);
        $this->assertSame(['No result written', 1], [$items['none']['label'], $items['none']['count']]);
    }

    public function test_planned_visits_an_hour_late_are_not_logged(): void
    {
        $rep = $this->rep();
        $this->meeting_($rep, CarbonImmutable::now()->subHours(5), ['client_name' => 'Oldest']);
        $this->meeting_($rep, CarbonImmutable::now()->subHours(2), ['client_name' => 'Newer', 'status' => 'moved']);
        $this->meeting_($rep, CarbonImmutable::now()->subMinutes(20), ['client_name' => 'Only just started']);
        $this->meeting_($rep, CarbonImmutable::now()->subHours(3), ['client_name' => 'Written up', 'status' => 'done']);
        $this->meeting_($rep, CarbonImmutable::now()->addHour(), ['client_name' => 'Later']);

        $this->assertSame(['Oldest', 'Newer'], SalesInsights::notLoggedMeetings(5)->pluck('client_name')->all());
        $this->assertSame(1, SalesInsights::notLoggedMeetings(1)->count());
        $this->assertSame(2, SalesInsights::summary(30)['meetings_not_logged']);
    }

    public function test_a_routes_stops_come_from_its_own_rep_in_its_own_order(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $m1 = $this->meeting_($a, $this->local('09:00'), ['client_name' => 'First']);
        $m2 = $this->meeting_($a, $this->local('13:00'), ['client_name' => 'Second']);
        $m3 = $this->meeting_($a, $this->local('15:00'), ['client_name' => 'Missing from the order']);
        $theirs = $this->meeting_($b, $this->local('10:00'), ['client_name' => 'Not mine']);
        $route = SalesRoute::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'route_date' => $this->local()->toDateString(), 'title' => 'Run', 'start_time' => '09:00', 'stops_order' => [$m2->uuid, $m1->uuid], 'client_updated_at' => now()]);
        foreach ([$m1, $m2, $m3, $theirs] as $m) {
            $m->update(['route_uuid' => $route->uuid]);
        }

        $this->assertSame(['Second', 'First', 'Missing from the order'], $route->stops()->pluck('client_name')->all());
        $this->assertSame(['Second', 'First', 'Missing from the order'], $route->orderedStops(SalesMeeting::all())->pluck('client_name')->all(), "loaded meetings give the same answer, and the other rep's one is left out");
    }

    // ---- the page: permissions, tabs, approving ------------------------------------------------------------------

    private function viewerOf(array $access): User
    {
        config(['roles.test_viewer' => ['label' => 'Test viewer', 'description' => 'x', 'access' => $access]]);

        return User::factory()->create(['role' => 'test_viewer', 'is_active' => true]);
    }

    public function test_the_page_draws_for_a_manager_with_every_block(): void
    {
        $a = $this->synced($this->rep('REP001', '9000000001', name: 'Ravi Sharma'));
        $this->photoExpense($a, ['amount_paise' => 482300, 'bill_type' => 'with_bill']);
        $this->meeting_($a, $this->local('15:00'), ['client_name' => 'Acme Labs']);
        $this->enquiry_($a, ['source' => 'whatsapp']);

        $this->actingAs($this->manager());

        Livewire::test(SalesManagement::class)
            ->assertSee('Today in the field')->assertSee('Meetings today')->assertSee('Reps synced')
            ->assertSee('Needs your action')->assertSee('Needs a word')
            ->assertSee('Approvals')->assertSee('Overdue enquiries')->assertSee('Not logged')->assertSee('Follow-ups')->assertSee('Not reached')
            ->assertSee('Spent')->assertSee('Meetings done')->assertSee('New enquiries')->assertSee('Orders won')->assertSee('Win rate')->assertSee('Client calls')
            ->assertSee('Open enquiries by stage')->assertSee('Where new enquiries came from')->assertSee('WhatsApp')
            ->assertSee('What clients ask for')->assertSee('How visits ended')->assertSee('Spend by category')->assertSee('Local or outstation')
            ->assertSee('Client pool')->assertSee('Field reps')
            ->assertSee('Ravi Sharma')->assertSee('Acme Labs')->assertSee('₹4,823')
            ->assertSee('Approve all 1 with a bill')
            ->assertSee('Export report')
            ->assertSee('7 days')->assertSee('30 days')->assertSee('90 days');
    }

    public function test_a_manager_who_may_not_see_expenses_sees_no_money_and_cannot_approve(): void
    {
        $a = $this->synced($this->rep('REP001', '9000000001', name: 'Ravi Sharma'));
        $expense = $this->storedExpense($a, ['amount_paise' => 482300, 'bill_type' => 'with_bill', 'category' => 'fuel']);
        $this->meeting_($a, $this->local('15:00'), ['client_name' => 'Acme Labs']);

        $this->actingAs($this->viewerOf(['sales-dashboard', 'sales-meetings']));

        $page = Livewire::test(SalesManagement::class)
            ->assertSee('Today in the field')->assertSee('Acme Labs')->assertSee('Ravi Sharma')
            ->assertDontSee('₹')->assertDontSee('Spent')->assertDontSee('Spend by category')->assertDontSee('Local or outstation')
            ->assertDontSee('Export report')->assertDontSee('Approve')->assertDontSee('Approvals')->assertDontSee('Petrol')
            ->assertDontSee('Pending')
            ->assertSet('tab', 'approvals');   // the stored value, but what is drawn is the first tab this manager may see

        $page->call('setTab', 'approvals')->assertSet('tab', 'notlogged');
        // a refused call ends that request: every one is tried on a fresh page
        Livewire::test(SalesManagement::class)->call('approveExpense', $expense->id)->assertForbidden();
        Livewire::test(SalesManagement::class)->call('approveAllWithBill')->assertForbidden();
        Livewire::test(SalesManagement::class)->call('exportReport')->assertForbidden();
        $this->assertSame('pending', $expense->fresh()->status);
    }

    public function test_the_range_and_the_tabs_switch(): void
    {
        $a = $this->synced($this->rep());
        $this->meeting_($a, CarbonImmutable::now()->subHours(4), ['client_name' => 'Forgotten Co']);

        $this->actingAs($this->manager());

        Livewire::test(SalesManagement::class)
            ->call('setRange', 7)->assertSet('range', 7)
            ->call('setRange', 90)->assertSet('range', 90)
            ->call('setRange', 5)->assertSet('range', 30)
            ->call('setTab', 'notlogged')->assertSet('tab', 'notlogged')->assertSee('Forgotten Co')->assertSee('late')
            ->call('setTab', 'followups')->assertSet('tab', 'followups')
            ->call('setTab', 'reach')->assertSet('tab', 'reach')
            ->call('setTab', 'enquiries')->assertSet('tab', 'enquiries')
            ->call('setTab', 'nonsense')->assertSet('tab', 'approvals');
    }

    public function test_a_row_of_the_reps_table_opens_the_detail_in_a_slide_over(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Ravi Sharma');
        $a->forceFill(['device_model' => 'Pixel 7', 'app_version' => '1.7'])->save();
        $this->actingAs($this->manager());

        $page = Livewire::test(SalesManagement::class)
            ->call('selectRep', $a->id)->assertSet('selectedRep', $a->id)
            ->assertActionMounted('repDetail');

        // the slide-over is drawn as a separate piece of the page: read what it shows from the action
        $action = $page->instance()->getMountedAction();
        $html = $action->getModalContent()->render();
        $this->assertSame('Ravi Sharma', $action->getModalHeading());
        $this->assertStringContainsString('Pixel 7', $html);
        $this->assertStringContainsString('Open full rep page', $html);
    }

    public function test_approving_one_expense_uses_the_same_approve_and_writes_the_audit_trail(): void
    {
        $a = $this->rep();
        $expense = $this->storedExpense($a, ['amount_paise' => 50000]);
        $manager = $this->manager();
        $this->actingAs($manager);

        Livewire::test(SalesManagement::class)->call('approveExpense', $expense->id);

        $expense->refresh();
        $this->assertSame([SalesExpense::APPROVED, $manager->id], [$expense->status, $expense->reviewed_by]);
        $this->assertNotNull($expense->reviewed_at);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $manager->id, 'subject_type' => SalesExpense::class, 'subject_id' => $expense->id, 'action' => 'updated']);

        // deciding it twice, or an id that is not there, changes nothing and does not break the page
        $logs = DB::table('activity_logs')->count();
        Livewire::test(SalesManagement::class)->call('approveExpense', $expense->id)->call('approveExpense', 987654)->assertOk();
        $this->assertSame($logs, DB::table('activity_logs')->count());
    }

    public function test_a_rejected_expense_is_not_approved_from_the_page(): void
    {
        $a = $this->rep();
        $expense = $this->storedExpense($a, ['status' => 'rejected']);
        $this->actingAs($this->manager());

        Livewire::test(SalesManagement::class)->call('approveExpense', $expense->id);

        $this->assertSame('rejected', $expense->fresh()->status);
    }

    public function test_approve_all_asks_first_and_approves_only_expenses_with_a_bill(): void
    {
        $a = $this->rep();
        $with1 = $this->photoExpense($a, ['amount_paise' => 700000, 'bill_type' => 'with_bill']);
        $with2 = $this->photoExpense($a, ['amount_paise' => 720000, 'bill_type' => 'with_bill']);
        $without = $this->storedExpense($a, ['amount_paise' => 100000, 'bill_type' => 'without_bill']);
        $older = $this->storedExpense($a, ['amount_paise' => 100000]);   // an older phone: it did not say
        $done = $this->storedExpense($a, ['amount_paise' => 100000, 'bill_type' => 'with_bill', 'status' => 'rejected']);
        $manager = $this->manager();
        $this->actingAs($manager);

        $page = Livewire::test(SalesManagement::class)
            ->assertSee('Approve all 2 with a bill')
            ->mountAction('approveAll')->assertActionMounted('approveAll');

        $this->assertSame('Approve 2 expenses worth ₹14,200?', $page->instance()->getMountedAction()->getModalHeading());

        $this->assertSame('pending', $with1->fresh()->status, 'asking is not approving');

        $page->callMountedAction();

        $this->assertSame([SalesExpense::APPROVED, SalesExpense::APPROVED], [$with1->fresh()->status, $with2->fresh()->status]);
        $this->assertSame(['pending', 'pending', 'rejected'], [$without->fresh()->status, $older->fresh()->status, $done->fresh()->status], 'never an expense without a bill');
        $this->assertSame(2, DB::table('activity_logs')->where('user_id', $manager->id)->where('subject_type', SalesExpense::class)->count());
        $this->assertSame($manager->id, $with1->fresh()->reviewed_by);
    }

    public function test_approve_all_approves_what_the_question_showed_and_not_what_came_after(): void
    {
        $a = $this->rep();
        $first = $this->photoExpense($a, ['amount_paise' => 700000, 'bill_type' => 'with_bill']);
        $manager = $this->manager();
        $this->actingAs($manager);

        $page = Livewire::test(SalesManagement::class)->mountAction('approveAll');
        $this->assertSame('Approve 1 expense worth ₹7,000?', $page->instance()->getMountedAction()->getModalHeading());

        // another expense arrives while the question is open
        $late = $this->photoExpense($a, ['amount_paise' => 100000, 'bill_type' => 'with_bill']);
        $page->callMountedAction();

        $this->assertSame('approved', $first->fresh()->status);
        $this->assertSame('pending', $late->fresh()->status, 'it was not in the question');
    }

    public function test_approve_all_reaches_every_row_when_there_are_many(): void
    {
        $a = $this->rep();
        $ids = [];
        foreach (range(1, 12) as $i) {
            $ids[] = $this->photoExpense($a, ['amount_paise' => 10000 + $i, 'bill_type' => 'with_bill'])->id;
        }
        $this->actingAs($this->manager());

        Livewire::test(SalesManagement::class)->call('approveAllWithBill');

        $this->assertSame(0, SalesExpense::whereIn('id', $ids)->where('status', 'pending')->count());
    }

    public function test_money_is_not_in_the_rep_detail_of_a_manager_who_may_not_see_expenses(): void
    {
        $a = $this->rep();
        $this->storedExpense($a, ['amount_paise' => 123456, 'bill_type' => 'with_bill']);
        $this->actingAs($this->viewerOf(['sales-dashboard', 'sales-meetings', 'sales-reps']));

        $page = Livewire::test(SalesManagement::class)->call('selectRep', $a->id);
        $html = $page->instance()->getMountedAction()->getModalContent()->render();

        $this->assertStringNotContainsString('1,234', $html);
        $this->assertStringNotContainsString('Spent', $html);
    }

    public function test_short_money_never_shows_a_thousand_rupees_as_a_plain_number(): void
    {
        foreach ([99950 => '₹1K', 99999 => '₹1K', 99940 => '₹999', 99900 => '₹999', 100049 => '₹1K'] as $paise => $text) {
            $this->assertSame($text, Money::short($paise), (string) $paise);
        }
    }

    public function test_nobody_without_the_right_can_approve_all(): void
    {
        $a = $this->rep();
        $expense = $this->storedExpense($a, ['bill_type' => 'with_bill']);
        $this->actingAs($this->viewerOf(['sales-dashboard', 'sales-reports']));

        Livewire::test(SalesManagement::class)->assertActionHidden('approveAll')->call('approveAllWithBill')->assertForbidden();
        $this->assertSame('pending', $expense->fresh()->status);

        // a field rep never reaches the page at all
        $rep = User::query()->find($a->user_id);
        $this->actingAs($rep);
        $this->assertFalse(SalesManagement::canAccess());
    }

    // ---- the page is built from the insights, in a bounded number of queries ---------------------------------------

    public function test_the_page_asks_the_database_the_same_number_of_times_for_five_reps_and_fifty(): void
    {
        $this->actingAs($this->manager());

        $seed = function (int $from, int $to) {
            for ($i = $from; $i <= $to; $i++) {
                $rep = $this->synced($this->rep(sprintf('R%04d', $i), sprintf('91%08d', $i), name: "Rep $i"));
                $this->storedExpense($rep, ['amount_paise' => 1000 * $i, 'bill_type' => $i % 2 ? 'with_bill' : 'without_bill']);
                $m1 = $this->meeting_($rep, $this->local('10:00'), ['client_name' => "Client $i"]);
                $m2 = $this->meeting_($rep, $this->local('13:00'), ['client_name' => "Other $i"]);
                $route = SalesRoute::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'route_date' => $this->local()->toDateString(), 'title' => "Run $i", 'start_time' => '09:00', 'stops_order' => [$m1->uuid, $m2->uuid], 'client_updated_at' => now()]);
                $m1->update(['route_uuid' => $route->uuid]);
                $m2->update(['route_uuid' => $route->uuid]);
                $this->meeting_($rep, CarbonImmutable::now()->subHours(3), ['client_name' => "Late $i"]);
                $this->enquiry_($rep, ['next_followup_at' => now()->subDay(), 'source' => 'call']);
            }
        };

        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            Livewire::test(SalesManagement::class)->assertSee('Today in the field');
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $seed(1, 5);
        $few = $count();
        $seed(6, 50);
        $many = $count();

        $this->assertLessThanOrEqual($few + 2, $many, "5 reps: $few queries, 50 reps: $many queries (it must not grow with the reps)");
        $this->assertLessThan(120, $many);
    }

    public function test_the_page_source_has_the_accessible_controls_and_escapes_every_value(): void
    {
        $dir = resource_path('views/filament/pages');
        $source = file_get_contents($dir . '/sales-management.blade.php');
        foreach (glob($dir . '/sales-management/*.blade.php') as $file) {
            $source .= file_get_contents($file);
        }

        $this->assertStringNotContainsString('{!!', $source, 'every value goes through {{ }}');
        $this->assertStringNotContainsString('<script', $source, 'no inline script');
        $this->assertStringContainsString('aria-pressed', $source);
        $this->assertStringContainsString('aria-selected', $source);
        $this->assertStringContainsString('wire:keydown.enter', $source);
        $this->assertStringContainsString('scope="col"', $source);
        $this->assertStringContainsString('wire:poll.60s', $source, 'the field card refreshes itself');
        $this->assertSame(1, substr_count($source, 'wire:poll'), 'and only that card');
        $this->assertStringContainsString('aria-label="{{ $rep[\'name\'] }}: meetings today"', $source, 'the timeline has a text alternative per rep');

        $a = $this->rep('REP001', '9000000001', name: '<b>Bold</b> Rep');
        $this->meeting_($a, $this->local('11:00'), ['client_name' => '<i>Evil</i> Client']);
        $this->actingAs($this->manager());
        Livewire::test(SalesManagement::class)->assertDontSeeHtml('<b>Bold</b>')->assertDontSeeHtml('<i>Evil</i>')->assertSee('<b>Bold</b> Rep');
    }
}
