<?php

namespace Tests\Feature;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Widgets\TeamActivity;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function staff(string $role = 'admin', string $name = 'Asha Admin'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true, 'name' => $name]);
    }

    private function category(): Category
    {
        return Category::create([
            'brand_id' => Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true])->id,
            'name' => 'Balances', 'slug' => 'balances',
        ]);
    }

    public function test_creating_changing_and_deleting_are_recorded_with_who_and_what(): void
    {
        $asha = $this->staff();
        $this->actingAs($asha);
        $cat = $this->category();

        $p = Product::create(['category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => false, 'short_description' => 'Old text']);
        $p->update(['short_description' => 'New text', 'is_active' => true]);
        $p->delete();

        $log = ActivityLog::where('subject_type', Product::class)->orderBy('id')->get();

        $this->assertSame(['created', 'updated', 'deleted'], $log->pluck('action')->all());
        $this->assertSame([$asha->id, 'Asha Admin', 'Entris II'], [$log[0]->user_id, $log[0]->user_name, $log[0]->subject_label]);

        $changes = $log[1]->details;
        $this->assertSame(['old' => 'Old text', 'new' => 'New text'], $changes['short_description']);
        $this->assertSame(['old' => 'No', 'new' => 'Yes'], $changes['is_active']);
        $this->assertArrayNotHasKey('updated_at', $changes);

        // (MySQL stores JSON keys in its own order, so compare the names, not their order)
        $this->assertEqualsCanonicalizing(['Short Description', 'Active'], explode(', ', $log[1]->summary()));
    }

    public function test_visitors_consoles_and_unchanged_saves_leave_no_log(): void
    {
        $cat = $this->category();                                         // nobody signed in: created by a seeder or the console
        $p = Product::create(['category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true]);
        $this->assertSame(0, ActivityLog::count());

        // A visitor's form creates an enquiry; that is not team activity
        $this->post(route('contact.store'), ['name' => 'Asha Verma', 'email' => 'a@example.com', 'phone' => '9999999999', 'message' => 'Hi']);
        $this->assertSame(0, ActivityLog::count());

        $this->actingAs($this->staff());
        $p->update(['name' => 'Entris II']);                              // nothing really changed
        $p->touch();
        $this->assertSame(0, ActivityLog::count());
    }

    public function test_passwords_are_never_written_to_the_log(): void
    {
        $this->actingAs($this->staff());
        $user = User::factory()->create(['role' => 'editor', 'is_active' => true, 'name' => 'Edit Or']);

        $user->update(['password' => 'a-brand-new-secret-password', 'role' => 'sales']);

        $log = ActivityLog::where('subject_type', User::class)->where('action', 'updated')->firstOrFail();

        $this->assertSame(['old' => null, 'new' => '(changed)'], $log->details['password']);
        $this->assertSame('sales', $log->details['role']['new']);
        $this->assertStringNotContainsString('a-brand-new-secret-password', json_encode($log->toArray()));
        $this->assertStringNotContainsString('$2y$', json_encode($log->toArray()));      // no hash either
    }

    public function test_lists_are_summarised_and_ordering_is_not_logged(): void
    {
        $this->actingAs($this->staff());
        $p = Product::create(['category_id' => $this->category()->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true]);

        $p->update(['specs' => ['Capacity' => '220 g'], 'sort_order' => 5, 'top_pick_order' => 2]);

        $log = ActivityLog::where('action', 'updated')->firstOrFail();
        $this->assertSame(['specs'], array_keys($log->details));
        $this->assertSame('(list updated)', $log->details['specs']['new']);
    }

    public function test_bulk_actions_are_logged_per_product(): void
    {
        $asha = $this->staff();
        $this->actingAs($asha);
        $cat = $this->category();
        $a = Product::create(['category_id' => $cat->id, 'name' => 'A', 'slug' => 'a', 'is_active' => false]);
        $b = Product::create(['category_id' => $cat->id, 'name' => 'B', 'slug' => 'b', 'is_active' => false]);
        ActivityLog::query()->delete();

        Livewire::test(ListProducts::class)->callTableBulkAction('activate', [$a, $b]);

        $this->assertEqualsCanonicalizing(['A', 'B'], ActivityLog::where('action', 'updated')->pluck('subject_label')->all());
    }

    public function test_enquiries_log_only_their_removal_because_notes_cover_the_rest(): void
    {
        $this->actingAs($this->staff());
        $e = Enquiry::create(['name' => 'Ravi', 'email' => 'r@example.com', 'status' => 'new']);
        $e->update(['status' => 'contacted']);
        $this->assertSame(0, ActivityLog::count());
        $this->assertTrue($e->teamNotes()->exists());                      // the day-to-day log is in the notes

        $e->delete();
        $this->assertSame(['deleted'], ActivityLog::pluck('action')->all());
    }

    public function test_signing_in_is_recorded(): void
    {
        $user = $this->staff('editor', 'Edit Or');

        event(new \Illuminate\Auth\Events\Login('web', $user, false));

        $log = ActivityLog::firstOrFail();
        $this->assertSame(['login', 'Edit Or', null], [$log->action, $log->user_name, $log->subject_type]);
        $this->assertSame('Signed in', $log->actionLabel());
    }

    public function test_a_failing_log_never_breaks_the_real_save(): void
    {
        $this->actingAs($this->staff());
        $cat = $this->category();
        DB::statement('DROP TABLE activity_logs');

        $p = Product::create(['category_id' => $cat->id, 'name' => 'Still saved', 'slug' => 'still-saved', 'is_active' => true]);

        $this->assertDatabaseHas('products', ['id' => $p->id]);
    }

    // ---------------------------------------------------------------- the admin screens

    public function test_only_administrators_open_the_log(): void
    {
        foreach (['editor', 'sales', 'hr'] as $role) {
            $this->actingAs($this->staff($role, $role));
            $this->get('/admin/activity-logs')->assertForbidden();
        }

        $this->actingAs($this->staff());
        $this->get('/admin/activity-logs')->assertOk();
    }

    public function test_the_log_is_read_only(): void
    {
        $this->actingAs($this->staff());
        $this->category();
        $log = ActivityLog::firstOrFail();

        $this->assertFalse(ActivityLogResource::canCreate());
        $this->assertFalse(ActivityLogResource::canEdit($log));
        $this->assertFalse(ActivityLogResource::canDelete($log));
        $this->assertArrayNotHasKey('create', ActivityLogResource::getPages());
        $this->assertArrayNotHasKey('edit', ActivityLogResource::getPages());
    }

    public function test_list_shows_people_filters_and_change_details(): void
    {
        $asha = $this->staff('admin', 'Asha Admin');
        $sneha = $this->staff('editor', 'Sneha Editor');
        $cat = $this->category();

        $this->actingAs($asha);
        $p = Product::create(['category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true, 'short_description' => 'Old']);
        $this->actingAs($sneha);
        $p->update(['short_description' => 'Brand new text']);
        $this->actingAs($asha);

        $mine = ActivityLog::where('user_id', $asha->id)->get();
        $hers = ActivityLog::where('user_id', $sneha->id)->get();

        Livewire::test(ListActivityLogs::class)
            ->assertSee('Asha Admin')->assertSee('Sneha Editor')->assertSee('Entris II')
            ->filterTable('user_id', $sneha->id)
            ->assertCanSeeTableRecords($hers)->assertCanNotSeeTableRecords($mine);

        Livewire::test(ListActivityLogs::class)
            ->filterTable('action', 'created')->assertCanSeeTableRecords($mine->where('action', 'created'))->assertCanNotSeeTableRecords($hers);

        Livewire::test(ListActivityLogs::class)->mountTableAction('details', $hers->first());

        // what the "Details" window shows: each field with the value before and after
        $html = view('filament.activity.changes', ['log' => $hers->first()])->render();
        $this->assertStringContainsString('Short Description', $html);
        $this->assertStringContainsString('Old', $html);
        $this->assertStringContainsString('Brand new text', $html);
    }

    public function test_dashboard_box_shows_the_latest_team_changes_to_admins_only(): void
    {
        $this->actingAs($this->staff('admin', 'Asha Admin'));
        Product::create(['category_id' => $this->category()->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true]);

        Livewire::test(TeamActivity::class)->assertSee('Team activity')->assertSee('Asha Admin')->assertSee('Entris II')->assertSee('See all activity');
        $this->assertTrue(TeamActivity::canView());

        $this->actingAs($this->staff('editor', 'Edit Or'));
        $this->assertFalse(TeamActivity::canView());
    }

    public function test_old_lines_are_removed_by_the_daily_clean_up(): void
    {
        $this->actingAs($this->staff());
        $this->category();
        $fresh = ActivityLog::firstOrFail();
        $old = ActivityLog::create(['user_name' => 'Old Timer', 'action' => 'updated', 'subject_label' => 'x']);
        DB::table('activity_logs')->where('id', $old->id)->update(['created_at' => now()->subDays(200)]);

        $this->artisan('housekeeping:run')->assertSuccessful();

        $this->assertDatabaseHas('activity_logs', ['id' => $fresh->id]);
        $this->assertDatabaseMissing('activity_logs', ['id' => $old->id]);
    }

    public function test_help_guide_explains_the_log(): void
    {
        $this->actingAs($this->staff());

        $this->get('/admin/help')->assertOk()->assertSee('Activity log: who changed what');
        $this->assertSame('activity', \App\Support\HelpGuide::topicFor('admin/activity-logs'));
        $this->assertContains('activity', array_column(\App\Support\HelpGuide::search('who changed what', auth()->user()), 'id'));
    }
}
