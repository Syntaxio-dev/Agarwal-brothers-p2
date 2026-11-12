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

    /** status => label: the six stages of the pipeline. The first four are "open": somebody still has to act. */
    public const STATUSES = [
        'qualification' => 'Qualification',
        'need_analysis' => 'Need Analysis',
        'proposal' => 'Proposal / Price Quote',
        'negotiation' => 'Negotiation / Review',
        'won' => 'Closed Won',
        'lost' => 'Closed Lost',
    ];

    public const OPEN = ['qualification', 'need_analysis', 'proposal', 'negotiation'];

    /**
     * The stages before app v1.15 (eight of them) and what each one means now. A phone or a stored row that still has an old name is
     * read through this list; a phone older than v1.15 is sent the old name that is closest (LEGACY_FOR_OLD_APPS).
     */
    public const LEGACY = ['new' => 'qualification', 'callback' => 'qualification', 'meeting_planned' => 'need_analysis', 'price_to_send' => 'proposal', 'price_sent' => 'proposal'];

    public const LEGACY_FOR_OLD_APPS = ['qualification' => 'new', 'need_analysis' => 'meeting_planned', 'proposal' => 'price_to_send'];

    public static function normalizeStatus(?string $status): ?string
    {
        return self::LEGACY[$status] ?? $status;
    }

    public const CLOSED = ['won', 'lost'];

    /** source => label: how the enquiry came through (optional; the phone sends it from app v1.7). */
    public const SOURCES = ['call' => 'Call', 'walkin' => 'Walk-in', 'whatsapp' => 'WhatsApp', 'reference' => 'Reference'];

    /**
     * lead_status => label: where the follow-up of a lead (= this enquiry) stands (app v1.15). The phone and the rep desk write them;
     * a null status is an enquiry from before v1.15 and reads as "New lead". `deal_done` and `deal_lost` finish the enquiry.
     */
    public const LEAD_STATUSES = [
        'new' => 'New lead',
        'price_sent' => 'Price sent',
        'better_option' => 'Asked for better option',
        'call_not_picking' => 'Call not picking',
        'want_less_price' => 'Want less price',
        'interested' => 'Interested',
        'waiting' => 'Waiting for decision',
        'not_interested' => 'Not interested',
        'deal_done' => 'Deal done',
        'deal_lost' => 'Deal lost',
    ];

    /** lead_status => the stage that finishes the enquiry. */
    public const LEAD_CLOSES = ['deal_done' => 'won', 'deal_lost' => 'lost'];

    public const LEAD_REMARK_MAX = 300;

    /**
     * The reminder a status suggests (the phone suggests the same): [days, hour of the day] or ['hours' => n]. Only a suggestion, the rep
     * changes it; the finishing statuses have none.
     */
    public const LEAD_DELAYS = [
        'new' => [1, 10], 'price_sent' => [3, 10], 'better_option' => [1, 10], 'call_not_picking' => ['hours' => 3],
        'want_less_price' => [1, 10], 'interested' => [2, 10], 'waiting' => [7, 10], 'not_interested' => [30, 10],
    ];

    /** The suggested next reminder for a lead status from `$from` (business time), or null for Deal done / Deal lost. */
    public static function suggestedReminder(string $leadStatus, ?\Carbon\CarbonInterface $from = null): ?\Carbon\CarbonImmutable
    {
        $rule = self::LEAD_DELAYS[$leadStatus] ?? null;
        if ($rule === null) {
            return null;
        }

        $now = \Carbon\CarbonImmutable::instance($from ?? now())->timezone(config('sales.timezone'));

        return isset($rule['hours']) ? $now->addHours($rule['hours']) : $now->startOfDay()->addDays($rule[0])->setTime($rule[1], 0);
    }

    protected $table = 'sales_enquiries';

    protected $fillable = [
        'uuid', 'user_id', 'client_uuid', 'client_name', 'contact_uuid', 'contact_name', 'status', 'source', 'lead_status', 'lead_remark',
        'last_followup_at', 'items', 'opened_at', 'next_followup_at', 'closed_at', 'timeline', 'client_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'timeline' => 'array',
            'opened_at' => 'datetime',
            'next_followup_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_followup_at' => 'datetime',
        ];
    }

    public function leadStatusLabel(): string
    {
        return self::LEAD_STATUSES[$this->lead_status ?? 'new'] ?? ucfirst(str_replace('_', ' ', (string) $this->lead_status));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceLabel(): ?string
    {
        return $this->source ? (self::SOURCES[$this->source] ?? $this->source) : null;
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
