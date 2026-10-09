<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Insight extends Model
{
    use HasFactory;

    public const TZ = 'Asia/Kolkata';

    protected $fillable = [
        'type', 'brand_id', 'title', 'slug', 'excerpt', 'content', 'image', 'pdf',
        'event_date', 'starts_at', 'time_tbd', 'venue', 'registration_url', 'recording_url',
        'is_active', 'is_featured',
    ];

    protected $casts = [
        'event_date' => 'date',
        'starts_at' => 'datetime',
        'time_tbd' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Keep event_date in sync so month/year filters and the homepage keep working.
        static::saving(function (Insight $insight) {
            if ($insight->type === 'webinar' && $insight->starts_at) {
                $insight->event_date = $insight->starts_at->toDateString();
            }
        });
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /** The stored wall-clock time is India time, whatever the app timezone is. */
    public function getStartIstAttribute(): ?Carbon
    {
        return $this->starts_at?->copy()->shiftTimezone(self::TZ);
    }

    public function isUpcoming(): bool
    {
        $start = $this->start_ist;

        if (! $start) {
            return false;
        }

        $now = now(self::TZ);

        return $this->time_tbd ? $start->copy()->endOfDay()->gte($now) : $start->gte($now);
    }
}
