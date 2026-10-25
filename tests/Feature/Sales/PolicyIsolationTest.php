<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use Illuminate\Support\Str;

/** Rep A must never be able to read or change rep B's data; managers can see all; office staff see nothing. */
class PolicyIsolationTest extends SalesTestCase
{
    public function test_rep_a_cannot_touch_rep_b_expenses(): void
    {
        $a = $this->rep('REP001', '9000000001')->user;
        $b = $this->rep('REP002', '9000000002');
        $mine = $this->storedExpense($a->salesProfile);
        $theirs = $this->storedExpense($b);

        $this->assertTrue($a->can('view', $mine));
        $this->assertTrue($a->can('update', $mine));
        $this->assertTrue($a->can('delete', $mine));

        $this->assertFalse($a->can('view', $theirs));
        $this->assertFalse($a->can('update', $theirs));
        $this->assertFalse($a->can('delete', $theirs));
    }

    public function test_meetings_stay_private_but_the_client_pool_is_shared(): void
    {
        $a = $this->rep('REP001', '9000000001')->user;
        $b = $this->rep('REP002', '9000000002')->user;

        $meeting = SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $b->id, 'client_name' => 'X', 'meeting_at' => now(), 'client_updated_at' => now()]);
        $this->assertFalse($a->can('view', $meeting));
        $this->assertFalse($a->can('update', $meeting));
        $this->assertFalse($a->can('delete', $meeting));
        $this->assertTrue($b->can('view', $meeting));

        // the pool: everybody reads and improves, only the one who added it (or a manager) deletes
        $client = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $b->id, 'name' => 'Lab Co', 'client_updated_at' => now()]);
        $this->assertTrue($a->can('view', $client));
        $this->assertTrue($a->can('update', $client));
        $this->assertFalse($a->can('delete', $client));
        $this->assertTrue($b->can('delete', $client));
        $this->assertTrue($this->manager()->can('delete', $client));
    }

    public function test_a_rep_cannot_change_an_expense_after_review_and_cannot_review(): void
    {
        $profile = $this->rep();
        $manager = $this->manager();
        $expense = $this->storedExpense($profile);

        $this->assertFalse($profile->user->can('review', $expense));
        $this->assertTrue($manager->can('review', $expense));

        $expense->approve($manager);

        $this->assertSame('approved', $expense->fresh()->status);
        $this->assertSame($manager->id, $expense->fresh()->reviewed_by);
        $this->assertFalse($profile->user->can('update', $expense->fresh()));
        $this->assertFalse($profile->user->can('delete', $expense->fresh()));
    }

    public function test_status_cannot_be_set_by_mass_assignment(): void
    {
        $profile = $this->rep();

        $expense = SalesExpense::create([
            'uuid' => (string) Str::uuid(), 'user_id' => $profile->user_id, 'category' => 'food', 'amount_paise' => 1000,
            'expense_date' => now()->toDateString(), 'client_updated_at' => now(),
            'status' => 'approved', 'reviewed_by' => $profile->user_id,
        ]);

        $this->assertSame('pending', $expense->fresh()->status);
        $this->assertNull($expense->fresh()->reviewed_by);
    }

    public function test_managers_see_everything_and_other_roles_nothing(): void
    {
        $expense = $this->storedExpense($this->rep());
        $manager = $this->manager();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->assertTrue($manager->can('view', $expense));
        $this->assertTrue($admin->can('view', $expense));
        $this->assertTrue($manager->can('viewAny', SalesExpense::class));

        foreach (['sales', 'editor', 'hr'] as $role) {
            $other = User::factory()->create(['role' => $role, 'is_active' => true]);   // "sales" = enquiry staff, not the field team
            $this->assertFalse($other->can('view', $expense), $role);
            $this->assertFalse($other->can('viewAny', SalesExpense::class), $role);
            $this->assertFalse($other->can('create', SalesExpense::class), $role);
        }
    }

    public function test_a_switched_off_rep_has_no_rights(): void
    {
        $profile = $this->rep();
        $expense = $this->storedExpense($profile);

        $profile->user->update(['is_active' => false]);

        $this->assertFalse($profile->user->fresh()->can('view', $expense));
    }

    public function test_reps_cannot_be_deleted_by_policy_and_a_rep_sees_only_their_own_profile(): void
    {
        $a = $this->rep('REP001', '9000000001');
        $b = $this->rep('REP002', '9000000002');
        $manager = $this->manager();

        $this->assertFalse($manager->can('delete', $a));
        $this->assertTrue($manager->can('update', $a));
        $this->assertFalse($a->user->can('update', $a));
        $this->assertTrue($a->user->can('view', $a));
        $this->assertFalse($a->user->can('view', $b));
    }

    public function test_switching_a_rep_is_written_to_the_activity_log(): void
    {
        $profile = $this->rep();
        $manager = $this->manager();

        $this->actingAs($manager);
        $profile->update(['is_active' => false]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $manager->id,
            'action' => 'updated',
            'subject_type' => $profile::class,
            'subject_id' => $profile->id,
        ]);
    }
}
