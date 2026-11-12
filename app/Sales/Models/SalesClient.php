<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use App\Sales\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * A client of the company, shared by the whole sales team (one pool). `user_id` is who added it, `assigned_to` is the rep who
 * works it (a manager sets it in the admin). The people at the client are SalesContact rows.
 */
class SalesClient extends Model
{
    use HasPhoneTime, SoftDeletes;

    /** Edits made in the admin get a new phone time, so the phones take them (see HasPhoneTime). */
    protected bool $bumpsPhoneTimeOnEdit = true;

    protected $table = 'sales_clients';

    protected $fillable = ['uuid', 'user_id', 'assigned_to', 'updated_by', 'name', 'company', 'phone', 'city', 'notes', 'lat', 'lng', 'location_accuracy_m', 'location_at', 'location_source', 'client_updated_at'];

    /** True while a phone's push is being saved: the spot then counts as the rep's, otherwise (the admin) as the office's. */
    public bool $fromPhone = false;

    /** A spot from what a manager pastes: "26.9124, 75.7873", a Google Maps link with @lat,lng or ?q=lat,lng. A short link (maps.app.goo.gl) cannot be read: open it first and paste the long one. */
    public static function parseSpot(?string $text): ?array
    {
        $text = urldecode(trim((string) $text));
        $number = '(-?\d{1,3}\.\d{3,})';

        foreach (['/!3d' . $number . '!4d' . $number . '/', '/@' . $number . ',' . $number . '/', '/[?&](?:q|ll|query)=' . $number . ',' . $number . '/', '/^\s*' . $number . '\s*[,; ]\s*' . $number . '\s*$/'] as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                [$lat, $lng] = [round((float) $m[1], 6), round((float) $m[2], 6)];

                return abs($lat) <= 90 && abs($lng) <= 180 && ! ($lat == 0 && $lng == 0) ? ['lat' => $lat, 'lng' => $lng] : null;
            }
        }

        return null;
    }

    public function hasLocation(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    protected function casts(): array
    {
        return ['lat' => 'float', 'lng' => 'float', 'location_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (SalesClient $client) {
            // made in the admin (not by a phone): the same defaults a phone would have sent
            $client->uuid ??= (string) Str::uuid();
            $client->user_id ??= auth()->id();
            $client->client_updated_at ??= now();
        });

        // who-sees-what is cached for a minute (SalesInsights): any change of a client clears it
        static::saved(fn () => \App\Sales\Support\SalesInsights::forgetClientSets());
        static::deleted(fn () => \App\Sales\Support\SalesInsights::forgetClientSets());
        static::restored(fn () => \App\Sales\Support\SalesInsights::forgetClientSets());

        // A deleted client takes its people with it (the phones hear about each one as a delete).
        static::deleted(function (SalesClient $client) {
            if (! $client->isForceDeleting()) {
                $client->contacts()->get()->each->delete();
            }
        });

        // The manager set or moved the spot (the office, never a phone): phones must hear about it, and visits already checked in are measured again.
        static::saving(function (SalesClient $client) {
            if (! $client->fromPhone && $client->isDirty(['lat', 'lng'])) {
                $client->location_source = $client->lat === null ? null : 'office';
            }
            if ($client->exists && $client->isDirty(['lat', 'lng']) && ! $client->isDirty('client_updated_at')) {
                $old = $client->getOriginal('client_updated_at');
                $client->client_updated_at = $old && $old->gte(now()) ? $old->copy()->addMillisecond() : now();
            }
        });

        static::saved(function (SalesClient $client) {
            if ($client->wasChanged(['lat', 'lng'])) {
                SalesMeeting::where('client_uuid', $client->uuid)->whereNotNull('checkin_at')->get()->each(fn (SalesMeeting $m) => $m->forceFill([
                    'checkin_distance_m' => $client->hasLocation() ? SalesMeeting::distanceBetween($client->lat, $client->lng, (float) $m->checkin_lat, (float) $m->checkin_lng) : null,
                ])->save());
            }
        });

        // The normalised number is what a later call-log match uses; it is never typed by hand.
        static::saving(function (SalesClient $client) {
            $client->phone_normalized = PhoneNumber::normalize($client->phone);
            $client->updated_by = $client->isDirty('updated_by') ? $client->updated_by : (auth()->id() ?? $client->updated_by);
        });
    }

    /** The reps the manager let see this client (and its people) although it is not theirs. */
    public function viewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sales_client_access', 'client_id', 'user_id')->withTimestamps();
    }

    /**
     * What a rep sees: the clients they added, the ones given to them, and the ones the manager opened for them. A manager sees all of it in
     * the admin (this scope is only used for the phones).
     */
    public function scopeVisibleTo($query, User $user)
    {
        if (! config('sales.private_pool', true)) {
            return $query;
        }

        return $query->where(function ($w) use ($user) {
            $w->where('user_id', $user->id)
                ->orWhere('assigned_to', $user->id)
                ->orWhereIn('id', fn ($s) => $s->select('client_id')->from('sales_client_access')->where('user_id', $user->id));
        });
    }

    /** Who added the client. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Who changed it last. */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** The rep who works the client. */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(SalesContact::class, 'client_uuid', 'uuid');
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(SalesMeeting::class, 'client_uuid', 'uuid');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(SalesCall::class, 'client_uuid', 'uuid');
    }
}
