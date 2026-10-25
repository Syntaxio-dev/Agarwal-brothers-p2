<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One call between a rep and a number of the client pool (the phone sends only those: personal calls never leave it).
 * Calls are a log, so they are never edited or deleted from the phone.
 */
class SalesCall extends Model
{
    use HasPhoneTime;

    public const DIRECTIONS = ['incoming', 'outgoing', 'missed'];

    protected $table = 'sales_calls';

    protected $fillable = [
        'uuid', 'user_id', 'direction', 'number', 'client_uuid', 'contact_uuid', 'client_name', 'contact_name',
        'started_at', 'duration_seconds', 'client_updated_at',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'duration_seconds' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(SalesClient::class, 'client_uuid', 'uuid');
    }
}
