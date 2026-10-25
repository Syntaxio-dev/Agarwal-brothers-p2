<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A rep's own pitch for a product, in their own words ("what I say about weighing balances"). Written on the phone, kept on the
 * office copy so a new phone gets it back. Private to the rep; managers can read it, only the rep changes or deletes it.
 */
class SalesScript extends Model
{
    use HasPhoneTime, SoftDeletes;

    protected $table = 'sales_scripts';

    protected $fillable = ['uuid', 'user_id', 'product_uuid', 'product_name', 'title', 'body', 'client_updated_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SalesProduct::class, 'product_uuid', 'uuid');
    }
}
