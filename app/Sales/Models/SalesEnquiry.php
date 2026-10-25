<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * What a client asked a rep for ("price of an oven and a furnace"), with the follow-ups until it is won or lost.
 * Private to the rep who made it; managers see every rep's enquiries.
 */
class SalesEnquiry extends Model
{
    use HasPhoneTime, SoftDeletes;

    /** status => label. The first six are "open": somebody still has to act. */
    public const STATUSES = [
        'new' => 'New',
        'price_to_send' => 'Price to send',
        'price_sent' => 'Price sent',
        'callback' => 'Call back',
        'meeting_planned' => 'Meeting planned',
        'negotiation' => 'Negotiation',
        'won' => 'Order received',
        'lost' => 'Lost',
    ];

    public const OPEN = ['new', 'price_to_send', 'price_sent', 'callback', 'meeting_planned', 'negotiation'];

    public const CLOSED = ['won', 'lost'];

    protected $table = 'sales_enquiries';

    protected $fillable = [
        'uuid', 'user_id', 'client_uuid', 'client_name', 'contact_uuid', 'contact_name', 'status', 'items', 'opened_at',
        'next_followup_at', 'closed_at', 'timeline', 'client_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'timeline' => 'array',
            'opened_at' => 'datetime',
            'next_followup_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN, true);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    /** "Oven x2, Furnace" for lists. */
    public function itemsSummary(int $max = 3): string
    {
        $items = collect($this->items ?? []);
        $names = $items->take($max)->map(fn ($i) => ($i['product'] ?? '?') . (($i['quantity'] ?? 1) > 1 ? ' x' . $i['quantity'] : ''))->implode(', ');

        return $names . ($items->count() > $max ? ' +' . ($items->count() - $max) . ' more' : '');
    }

    public function budgetPaise(): int
    {
        return (int) collect($this->items ?? [])->sum(fn ($i) => (int) ($i['budget_paise'] ?? 0));
    }

    public function isOverdue(): bool
    {
        return $this->isOpen() && $this->next_followup_at !== null && $this->next_followup_at->isPast();
    }
}
