<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesMeeting extends Model
{
    use HasPhoneTime, SoftDeletes;

    /** status => label. A meeting written after it happened (every old one) is "done"; "planned" and "moved" are still to happen. */
    public const STATUSES = ['planned' => 'Planned', 'done' => 'Done', 'missed' => 'Missed', 'moved' => 'Moved'];

    public const OPEN = ['planned', 'moved'];

    /** result => label: what came out of a visit that was done. */
    public const RESULTS = ['price_asked' => 'Price asked', 'demo_booked' => 'Demo booked', 'order' => 'Order', 'not_interested' => 'Not interested'];

    /** How many days ahead a meeting can be planned (the phone checks the same). */
    public const PLAN_DAYS = 90;

    protected $table = 'sales_meetings';

    protected $fillable = [
        'uuid', 'user_id', 'client_uuid', 'client_name', 'contact_uuids', 'meeting_at', 'purpose', 'discussion', 'outcome',
        'next_followup_at', 'status', 'scheduled_at', 'ended_at', 'route_uuid', 'result', 'moved_from', 'time_manual', 'client_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'meeting_at' => 'datetime',
            'contact_uuids' => 'array',
            'next_followup_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'ended_at' => 'datetime',
            'moved_from' => 'datetime',
            'time_manual' => 'boolean',
        ];
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN, true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function resultLabel(): ?string
    {
        return $this->result ? (self::RESULTS[$this->result] ?? $this->result) : null;
    }

    /** The planned moment for one that has not happened (or was missed), the real one for one that was done. */
    public function plannedOrActualAt(): \Carbon\CarbonInterface
    {
        return ($this->isOpen() || $this->status === 'missed') && $this->scheduled_at ? $this->scheduled_at : $this->meeting_at;
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(SalesRoute::class, 'route_uuid', 'uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
