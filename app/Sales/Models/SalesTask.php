<?php

namespace App\Sales\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Work a manager gives a field rep (app v1.20): "make the quotation from these specs and send it to this e-mail". The manager writes it here, the rep
 * sees it on the phone and moves it on (status + a note); the manager checks the status here. The office's own fields and the rep's two fields
 * (status, rep_note) never overwrite each other.
 */
class SalesTask extends Model
{
    use SoftDeletes;

    /** What the manager and the rep see. 'new' and 'cancelled' are the office's; the rep sets the three in the middle. */
    public const STATUSES = ['new' => 'New', 'in_progress' => 'Started', 'done' => 'Done', 'blocked' => 'Cannot do', 'cancelled' => 'Cancelled'];

    public const REP_STATUSES = ['in_progress', 'done', 'blocked'];

    public const OPEN = ['new', 'in_progress', 'blocked'];

    public const PRIORITIES = ['normal' => 'Normal', 'high' => 'High'];

    public const MAX_FILES = 5;

    protected $table = 'sales_tasks';

    protected $fillable = ['uuid', 'assigned_to', 'created_by', 'title', 'description', 'send_to_email', 'due_at', 'priority', 'status', 'rep_note', 'rep_updated_at'];

    protected function casts(): array
    {
        return ['due_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesTask $task) {
            $task->uuid ??= (string) Str::uuid();
            $task->created_by ??= auth()->id();
        });

        // every change made on the office side is a new version (a phone compares versions, never clocks)
        static::updating(function (SalesTask $task) {
            if (! $task->isDirty('version')) {
                $task->version = (int) $task->getOriginal('version') + 1;
            }
        });
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN, true);
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->due_at !== null && $this->due_at->isPast();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(SalesTaskFile::class, 'task_id')->orderBy('id');
    }

    /** rep_updated_at keeps milliseconds (the phone counts in ms): written and read as text, like client_updated_at. */
    protected function repUpdatedAt(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? \Carbon\CarbonImmutable::parse($value, 'UTC') : null,
            set: fn ($value) => $value ? \Carbon\CarbonImmutable::parse($value)->utc()->format('Y-m-d H:i:s.v') : null,
        );
    }
}
