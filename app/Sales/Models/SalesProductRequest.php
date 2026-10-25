<?php

namespace App\Sales\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A rep asking the manager to add or correct a note in the product guide. The manager answers in the admin. */
class SalesProductRequest extends Model
{
    public const STATUSES = ['open' => 'Open', 'done' => 'Done', 'declined' => 'Declined'];

    protected $table = 'sales_product_requests';

    protected $fillable = ['uuid', 'user_id', 'product_uuid', 'product_name', 'body', 'status', 'reply', 'handled_by', 'handled_at', 'client_updated_at'];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime', 'client_updated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SalesProduct::class, 'product_uuid', 'uuid');
    }
}
