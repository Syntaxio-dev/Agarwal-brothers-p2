<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A rep's report for one day. One per rep per day; the rep can correct it, nobody deletes it. */
class SalesDailyReport extends Model
{
    use HasPhoneTime;

    protected $table = 'sales_daily_reports';

    protected $fillable = ['uuid', 'user_id', 'report_date', 'notes', 'tomorrow_plan', 'issues', 'figures', 'submitted_at', 'client_updated_at'];

    protected function casts(): array
    {
        return ['report_date' => 'date', 'figures' => 'array', 'submitted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
