<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Support\PhoneNumber;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesClient extends Model
{
    use HasPhoneTime, SoftDeletes;

    protected $table = 'sales_clients';

    protected $fillable = ['uuid', 'user_id', 'name', 'company', 'phone', 'city', 'notes', 'client_updated_at'];

    protected function casts(): array
    {
        return [];
    }

    protected static function booted(): void
    {
        // The normalised number is what a later call-log match uses; it is never typed by hand.
        static::saving(function (SalesClient $client) {
            $client->phone_normalized = PhoneNumber::normalize($client->phone);
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
