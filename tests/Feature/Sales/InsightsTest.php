<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Support\Money;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Str;

class InsightsTest extends SalesTestCase
{
    private function today(): string
    {
        return SalesInsights::today()->toDateString();
    }

    private function daysAgo(int $n): string
    {
        return SalesInsights::today()->subDays($n)->toDateString();
    }

    public function test_money_uses_indian_grouping(): void
    {
        $this->assertSame('₹0.00', Money::rupees(0));
        $this->assertSame('₹0.05', Money::rupees(5));
        $this->assertSame('₹450.00', Money::rupees(45000));
        $this->assertSame('₹450', Money::rupees(45000, false));
        $this->assertSame('₹450.50', Money::rupees(45050, false));
        $this->assertSame('₹1,000.00', Money::rupees(100000));
        $this->assertSame('₹12,345.67', Money::rupees(1234567));
        $this->assertSame('₹1,23,456.50', Money::rupees(12345650));
        $this->assertSame('₹12,34,567.00', Money::rupees(123456700));
        $this->assertSame('-₹5.00', Money::rupees(-500));
    }

    public function test_the_headline_numbers(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');

        $this->storedExpense($a, ['amount_paise' => 10000, 'expense_date' => $this->today()]);
        $this->storedExpense($a, ['amount_paise' => 20000, 'expense_date' => $this->daysAgo(15), 'status' => 'approved']);
        $this->storedExpense($b, ['amount_paise' => 5000, 'expense_date' => $this->daysAgo(40)]);   // outside 30 days, inside 90

        $thirty = SalesInsights::summary(30);
        $this->assertSame(30000, $thirty['spend_paise']);
        $this->assertSame(2, $thirty['expense_count']);
        $this->assertSame(20000, $thirty['approved_paise']);
        $this->assertSame(2, $thirty['pending_count'], 'pending counts every pending expense, whatever its age');
        $this->assertSame(15000, $thirty['pending_paise']);
        $this->assertSame(2, $thirty['reps_active']);

        $this->assertSame(10000, SalesInsights::summary(7)['spend_paise']);
        $this->assertSame(35000, SalesInsights::summary(90)['spend_paise']);
    }

    public function test_daily_has_one_entry_per_day_and_zero_fill(): void
    {
        $a = $this->rep();
        $this->storedExpense($a, ['amount_paise' => 10000, 'expense_date' => $this->today()]);
        $this->storedExpense($a, ['amount_paise' => 2500, 'expense_date' => $this->today()]);

        $daily = SalesInsights::daily(7);

        $this->assertCount(7, $daily);
        $this->assertSame($this->today(), end($daily)['date']);
        $this->assertSame(12500, end($daily)['paise']);
        $this->assertSame(0, $daily[0]['paise']);
        $this->assertSame(array_unique(array_column($daily, 'date')), array_column($daily, 'date'));
    }

    public function test_categories_are_biggest_first(): void
    {
        $a = $this->rep();
        $this->storedExpense($a, ['category' => 'food', 'amount_paise' => 1000, 'expense_date' => $this->today()]);
        $this->storedExpense($a, ['category' => 'fuel', 'amount_paise' => 9000, 'expense_date' => $this->today()]);
        $this->storedExpense($a, ['category' => 'fuel', 'amount_paise' => 1000, 'expense_date' => $this->today()]);

        $list = SalesInsights::byCategory(7);

        $this->assertSame(['fuel', 'food'], array_column($list, 'category'));
        $this->assertSame([10000, 1000], array_column($list, 'paise'));
        $this->assertSame([2, 1], array_column($list, 'count'));
    }

    public function test_reps_who_have_not_synced_today_are_listed(): void
    {
        $fresh = $this->rep('REP001', '9000000001');
        $old = $this->rep('REP002', '9000000002');
        $never = $this->rep('REP003', '9000000003');
        $off = $this->rep('REP004', '9000000004', active: false);

        $fresh->update(['last_sync_at' => now()]);
        $old->update(['last_sync_at' => now()->subDays(2)]);

        $codes = SalesInsights::staleReps()->pluck('employee_code')->all();

        $this->assertEqualsCanonicalizing(['REP002', 'REP003'], $codes);
        $this->assertSame(2, SalesInsights::summary(30)['reps_not_synced_today']);
    }

    public function test_rep_figures_and_follow_ups(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Ravi');
        $this->storedExpense($a, ['amount_paise' => 30000, 'expense_date' => $this->today()]);
        $this->storedExpense($a, ['amount_paise' => 12000, 'expense_date' => $this->today(), 'status' => 'approved']);

        SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'client_name' => 'Soon', 'meeting_at' => now()->subDay(), 'next_followup_at' => now()->addDays(2), 'client_updated_at' => now()]);
        SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'client_name' => 'Late', 'meeting_at' => now()->subDays(9), 'next_followup_at' => now()->subDay(), 'client_updated_at' => now()]);
        SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'client_name' => 'Far', 'meeting_at' => now()->subDay(), 'next_followup_at' => now()->addDays(30), 'client_updated_at' => now()]);
        SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'client_name' => 'None', 'meeting_at' => now()->subDay(), 'client_updated_at' => now()]);

        $rep = SalesInsights::reps(30)->first();
        $this->assertSame('Ravi', $rep['name']);
        $this->assertSame(42000, $rep['spend_paise']);
        $this->assertSame(1, $rep['pending']);
        $this->assertSame(4, $rep['meetings']);

        $this->assertSame(['Late', 'Soon'], SalesInsights::followups()->pluck('client_name')->all(), 'overdue first, then the next 7 days; far ones and ones without a date are left out');
        $this->assertSame(2, SalesInsights::summary(30)['followups_due']);
    }

    public function test_deleted_records_do_not_count(): void
    {
        $a = $this->rep();
        $kept = $this->storedExpense($a, ['amount_paise' => 10000]);
        $gone = $this->storedExpense($a, ['amount_paise' => 90000]);
        $gone->delete();

        $this->assertSame(10000, SalesInsights::summary(30)['spend_paise']);
        $this->assertSame(1, SalesInsights::summary(30)['pending_count']);
        $this->assertSame([$kept->id], SalesInsights::pendingExpenses()->pluck('id')->all());
    }
}
