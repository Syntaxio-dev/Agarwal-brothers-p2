<?php

namespace Tests\Feature\Sales;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use App\Sales\Filament\Pages\SalesManagement;
use App\Sales\Filament\Resources\SalesClients\Pages\CreateSalesClient;
use App\Sales\Filament\Resources\SalesClients\Pages\EditSalesClient;
use App\Sales\Filament\Resources\SalesClients\Pages\ListSalesClients;
use App\Sales\Filament\Resources\SalesClients\RelationManagers\ContactsRelationManager;
use App\Sales\Models\SalesContact;
use App\Sales\Filament\Resources\SalesMeetings\Pages\ListSalesMeetings;
use App\Sales\Filament\Resources\SalesReps\Pages\CreateSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\EditSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\ListSalesReps;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Support\SalesExporter;
use App\Support\AdminFeatures;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

class AdminSalesTest extends SalesTestCase
{
    private const PAGES = ['/admin/sales-management', '/admin/sales-expenses', '/admin/sales-reps', '/admin/sales-meetings', '/admin/sales-clients', '/admin/sales-contacts', '/admin/sales-calls', '/admin/sales-positions', '/admin/sales-clients/create', '/admin/sales-reps/create', '/admin/sales-products', '/admin/sales-products/create', '/admin/sales-product-requests'];

    public function test_managers_and_admins_open_every_sales_screen(): void
    {
        foreach ([$this->manager(), User::factory()->create(['role' => 'admin', 'is_active' => true])] as $user) {
            $this->actingAs($user);
            foreach (self::PAGES as $url) {
                $this->get($url)->assertOk();
            }
        }
    }

    public function test_nobody_else_can_open_them(): void
    {
        $rep = $this->rep()->user;

        foreach ([$rep, User::factory()->create(['role' => 'sales', 'is_active' => true]), User::factory()->create(['role' => 'editor', 'is_active' => true]), User::factory()->create(['role' => 'hr', 'is_active' => true])] as $user) {
            $this->actingAs($user);
            foreach (self::PAGES as $url) {
                $this->get($url)->assertForbidden();
            }
        }

        auth()->logout();
        $this->get('/admin/sales-management')->assertRedirect('/admin/login');
    }

    public function test_the_dashboard_lists_the_sales_screens_only_for_those_who_can_open_them(): void
    {
        $this->assertArrayHasKey('Sales team', AdminFeatures::forUser($this->manager()));
        $this->assertArrayNotHasKey('Sales team', AdminFeatures::forUser(User::factory()->create(['role' => 'editor', 'is_active' => true])));
    }

    public function test_the_management_page_shows_the_numbers(): void
    {
        $a = $this->rep('REP001', '9000000001', name: 'Ravi Sharma');
        $this->storedExpense($a, ['amount_paise' => 123456, 'category' => 'fuel']);
        SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'client_name' => 'Acme Labs', 'meeting_at' => now()->subDays(3), 'next_followup_at' => now()->addDay(), 'client_updated_at' => now()]);

        $this->actingAs($this->manager());

        Livewire::test(SalesManagement::class)
            ->assertSee('₹1,234')
            ->assertSee('Ravi Sharma')
            ->assertDontSee('Acme Labs')   // a follow-up shows on its own tab
            ->call('setTab', 'followups')->assertSee('Acme Labs')
            ->assertSee('Petrol')
            ->assertSee('Needs your action')
            ->call('setRange', 7)->assertSet('range', 7)->assertSee('7 days')
            ->call('setRange', 5)->assertSet('range', 30);   // only 7, 30 or 90 are allowed
    }

    // approving, rejecting, bulk approve and the filters of the Expenses list moved to ExpensesQueueTest (the screen is a review queue now)

    public function test_meetings_are_read_only_but_the_manager_runs_the_client_pool(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $a = $this->rep();
        $m = SalesMeeting::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'client_name' => 'Acme', 'meeting_at' => now(), 'client_updated_at' => now()]);
        $c = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $a->user_id, 'name' => 'Lab Co', 'client_updated_at' => now()]);

        Livewire::test(ListSalesMeetings::class)->assertCanSeeTableRecords([$m]);
        Livewire::test(ListSalesClients::class)->assertCanSeeTableRecords([$c]);

        $this->get('/admin/sales-expenses/create')->assertNotFound();
        $this->get('/admin/sales-meetings/create')->assertNotFound();
        $this->get('/admin/sales-clients/create')->assertOk();
        $this->get('/admin/sales-clients/' . $c->id . '/edit')->assertOk();
        $this->get('/admin/sales-calls')->assertOk();
        $this->get('/admin/sales-positions')->assertOk();
    }

    public function test_a_manager_adds_a_client_with_people_and_gives_it_to_a_rep(): void
    {
        $this->actingAs($this->manager());
        $rep = $this->rep();

        Livewire::test(CreateSalesClient::class)
            ->fillForm(['name' => 'JK Cement', 'city' => 'Nimbahera', 'assigned_to' => $rep->user_id])
            ->call('create')
            ->assertHasNoFormErrors();

        $client = SalesClient::firstWhere('name', 'JK Cement');
        $this->assertNotNull($client->uuid, 'a client made in the admin gets the id a phone would have made');
        $this->assertSame($rep->user_id, $client->assigned_to);

        Livewire::test(ContactsRelationManager::class, ['ownerRecord' => $client, 'pageClass' => EditSalesClient::class])
            ->callTableAction('create', data: ['name' => 'Rohit Sharma', 'position' => 'Purchase Manager', 'phone' => '98765 43210'])
            ->assertHasNoTableActionErrors();

        $person = SalesContact::firstWhere('name', 'Rohit Sharma');
        $this->assertSame($client->uuid, $person->client_uuid);
        $this->assertSame('+919876543210', $person->phone_normalized);

        // the same number cannot be saved twice anywhere in the pool
        Livewire::test(ContactsRelationManager::class, ['ownerRecord' => $client, 'pageClass' => EditSalesClient::class])
            ->callTableAction('create', data: ['name' => 'Someone Else', 'position' => 'Lab Head', 'phone' => '+91 98765-43210'])
            ->assertHasTableActionErrors(['phone']);
        $this->assertSame(1, SalesContact::count());
    }

    public function test_an_admin_edit_is_a_newer_version_for_the_phones_and_a_bulk_give_works(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $rep = $this->rep();
        $other = $this->rep('REP002', '9000000002');
        $client = SalesClient::create(['uuid' => (string) Str::uuid(), 'user_id' => $rep->user_id, 'assigned_to' => $rep->user_id, 'name' => 'Lab Co', 'client_updated_at' => now()->subDay()]);
        $before = $client->client_updated_at;

        Livewire::test(ListSalesClients::class)->callTableBulkAction('assign', [$client], data: ['rep' => $other->user_id]);

        $client->refresh();
        $this->assertSame($other->user_id, $client->assigned_to);
        $this->assertTrue($client->client_updated_at->gt($before), 'the phones must see the edit as newer');
        $this->assertSame($manager->id, $client->updated_by);
    }

    public function test_the_csv_import_action_fills_the_pool(): void
    {
        $this->actingAs($this->manager());
        $this->rep();
        \Illuminate\Support\Facades\Storage::fake('local');
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('numbers.csv', "Client,Place,Contact,Position,Phone\nShree Cement,Beawar,Mohan Lal,Quality Chief,99999 11111\n");

        Livewire::test(ListSalesClients::class)
            ->callTableAction('import', data: ['file' => $file, 'rep' => null])
            ->assertHasNoTableActionErrors();

        $this->assertSame(1, SalesClient::where('name', 'Shree Cement')->count());
        $this->assertSame(1, SalesContact::where('name', 'Mohan Lal')->count());
    }

    public function test_a_manager_adds_a_rep_who_can_then_sign_in(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'Anita Verma', 'employee_code' => 'rep-007', 'phone' => '98765 43210'])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'rep-007@reps.invalid')->first();
        $this->assertNotNull($user);
        $this->assertSame('field_rep', $user->role);
        // the password is made for the manager and shown once on step 2
        $password = Livewire::test(\App\Sales\Filament\Resources\SalesReps\Pages\RepSignInReady::class, ['record' => $user->salesProfile->getKey()])->get('password');
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertDatabaseHas('sales_profiles', ['user_id' => $user->id, 'employee_code' => 'REP-007', 'phone' => '+919876543210', 'is_active' => 1]);

        auth()->logout();
        $this->postJson('/api/sales/v1/auth/login', ['login' => '9876543210', 'password' => $password])->assertOk()->assertJsonPath('account.name', 'Anita Verma');
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_rep_form_rules(): void
    {
        $this->actingAs($this->manager());
        $this->rep('REP001', '9000000001');

        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'X', 'employee_code' => 'REP001', 'phone' => '12'])
            ->call('create')
            ->assertHasFormErrors(['name', 'employee_code', 'phone']);

        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'Anita', 'employee_code' => 'NEW1', 'phone' => '90000 00001'])
            ->call('create')
            ->assertHasFormErrors(['phone']);   // the same number as another rep, in another spelling

        $this->assertDatabaseCount('sales_profiles', 1);
    }

    public function test_switching_a_rep_off_in_the_admin_blocks_the_phone_and_resetting_signs_it_out(): void
    {
        $manager = $this->manager();
        $this->actingAs($manager);
        $profile = $this->rep();
        $this->headers($profile);

        Livewire::test(EditSalesRep::class, ['record' => $profile->getKey()])
            ->fillForm(['name' => 'Ravi Kumar', 'phone' => '9000000001', 'is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Ravi Kumar', $profile->user->fresh()->name);
        $this->assertFalse($profile->fresh()->is_active);
        $this->assertFalse($profile->user->fresh()->is_active);
        $this->assertDatabaseCount('personal_access_tokens', 0);

        // back on, then a new password: the old one stops working and every phone is signed out
        \App\Sales\Support\RepAccount::setActive($profile->fresh(), true);
        $this->headers($profile);
        Livewire::test(EditSalesRep::class, ['record' => $profile->getKey()])->callAction('resetPassword');
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->postJson('/api/sales/v1/auth/login', ['login' => 'REP001', 'password' => self::PASSWORD])->assertStatus(401);
    }

    public function test_the_rep_list_shows_reps_and_the_users_list_hides_them(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $a = $this->rep('REP001', '9000000001', name: 'Ravi');
        $this->storedExpense($a, ['amount_paise' => 70000]);

        Livewire::test(ListSalesReps::class)->assertSee('Ravi')->assertSee('₹700');
        Livewire::test(ListUsers::class)->assertCanNotSeeTableRecords([$a->user]);
    }

    public function test_exports_neutralise_spreadsheet_formulas(): void
    {
        $profile = $this->rep();
        $this->storedExpense($profile, ['note' => '=HYPERLINK("http://evil.example","click")', 'amount_paise' => 45050]);

        ob_start();
        SalesExporter::expenses(SalesExpense::query(), 'csv')->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringNotContainsString(',=HYPERLINK', $csv);
        $this->assertStringContainsString('450.5', $csv);
        $this->assertStringContainsString('REP001', $csv);
    }
}
