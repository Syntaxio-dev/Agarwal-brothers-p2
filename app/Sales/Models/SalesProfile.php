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

    protected $fillable = ['user_id', 'employee_code', 'phone', 'is_active', 'last_sync_at', 'app_version', 'device_model'];

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
