<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A rep's own to-do (app v1.20). Private to the rep: kept here only so a new phone gets the list back; no admin screen reads it. */
class SalesTodo extends Model
{
    use HasPhoneTime, SoftDeletes;

    protected $table = 'sales_todos';

    protected $fillable = ['uuid', 'user_id', 'title', 'done', 'done_at', 'due_date', 'client_updated_at'];

    protected function casts(): array
    {
        // done_at must be a date object: the pull formats it (a plain string made EVERY sync of a rep with a ticked to-do fail, 25 Nov 2026)
        return ['done' => 'boolean', 'done_at' => 'datetime', 'due_date' => 'date:Y-m-d'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
