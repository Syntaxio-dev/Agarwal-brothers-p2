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
        'checkin_at', 'checkin_lat', 'checkin_lng', 'checkin_accuracy_m', 'checkin_distance_m', 'checkin_received_at', 'selfie_path', 'selfie_mime',
    ];

    /** A check-in further than this from the client's saved spot is flagged "away from client" (the phone uses the same number). */
    public const CHECKIN_RADIUS_M = 300;

    /** Straight-line metres between two spots (haversine). */
    public static function distanceBetween(float $latA, float $lngA, float $latB, float $lngB): int
    {
        $x = sin(deg2rad($latB - $latA) / 2) ** 2 + cos(deg2rad($latA)) * cos(deg2rad($latB)) * sin(deg2rad($lngB - $lngA) / 2) ** 2;

        return (int) round(2 * 6371000 * asin(min(1, sqrt($x))));
    }

    /** none | verified | away | no_client_location */
    public function checkinState(): string
    {
        if (! $this->checkin_at) {
            return 'none';
        }
        if ($this->checkin_distance_m === null) {
            return 'no_client_location';
        }

        return $this->checkin_distance_m <= self::CHECKIN_RADIUS_M ? 'verified' : 'away';
    }

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
            'checkin_at' => 'datetime',
            'checkin_received_at' => 'datetime',
            'checkin_lat' => 'float',
            'checkin_lng' => 'float',
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
