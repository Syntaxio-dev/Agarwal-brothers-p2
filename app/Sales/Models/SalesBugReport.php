<?php

namespace App\Sales\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A rep's "report a bug" from the app (v1.19). Sent once; a manager answers in the admin. Only what the rep typed and the app version arrive. */
class SalesBugReport extends Model
{
    public const STATUSES = ['open' => 'Open', 'fixed' => 'Fixed', 'declined' => 'Not a bug / declined'];

    public const AREAS = [
        'expenses' => 'Expenses', 'clients' => 'Clients', 'prospects' => 'Prospects', 'leads' => 'Leads', 'meetings' => 'Meetings',
        'guide' => 'Guide', 'calls' => 'Calls', 'sync' => 'Sync / sign in', 'other' => 'Something else',
    ];

    protected $table = 'sales_bug_reports';

    protected $fillable = ['uuid', 'user_id', 'area', 'body', 'app_version', 'status', 'reply', 'handled_by', 'handled_at', 'client_updated_at'];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime', 'client_updated_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
