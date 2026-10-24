<?php

namespace App\Sales\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesSyncBatch extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'sales_sync_batches';

    protected $fillable = ['user_id', 'received', 'accepted', 'duplicates', 'rejected', 'errors', 'app_version', 'duration_ms'];

    protected function casts(): array
    {
        return ['errors' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
