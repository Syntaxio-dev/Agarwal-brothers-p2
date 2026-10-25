<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use App\Sales\Support\PhoneNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/** A person at a client (name, position, phone). Shared by the whole team, like the client itself. */
class SalesContact extends Model
{
    use HasPhoneTime, SoftDeletes;

    protected bool $bumpsPhoneTimeOnEdit = true;

    protected $table = 'sales_contacts';

    protected $fillable = ['uuid', 'client_uuid', 'user_id', 'updated_by', 'name', 'position', 'phone', 'email', 'notes', 'client_updated_at'];

    protected static function booted(): void
    {
        static::creating(function (SalesContact $contact) {
            // made in the admin (not by a phone): the same defaults a phone would have sent
            $contact->uuid ??= (string) Str::uuid();
            $contact->user_id ??= auth()->id();
            $contact->client_updated_at ??= now();
        });

        static::saving(function (SalesContact $contact) {
            $contact->phone_normalized = PhoneNumber::normalize($contact->phone);
            $contact->updated_by = $contact->isDirty('updated_by') ? $contact->updated_by : (auth()->id() ?? $contact->updated_by);
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(SalesClient::class, 'client_uuid', 'uuid');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
