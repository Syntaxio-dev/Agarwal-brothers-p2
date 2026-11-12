<?php

namespace Tests\Feature\Sales;

use App\Sales\Auth\SalesLogin;
use Filament\Facades\Filament;
use Livewire\Livewire;

/** The Sales sub-domain sign-in: a manager with the e-mail, a rep with the employee code or phone of the phone app (and then on his own desk). */
class SalesLoginTest extends SalesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('sales');
    }

    private function signIn(string $login, string $password)
    {
        return Livewire::test(SalesLogin::class)->fillForm(['email' => $login, 'password' => $password])->call('authenticate');
    }

    public function test_a_rep_signs_in_with_code_or_phone_and_lands_on_his_desk(): void
    {
        $rep = $this->rep('AB-0001', '9000000001');

        foreach (['AB-0001', 'ab-0001', '9000000001', '+91 90000 00001'] as $login) {
            auth()->logout();
            $this->signIn($login, self::PASSWORD)->assertHasNoFormErrors()->assertRedirect(Filament::getPanel('rep')->getUrl());
            $this->assertSame($rep->user_id, auth()->id(), "signed in as $login");
        }
    }

    public function test_a_manager_still_signs_in_with_the_email_and_goes_to_the_office(): void
    {
        $manager = $this->manager();

        $this->signIn($manager->email, 'password')->assertHasNoFormErrors()->assertRedirect(Filament::getUrl());
        $this->assertSame($manager->id, auth()->id());
    }

    public function test_wrong_password_unknown_code_and_a_switched_off_rep_are_refused_alike(): void
    {
        $this->rep('AB-0001', '9000000001');
        $this->rep('AB-0002', '9000000002', active: false);

        foreach ([['AB-0001', 'wrong-password'], ['AB-9999', self::PASSWORD], ['AB-0002', self::PASSWORD], ['nobody@example.com', 'x']] as [$login, $password]) {
            $this->signIn($login, $password)->assertHasFormErrors(['email']);
            $this->assertGuest();
        }
    }

    public function test_a_rep_who_is_already_signed_in_and_opens_the_login_goes_to_his_desk(): void
    {
        $rep = $this->rep();
        $this->actingAs($rep->user);

        Livewire::test(SalesLogin::class)->assertRedirect(Filament::getPanel('rep')->getUrl());
    }

    public function test_the_login_page_asks_for_email_code_or_phone(): void
    {
        $this->get('/sales/login')->assertOk()->assertSee('Email, employee code or phone');
    }

    public function test_both_sign_in_pages_have_their_own_look_and_the_desk_pages_do_not(): void
    {
        foreach (['/sales/login', '/rep/login'] as $url) {
            $this->get($url)->assertOk()->assertSee('rl-hero', false)->assertSee('Your sales desk');
        }

        $this->actingAs($this->rep()->user);
        $this->get('/rep/enquiries')->assertOk()->assertDontSee('rl-hero', false);
    }
}
