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

    protected $table = 'sales_meetings';

    protected $fillable = [
        'uuid', 'user_id', 'client_uuid', 'client_name', 'contact_uuids', 'meeting_at', 'purpose', 'discussion', 'outcome',
        'next_followup_at', 'client_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'meeting_at' => 'datetime',
            'contact_uuids' => 'array',
            'next_followup_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
