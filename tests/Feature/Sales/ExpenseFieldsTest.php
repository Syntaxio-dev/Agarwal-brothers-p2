<?php

namespace Tests\Feature\Sales;

use App\Sales\Models\SalesExpense;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Every expense says where the work was (local / outstation) and whether there is a bill; two new categories (parking, gifting).
 * Older phones send neither answer: that must keep working and must not wipe what is stored.
 */
class ExpenseFieldsTest extends SalesTestCase
{
    private function sent(array $headers, array $expense)
    {
        return $this->answer($this->push($headers, ['expenses' => [$expense]])->assertOk(), 'expenses', $expense['uuid']);
    }

    public function test_the_two_answers_and_the_new_categories_are_stored_and_come_back(): void
    {
        $headers = $this->headers($this->rep());

        foreach (['parking', 'gifting', 'fuel'] as $category) {
            $e = $this->expense(['category' => $category, 'work_area' => 'outstation', 'bill_type' => 'without_bill']);
            $this->assertSame('accepted', $this->sent($headers, $e)['status'], $category);
        }

        $stored = SalesExpense::where('category', 'parking')->firstOrFail();
        $this->assertSame('outstation', $stored->work_area);
        $this->assertSame('without_bill', $stored->bill_type);

        $pulled = collect($this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json('expenses'))->firstWhere('uuid', $stored->uuid);
        $this->assertSame('outstation', $pulled['work_area']);
        $this->assertSame('without_bill', $pulled['bill_type']);
    }

    public function test_a_wrong_answer_is_refused_and_a_missing_one_from_an_older_phone_is_fine(): void
    {
        $headers = $this->headers($this->rep());

        $bad = $this->expense(['work_area' => 'abroad']);
        $this->assertSame('INVALID', $this->sent($headers, $bad)['code']);
        $bad = $this->expense(['bill_type' => 'maybe']);
        $this->assertSame('INVALID', $this->sent($headers, $bad)['code']);
        $this->assertSame('INVALID', $this->sent($headers, $this->expense(['category' => 'jewellery']))['code']);

        // an older phone sends neither field
        $old = $this->expense();
        unset($old['work_area'], $old['bill_type']);
        $this->assertSame('accepted', $this->sent($headers, $old)['status']);
        $row = SalesExpense::where('uuid', $old['uuid'])->first();
        $this->assertNull($row->work_area);
        $this->assertNull($row->bill_type);
    }

    public function test_an_older_phone_editing_an_expense_does_not_wipe_the_answers(): void
    {
        $headers = $this->headers($this->rep());
        $e = $this->expense(['work_area' => 'local', 'bill_type' => 'with_bill']);
        $this->sent($headers, $e);

        $edit = $e;
        unset($edit['work_area'], $edit['bill_type']);
        $edit['note'] = 'edited by an old phone';
        $edit['updated_at'] = $this->moment('+1 minute');
        $this->assertSame('accepted', $this->sent($headers, $edit)['status']);

        $row = SalesExpense::where('uuid', $e['uuid'])->first();
        $this->assertSame('edited by an old phone', $row->note);
        $this->assertSame('local', $row->work_area);
        $this->assertSame('with_bill', $row->bill_type);
    }

    public function test_the_manager_sees_filters_and_exports_the_answers(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);
        $this->sent($headers, $this->expense(['work_area' => 'local', 'bill_type' => 'with_bill', 'amount_paise' => 10000]));
        $this->sent($headers, $this->expense(['work_area' => 'outstation', 'bill_type' => 'without_bill', 'amount_paise' => 30000]));
        $older = $this->expense(['amount_paise' => 5000]);
        unset($older['work_area'], $older['bill_type']);
        $this->sent($headers, $older);

        $this->actingAs($this->manager());

        $outstation = SalesExpense::where('work_area', 'outstation')->first();
        $local = SalesExpense::where('work_area', 'local')->first();
        // the queue's filters (work area, bill) are one query shared by the list and the export
        $byArea = SalesInsights::expenseQuery(['area' => 'outstation'])->pluck('id')->all();
        $this->assertSame([$outstation->id], $byArea);
        $this->assertNotContains($local->id, $byArea);
        $this->assertSame([$outstation->id], SalesInsights::expenseQuery(['bill' => 'without_bill'])->pluck('id')->all());
        $this->assertCount(1, SalesInsights::expenseQuery(['area' => 'none'])->get(), 'an older app did not say: Not told');

        $split = SalesInsights::expenseSplit(30);
        $this->assertSame(['local' => 10000, 'outstation' => 30000, 'unknown' => 5000], $split['work_area']);
        $this->assertSame(['with_bill' => 10000, 'without_bill' => 30000, 'unknown' => 5000], $split['bill']);

        $this->get('/admin/sales-management')->assertOk()->assertSee('Local or outstation')->assertSee('With or without bill');

        ob_start();
        \App\Sales\Support\SalesExporter::expenses(SalesExpense::query(), 'csv')->sendContent();
        $csv = ob_get_clean();
        $this->assertStringContainsString('Work area', $csv);
        $this->assertStringContainsString('Outstation (outside Jaipur)', $csv);
        $this->assertStringContainsString('Without bill', $csv);
        $this->assertStringContainsString('Petrol', $csv);
    }

    public function test_the_labels_read_well(): void
    {
        $this->assertSame('Petrol', SalesExpense::categoryLabel('fuel'));
        $this->assertSame('Parking charges', SalesExpense::categoryLabel('parking'));
        $this->assertSame('Miscellaneous', SalesExpense::categoryLabel('other'));
        $this->assertSame('Something', SalesExpense::categoryLabel('something'));
        $this->assertTrue(Str::contains(SalesExpense::WORK_AREAS['local'], 'Jaipur'));
        $this->assertSame(array_keys(SalesExpense::CATEGORY_LABELS), config('sales.expense_categories'));
    }
}
