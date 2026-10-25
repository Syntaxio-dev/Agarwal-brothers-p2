<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use App\Sales\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected $fillable = ['uuid', 'user_id', 'assigned_to', 'updated_by', 'name', 'company', 'phone', 'city', 'notes', 'client_updated_at'];

    protected static function booted(): void
    {
        static::creating(function (SalesClient $client) {
            // made in the admin (not by a phone): the same defaults a phone would have sent
            $client->uuid ??= (string) Str::uuid();
            $client->user_id ??= auth()->id();
            $client->client_updated_at ??= now();
        });

        // A deleted client takes its people with it (the phones hear about each one as a delete).
        static::deleted(function (SalesClient $client) {
            if (! $client->isForceDeleting()) {
                $client->contacts()->get()->each->delete();
            }
        });

        // The normalised number is what a later call-log match uses; it is never typed by hand.
        static::saving(function (SalesClient $client) {
            $client->phone_normalized = PhoneNumber::normalize($client->phone);
            $client->updated_by = $client->isDirty('updated_by') ? $client->updated_by : (auth()->id() ?? $client->updated_by);
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
