<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Filament\Resources\SalesReps\Pages\CreateSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\EditSalesRep;
use App\Sales\Models\SalesProfile;
use Livewire\Livewire;

/**
 * A rep exists only because a manager made the account (name, e-mail, number, code, password). The app has no sign-up of its own:
 * these tests keep that promise and the new e-mail field honest.
 */
class RepEmailTest extends SalesTestCase
{
    public function test_the_manager_makes_the_account_with_name_email_number_code_and_password(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'Anita Verma', 'email' => 'Anita.Verma@Example.com', 'employee_code' => 'rep-010', 'phone' => '98765 43210', 'password' => 'Secret-pass-1', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::where('email', 'anita.verma@example.com')->first();
        $this->assertNotNull($user, 'the real e-mail is kept (in lower case)');
        $this->assertSame('field_rep', $user->role);
        $this->assertSame('REP-010', $user->salesProfile->employee_code);
        $this->assertSame('+919876543210', $user->salesProfile->phone);

        // the app signs in with the code (or the number) and the password: that is all that opens it
        auth()->logout();
        $this->postJson('/api/sales/v1/auth/login', ['login' => 'rep-010', 'password' => 'Secret-pass-1'])->assertOk()->assertJsonPath('account.name', 'Anita Verma');
        $this->postJson('/api/sales/v1/auth/login', ['login' => '9876543210', 'password' => 'Secret-pass-1'])->assertOk();
        $this->postJson('/api/sales/v1/auth/login', ['login' => 'anita.verma@example.com', 'password' => 'Secret-pass-1'])->assertStatus(401);

        // and a rep with a real e-mail still cannot open the admin panel
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_the_email_is_optional_but_a_number_is_needed_and_nothing_is_shared(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'No Number', 'employee_code' => 'REP020', 'password' => 'Secret-pass-1'])
            ->call('create')
            ->assertHasFormErrors(['phone' => 'required']);

        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'No Email', 'employee_code' => 'REP021', 'phone' => '90000 00021', 'password' => 'Secret-pass-1'])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertSame('rep021@reps.invalid', User::where('name', 'No Email')->value('email'), 'without an e-mail a placeholder is used');

        $taken = User::factory()->create(['email' => 'taken@example.com', 'role' => 'editor', 'is_active' => true]);
        Livewire::test(CreateSalesRep::class)
            ->fillForm(['name' => 'Copy', 'email' => 'TAKEN@example.com', 'employee_code' => 'REP022', 'phone' => '90000 00022', 'password' => 'Secret-pass-1'])
            ->call('create')
            ->assertHasFormErrors(['email']);
        $this->assertNotNull($taken);
        $this->assertSame(0, SalesProfile::where('employee_code', 'REP022')->count());
    }

    public function test_the_email_can_be_corrected_and_cleared_when_editing(): void
    {
        $this->actingAs($this->manager());
        $profile = $this->rep('REP030', '9000000030');

        Livewire::test(EditSalesRep::class, ['record' => $profile->getKey()])
            ->assertFormSet(['email' => null])
            ->fillForm(['name' => 'Rep REP030', 'phone' => '9000000030', 'email' => 'rep030@example.com', 'is_active' => true])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('rep030@example.com', $profile->user->fresh()->email);

        Livewire::test(EditSalesRep::class, ['record' => $profile->getKey()])
            ->assertFormSet(['email' => 'rep030@example.com'])
            ->fillForm(['email' => '', 'phone' => '9000000030', 'is_active' => true])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('rep030@reps.invalid', $profile->user->fresh()->email);

        // a rep keeps their own e-mail without it counting as "taken"
        Livewire::test(EditSalesRep::class, ['record' => $profile->getKey()])
            ->fillForm(['email' => 'rep030@example.com', 'phone' => '9000000030', 'is_active' => true])
            ->call('save')
            ->assertHasNoFormErrors();
        Livewire::test(EditSalesRep::class, ['record' => $profile->getKey()])
            ->fillForm(['email' => 'rep030@example.com', 'phone' => '9000000030', 'is_active' => true])
            ->call('save')
            ->assertHasNoFormErrors();
    }

    public function test_there_is_no_way_to_sign_up_from_the_app(): void
    {
        foreach (['register', 'signup', 'auth/register', 'auth/signup', 'users'] as $path) {
            $this->postJson('/api/sales/v1/' . $path, ['name' => 'Sneaky', 'employee_code' => 'HACK01', 'password' => 'Secret-pass-1'])->assertStatus(404);
        }
        $this->assertSame(0, SalesProfile::count());
    }
}
