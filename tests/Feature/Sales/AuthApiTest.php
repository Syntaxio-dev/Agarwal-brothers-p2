<?php

namespace Tests\Feature\Sales;

use App\Sales\Support\RepAccount;

class AuthApiTest extends SalesTestCase
{
    private const LOGIN = '/api/sales/v1/auth/login';

    public function test_rep_signs_in_with_employee_code_or_phone(): void
    {
        $this->rep('REP001', '98765 43210');

        $byCode = $this->postJson(self::LOGIN, ['login' => 'rep001', 'password' => self::PASSWORD, 'app_version' => '1.3', 'device_model' => 'Pixel 7']);
        $byCode->assertOk()->assertJsonStructure(['token', 'token_type', 'expires_at', 'account' => ['name', 'employee_code', 'phone'], 'server_time']);
        $this->assertSame('+919876543210', $byCode->json('account.phone'));

        $this->postJson(self::LOGIN, ['login' => '09876543210', 'password' => self::PASSWORD])->assertOk();

        $this->assertDatabaseHas('sales_profiles', ['employee_code' => 'REP001', 'app_version' => '1.3', 'device_model' => 'Pixel 7']);
    }

    public function test_wrong_password_and_unknown_rep_get_the_same_answer(): void
    {
        $this->rep();

        $wrong = $this->postJson(self::LOGIN, ['login' => 'REP001', 'password' => 'nope'])->assertStatus(401);
        $unknown = $this->postJson(self::LOGIN, ['login' => 'NOBODY', 'password' => 'nope'])->assertStatus(401);

        $this->assertSame('INVALID_CREDENTIALS', $wrong->json('code'));
        $this->assertSame($wrong->json('message'), $unknown->json('message'));
    }

    public function test_a_rep_without_a_phone_is_never_matched_by_a_junk_login(): void
    {
        $this->rep('REP001', '');

        $this->postJson(self::LOGIN, ['login' => '!!!', 'password' => self::PASSWORD])->assertStatus(401);
    }

    public function test_errors_use_one_shape(): void
    {
        $this->postJson(self::LOGIN, [])->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_FAILED')
            ->assertJsonStructure(['code', 'message', 'errors' => ['login', 'password']]);

        $this->getJson('/api/sales/v1/me')->assertStatus(401)->assertJsonPath('code', 'UNAUTHENTICATED');
        $this->getJson('/api/sales/v1/nothing-here')->assertStatus(404)->assertJsonPath('code', 'NOT_FOUND');
    }

    public function test_me_and_logout(): void
    {
        $headers = $this->headers($this->rep());

        $this->getJson('/api/sales/v1/me', $headers)->assertOk()->assertJsonPath('account.employee_code', 'REP001');

        $this->postJson('/api/sales/v1/auth/logout', [], $headers)->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_signing_in_again_signs_the_old_phone_out(): void
    {
        $profile = $this->rep();
        $this->headers($profile);
        $this->headers($profile);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_token_expires(): void
    {
        $headers = $this->headers($this->rep());

        $this->travel(config('sales.token_days') + 1)->days();
        $this->app['auth']->forgetGuards();   // the guard is cached inside one test process: read the token again

        $this->getJson('/api/sales/v1/me', $headers)->assertStatus(401);
    }

    public function test_switched_off_rep_cannot_sign_in(): void
    {
        $this->rep('REP009', '9000000009', active: false);

        $this->postJson(self::LOGIN, ['login' => 'REP009', 'password' => self::PASSWORD])
            ->assertStatus(403)->assertJsonPath('code', 'ACCOUNT_INACTIVE');

        // a wrong password is still "wrong password": the switch is not revealed to a stranger
        $this->postJson(self::LOGIN, ['login' => 'REP009', 'password' => 'guess'])->assertStatus(401);
    }

    public function test_switching_a_rep_off_blocks_the_phone_at_once(): void
    {
        $profile = $this->rep();
        $headers = $this->headers($profile);

        RepAccount::setActive($profile, false);
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->getJson('/api/sales/v1/me', $headers)->assertStatus(401);

        // even a token that somehow survived is stopped by the per-call check
        RepAccount::setActive($profile, true);
        $headers = $this->headers($profile);
        $profile->update(['is_active' => false]);

        $this->getJson('/api/sales/v1/me', $headers)->assertStatus(403)->assertJsonPath('code', 'ACCOUNT_INACTIVE');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_is_rate_limited(): void
    {
        $this->rep();

        foreach (range(1, 5) as $i) {
            $this->postJson(self::LOGIN, ['login' => 'REP001', 'password' => 'bad'])->assertStatus(401);
        }

        $this->postJson(self::LOGIN, ['login' => 'REP001', 'password' => 'bad'])->assertStatus(429)->assertJsonPath('code', 'TOO_MANY_REQUESTS');
    }

    public function test_reset_password_revokes_tokens(): void
    {
        $profile = $this->rep();
        $this->headers($profile);

        RepAccount::resetPassword($profile, 'brand-new-pass-1');

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->postJson(self::LOGIN, ['login' => 'REP001', 'password' => self::PASSWORD])->assertStatus(401);
        $this->postJson(self::LOGIN, ['login' => 'REP001', 'password' => 'brand-new-pass-1'])->assertOk();
    }

    public function test_a_rep_token_cannot_open_the_admin_panel_and_a_manager_is_not_a_rep(): void
    {
        $profile = $this->rep();

        $this->actingAs($profile->user)->get('/admin')->assertForbidden();

        // a manager has no rep profile: the app API refuses them even with a valid token
        $manager = $this->manager();
        $token = $manager->createToken('x', ['sales:rep'])->plainTextToken;
        $this->getJson('/api/sales/v1/me', ['Authorization' => "Bearer $token"])->assertStatus(403)->assertJsonPath('code', 'ACCOUNT_INACTIVE');
    }

    public function test_the_api_does_not_accept_a_browser_session(): void
    {
        $profile = $this->rep();

        $this->actingAs($profile->user)->getJson('/api/sales/v1/me')->assertStatus(401);
    }
}
