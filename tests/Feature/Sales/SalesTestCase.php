<?php

namespace Tests\Feature\Sales;

use App\Models\User;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\RepAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class SalesTestCase extends TestCase
{
    use RefreshDatabase;

    protected const PASSWORD = 'correct-horse-9';

    /**
     * A real request starts with a fresh application, so the user a previous request signed in with is forgotten. Inside one
     * test the token guard would keep that user and ignore the next request's token: reset it before every request.
     * (Only the API's token guard and the default guard name: a user chosen with actingAs() for the admin panel is left alone.)
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $this->resetGuards();

        try {
            return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
        } finally {
            $this->resetGuards();   // so a later actingAs() lands on the web guard, not on the one the API request chose
        }
    }

    private string $defaultGuard = 'web';

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultGuard = config('auth.defaults.guard');   // shouldUse() overwrites this very setting, so remember it now
    }

    private function resetGuards(): void
    {
        $this->app['auth']->guard('sanctum')->forgetUser();
        $this->app['auth']->shouldUse($this->defaultGuard);   // auth:sanctum switches the default guard for the rest of the process
    }

    protected function rep(string $code = 'REP001', string $phone = '9000000001', bool $active = true, string $name = ''): SalesProfile
    {
        return RepAccount::create([
            'name' => $name ?: "Rep $code",
            'employee_code' => $code,
            'phone' => $phone,
            'password' => self::PASSWORD,
            'is_active' => $active,
        ]);
    }

    protected function manager(): User
    {
        return User::factory()->create(['role' => 'sales_manager', 'is_active' => true]);
    }

    /** Sign in through the real endpoint and return the Authorization header for later calls. */
    protected function headers(SalesProfile $profile): array
    {
        $token = $this->postJson('/api/sales/v1/auth/login', [
            'login' => $profile->employee_code,
            'password' => self::PASSWORD,
        ])->assertOk()->json('token');

        return ['Authorization' => 'Bearer ' . $token];
    }

    protected function moment(string $modify = 'now'): string
    {
        return now()->utc()->modify($modify)->format('Y-m-d\TH:i:s.v\Z');
    }

    protected function expense(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'category' => 'fuel',
            'amount_paise' => 45000,
            'expense_date' => now(config('sales.timezone'))->toDateString(),
            'note' => 'Taxi to the lab',
            'has_bill' => false,
            'updated_at' => $this->moment(),
        ], $over);
    }

    protected function client(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'name' => 'Lab Co',
            'company' => 'Lab Co Pvt Ltd',
            'phone' => '98765 43210',
            'city' => 'Jaipur',
            'notes' => '',
            'updated_at' => $this->moment(),
        ], $over);
    }

    protected function meeting(array $over = []): array
    {
        return array_merge([
            'uuid' => (string) Str::uuid(),
            'client_uuid' => null,
            'client_name' => 'Lab Co',
            'meeting_at' => $this->moment('-1 hour'),
            'purpose' => 'Demo',
            'discussion' => 'Showed the BSA series',
            'outcome' => 'Quote asked',
            'next_followup_at' => $this->moment('+7 days'),
            'updated_at' => $this->moment(),
        ], $over);
    }

    protected function push(array $headers, array $data)
    {
        return $this->postJson('/api/sales/v1/sync/push', $data, $headers);
    }

    /** The one answer for a record, from a push result list. */
    protected function answer($response, string $kind, string $uuid): array
    {
        return collect($response->json("results.$kind"))->firstWhere('uuid', $uuid) ?? [];
    }

    protected function storedExpense(SalesProfile $profile, array $over = []): SalesExpense
    {
        // status is not mass assignable (only a manager sets it), so a test sets it the way the code does
        $status = $over['status'] ?? null;
        unset($over['status']);

        $expense = SalesExpense::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'user_id' => $profile->user_id,
            'category' => 'fuel',
            'amount_paise' => 45000,
            'expense_date' => now(config('sales.timezone'))->toDateString(),
            'client_updated_at' => now(),
        ], $over));

        if ($status) {
            $expense->forceFill(['status' => $status])->save();
        }

        return $expense;
    }
}
