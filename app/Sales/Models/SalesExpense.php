<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Support\ActivityLogger;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesExpense extends Model
{
    use HasPhoneTime, SoftDeletes;

    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $table = 'sales_expenses';

    // status / reviewed_* are NOT fillable: only approve() and reject() can set them, never a push from the phone.
    protected $fillable = ['uuid', 'user_id', 'category', 'amount_paise', 'expense_date', 'note', 'client_updated_at'];

    // A new expense is pending from the first moment, also before it is read back from the database.
    protected $attributes = ['status' => self::PENDING];

    protected function casts(): array
    {
        return [
            'amount_paise' => 'integer',
            'expense_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }

    public function hasBill(): bool
    {
        return filled($this->bill_path);
    }

    /** Rupees for screens and exports ("1,234.50"): the stored value is whole paise. */
    public function getAmountAttribute(): float
    {
        return $this->amount_paise / 100;
    }

    public function approve(User $by): void
    {
        $this->review(self::APPROVED, $by, null);
    }

    public function reject(User $by, string $reason): void
    {
        $this->review(self::REJECTED, $by, $reason);
    }

    private function review(string $status, User $by, ?string $reason): void
    {
        $old = $this->status;

        $this->forceFill([
            'status' => $status,
            'reviewed_by' => $by->id,
            'reviewed_at' => now(),
            'reject_reason' => $reason,
        ])->save();

        ActivityLogger::record('updated', $this, array_filter([
            'status' => ['old' => $old, 'new' => $status],
            'reject_reason' => $reason ? ['old' => null, 'new' => $reason] : null,
        ]), $by);
    }
}
