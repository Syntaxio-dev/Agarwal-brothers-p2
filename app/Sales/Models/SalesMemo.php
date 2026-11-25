<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A rep's own note (app v1.20). Private to the rep: kept here only so a new phone gets it back; no admin screen reads it. */
class SalesMemo extends Model
{
    use HasPhoneTime, SoftDeletes;

    protected $table = 'sales_memos';

    protected $fillable = ['uuid', 'user_id', 'title', 'body', 'client_updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
