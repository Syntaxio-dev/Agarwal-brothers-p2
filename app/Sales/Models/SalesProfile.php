<?php

namespace App\Sales\Models;

use App\Models\Concerns\LogsActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The sales side of a user: employee code, phone, active switch and what the phone last reported.
 * Switching is_active (by a manager) is written to the admin activity log.
 */
class SalesProfile extends Model
{
    use LogsActivity;

    public const ROLE = 'field_rep';

    protected $table = 'sales_profiles';

    protected $fillable = ['user_id', 'employee_code', 'phone', 'is_active', 'sort_order', 'last_sync_at', 'app_version', 'device_model'];

    protected static function booted(): void
    {
        // the rep list of the Clients screen is cached (SalesInsights::activeReps)
        static::saved(fn (SalesProfile $p) => ($p->wasRecentlyCreated || $p->wasChanged('is_active')) && \App\Sales\Support\SalesInsights::forgetClientSets());
        static::deleted(fn () => \App\Sales\Support\SalesInsights::forgetClientSets());
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_sync_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The rep's expenses (expenses belong to the user, so the keys are user_id on both sides). */
    public function expenses(): HasMany
    {
        return $this->hasMany(SalesExpense::class, 'user_id', 'user_id');
    }

    /** Shown in the activity log line ("Ravi Sharma"). */
    public function getNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /** The rep behind an employee code or a phone number (the app's sign-in and the desktop sign-in find a rep the same way). */
    public static function findByLogin(string $login): ?self
    {
        $login = trim($login);
        $phone = \App\Sales\Support\PhoneNumber::normalize($login);

        return static::query()
            ->with('user')
            ->where(function ($query) use ($login, $phone) {
                $query->where('employee_code', \Illuminate\Support\Str::upper($login));

                if ($phone) {
                    $query->orWhere('phone', $phone);
                }
            })
            ->first();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Active on both sides: the profile and the user account behind it. */
    public function canUseApp(): bool
    {
        return $this->is_active && $this->user?->role === self::ROLE && (bool) $this->user?->is_active;
    }

    /** No sync yet, or the last one is older than the configured number of hours. */
    public function isSyncStale(): bool
    {
        return ! $this->last_sync_at || $this->last_sync_at->lt(now()->subHours((int) config('sales.stale_sync_hours')));
    }
}
