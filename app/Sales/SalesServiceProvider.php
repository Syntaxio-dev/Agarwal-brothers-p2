<?php

namespace App\Sales;

use App\Models\User;
use App\Sales\Http\Middleware\ForceJson;
use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesPosition;
use App\Sales\Models\SalesProfile;
use App\Sales\Policies\CallPolicy;
use App\Sales\Policies\ClientPolicy;
use App\Sales\Policies\ContactPolicy;
use App\Sales\Policies\EnquiryPolicy;
use App\Sales\Policies\ExpensePolicy;
use App\Sales\Policies\MeetingPolicy;
use App\Sales\Policies\PositionPolicy;
use App\Sales\Policies\ProfilePolicy;
use App\Sales\Policies\ReportPolicy;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

/**
 * Plugs the Sales module into the app. Removing the module = delete app/Sales, routes/sales-api.php, config/sales.php,
 * this provider's line in bootstrap/providers.php, the Sales files under app/Filament, and drop the sales_ tables.
 */
class SalesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // The only thing added to the existing User model's behaviour is this relation (no change to the users table).
        User::resolveRelationUsing('salesProfile', fn (User $user) => $user->hasOne(SalesProfile::class));

        Gate::policy(SalesProfile::class, ProfilePolicy::class);
        Gate::policy(SalesExpense::class, ExpensePolicy::class);
        Gate::policy(SalesMeeting::class, MeetingPolicy::class);
        Gate::policy(SalesClient::class, ClientPolicy::class);
        Gate::policy(SalesContact::class, ContactPolicy::class);
        Gate::policy(SalesCall::class, CallPolicy::class);
        Gate::policy(SalesPosition::class, PositionPolicy::class);
        Gate::policy(SalesEnquiry::class, EnquiryPolicy::class);
        Gate::policy(SalesDailyReport::class, ReportPolicy::class);

        // A guest of the phone API is told "401 sign in" (JSON), never redirected to a login page.
        // (This site has no route named "login": the admin panel has its own sign-in, so a guest is simply refused.)
        Authenticate::redirectUsing(fn (Request $request) => null);

        $this->limiters();

        if (! $this->app->routesAreCached()) {
            Route::middleware(['api', ForceJson::class])
                ->prefix('api/sales/v1')
                ->name('sales.api.')
                ->group(base_path('routes/sales-api.php'));

            $this->billViewer();
        }
    }

    /** Own counters, like the website's forms: a busy sync must not use up the login allowance. */
    private function limiters(): void
    {
        // Logins: per address, and tighter per address + typed login, so one rep's code cannot be guessed from one place.
        RateLimiter::for('sales-login', fn (Request $request) => [
            Limit::perMinute(20)->by('sales-login|' . $request->ip()),
            Limit::perMinute(5)->by('sales-login|' . $request->ip() . '|' . strtolower((string) $request->input('login'))),
        ]);

        // Signed-in calls (me, logout, push, pull ...): per rep.
        RateLimiter::for('sales-api', fn (Request $request) => Limit::perMinute(120)->by('sales-api|' . ($request->user()?->getAuthIdentifier() ?? $request->ip())));

        // Bill uploads are heavier: fewer per minute.
        RateLimiter::for('sales-upload', fn (Request $request) => Limit::perMinute(40)->by('sales-upload|' . ($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }

    /**
     * A manager looks at a bill photo through this address (the files are on the private disk, there is no public link).
     * It uses the admin panel's login session and the same role check as the Expenses screen.
     */
    private function billViewer(): void
    {
        Route::middleware('web')->get('admin/sales/bills/{uuid}', function (string $uuid) {
            abort_unless(auth()->user()?->canManage('sales-expenses'), 403);

            $expense = SalesExpense::withTrashed()->where('uuid', $uuid)->firstOrFail();
            abort_unless($expense->bill_path && Storage::disk('local')->exists($expense->bill_path), 404);

            return Storage::disk('local')->response($expense->bill_path, null, [
                'Content-Type' => $expense->bill_mime ?: 'application/octet-stream',
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ]);
        })->whereUuid('uuid')->name('sales.bill');
    }
}
