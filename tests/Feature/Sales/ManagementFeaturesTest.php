<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Pages\SalesManagement;
use App\Sales\Models\SalesBugReport;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesProspectNote;
use App\Sales\Models\SalesTask;
use App\Sales\Support\SalesInsights;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * The Sales management page follows the newer features: the tasks the manager gave (stuck, late, not opened), the prospects waiting for a call
 * back, bug reports waiting, and the two new columns of the reps table. Every figure comes from SalesInsights; the page only formats.
 */
class ManagementFeaturesTest extends SalesTestCase
{
    private function task(int $userId, array $over = []): SalesTask
    {
        return SalesTask::create(array_merge(['assigned_to' => $userId, 'title' => 'Task ' . Str::random(4), 'due_at' => now()->addDays(3)], $over));
    }

    private function prospect(int $userId, string $name, string $outcome, $followup): SalesProspect
    {
        $p = SalesProspect::create(['uuid' => (string) Str::uuid(), 'user_id' => $userId, 'name' => $name, 'territory' => 'Bagru', 'client_updated_at' => now()]);
        SalesProspectNote::create([
            'uuid' => (string) Str::uuid(), 'prospect_uuid' => $p->uuid, 'user_id' => $userId, 'outcome' => $outcome, 'remark' => 'x',
            'next_followup_at' => $followup, 'noted_at' => now()->subHour(), 'client_updated_at' => now(),
        ]);

        return $p;
    }

    public function test_the_tasks_that_need_the_manager_are_the_stuck_the_late_and_the_unopened(): void
    {
        $rep = $this->rep('REP001', '9000000001', name: 'Ayush Sharma');
        $blocked = $this->task($rep->user_id, ['title' => 'Quotation for Greenlam', 'status' => 'blocked', 'rep_note' => 'The specs are missing']);
        $late = $this->task($rep->user_id, ['title' => 'Send the price list', 'status' => 'in_progress', 'due_at' => now()->subDay()]);
        $old = $this->task($rep->user_id, ['title' => 'Old unopened task']);
        $old->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();
        $this->task($rep->user_id, ['title' => 'Fresh task, in time']);                                           // new today, not late: left alone
        $this->task($rep->user_id, ['title' => 'Finished', 'status' => 'done', 'due_at' => now()->subDays(5)]);   // done: never needs anyone
        $this->task($rep->user_id, ['title' => 'Cancelled', 'status' => 'cancelled', 'due_at' => now()->subDays(5)]);

        $found = SalesInsights::tasksNeedingAttention(5);
        $this->assertSame(3, $found['total']);
        $this->assertSame(['Quotation for Greenlam', 'Send the price list', 'Old unopened task'], $found['rows']->pluck('title')->all(), 'stuck first, then late, then not opened');

        $row = SalesInsights::reps(30)->firstWhere('id', $rep->id);
        $this->assertSame(4, $row['tasks_open'], 'open = new, started and cannot do');
        $this->assertSame(1, $row['tasks_late']);
    }

    public function test_the_prospects_waiting_for_a_call_back_are_the_open_ones_whose_date_has_come(): void
    {
        $rep = $this->rep('REP001', '9000000001', name: 'Ayush Sharma');
        $this->prospect($rep->user_id, 'Greenlam Pvt Ltd', 'call_back', now()->subDay());
        $this->prospect($rep->user_id, 'Behror Mills', 'interested', now()->subHours(2));
        $this->prospect($rep->user_id, 'Not yet due', 'call_back', now()->addDays(2));
        $this->prospect($rep->user_id, 'Not interested Co', 'not_interested', now()->subDay());   // closed: no more calls
        $this->prospect($rep->user_id, 'No date Co', 'no_answer', null);

        $due = SalesInsights::prospectCallsDue(5);
        $this->assertSame(2, $due['total']);
        $this->assertSame(['Greenlam Pvt Ltd', 'Behror Mills'], $due['rows']->map(fn ($r) => $r['prospect']->name)->all(), 'the oldest date first');
        $this->assertSame('Ayush Sharma', $due['rows'][0]['note']->user->name);

        $this->assertSame(5, SalesInsights::reps(30)->firstWhere('id', $rep->id)['prospect_calls'], 'every call of the window counts for the rep');
    }

    public function test_a_stuck_task_and_waiting_bug_reports_are_flags(): void
    {
        $rep = $this->rep('REP001', '9000000001', name: 'Ayush Sharma');
        $this->task($rep->user_id, ['title' => 'Quotation for Greenlam', 'status' => 'blocked', 'rep_note' => 'The specs are missing']);
        SalesBugReport::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'area' => 'prospects', 'body' => 'Something is wrong here', 'client_updated_at' => now()]);

        $flags = collect(SalesInsights::flags());
        $blocked = $flags->firstWhere('key', 'task_blocked');
        $this->assertSame('alert', $blocked['tone']);
        $this->assertStringContainsString('Ayush Sharma cannot do "Quotation for Greenlam"', $blocked['title']);
        $this->assertStringContainsString('The specs are missing', $blocked['text']);
        $this->assertSame('1 bug report waiting', $flags->firstWhere('key', 'bug_reports')['title']);
    }

    public function test_the_page_shows_the_new_tabs_flags_and_columns(): void
    {
        $rep = $this->rep('REP001', '9000000001', name: 'Ayush Sharma');
        $this->task($rep->user_id, ['title' => 'Quotation for Greenlam', 'status' => 'blocked', 'rep_note' => 'The specs are missing']);
        $this->prospect($rep->user_id, 'Greenlam Pvt Ltd', 'call_back', now()->subDay());
        $this->actingAs($this->manager());

        Livewire::test(SalesManagement::class)
            ->assertSee('Tasks')
            ->assertSee('Prospect calls')
            ->assertSee('Ayush Sharma cannot do')                    // the flag
            ->call('setTab', 'tasks')
            ->assertSee('Quotation for Greenlam')
            ->assertSee('Cannot do: The specs are missing')
            ->call('setTab', 'prospects')
            ->assertSee('Greenlam Pvt Ltd')
            ->assertSee('last call by Ayush Sharma');
    }

    public function test_a_manager_without_the_tasks_and_prospects_areas_does_not_get_those_tabs_or_flags(): void
    {
        $rep = $this->rep('REP001', '9000000001', name: 'Ayush Sharma');
        $this->task($rep->user_id, ['title' => 'Quotation for Greenlam', 'status' => 'blocked', 'rep_note' => 'secret reason']);
        $this->prospect($rep->user_id, 'Greenlam Pvt Ltd', 'call_back', now()->subDay());

        // a user who may open the page but not the tasks and prospects screens
        $limited = \App\Models\User::factory()->create(['role' => 'sales_manager', 'is_active' => true]);
        $roles = config('roles');
        $roles['sales_manager']['access'] = array_values(array_diff($roles['sales_manager']['access'], ['sales-tasks', 'sales-prospects', 'sales-bug-reports']));
        config(['roles' => $roles]);
        $this->actingAs($limited);

        Livewire::test(SalesManagement::class)
            ->assertDontSee('secret reason')
            ->assertDontSee('Ayush Sharma cannot do')
            ->assertDontSee('Prospect calls');
    }
}
