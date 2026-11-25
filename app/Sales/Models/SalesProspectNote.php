<?php

namespace App\Sales\Models;

use App\Models\User;
use App\Sales\Models\Concerns\HasPhoneTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One call to a prospect, made by any rep. A log: written once from a phone and never changed. */
class SalesProspectNote extends Model
{
    use HasPhoneTime;

    /** value => [label, still worth calling?] */
    public const OUTCOMES = [
        'no_answer' => ['No answer', true],
        'call_back' => ['Call back later', true],
        'interested' => ['Interested', true],
        'meeting_fixed' => ['Meeting fixed', true],
        'not_interested' => ['Not interested', false],
        'wrong_number' => ['Wrong number', false],
        'do_not_call' => ['Do not call', false],
        'converted' => ['Became a client', false],
    ];

    protected $table = 'sales_prospect_notes';

    protected $fillable = ['uuid', 'prospect_uuid', 'user_id', 'outcome', 'remark', 'next_followup_at', 'noted_at', 'client_updated_at'];

    public static function labelOf(?string $outcome): string
    {
        return $outcome === null || $outcome === 'new' ? 'Not called yet' : (self::OUTCOMES[$outcome][0] ?? $outcome);
    }

    protected function casts(): array
    {
        return ['next_followup_at' => 'datetime'];
    }

    protected function notedAt(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? \Carbon\CarbonImmutable::parse($value, 'UTC') : null,
            set: fn ($value) => $value ? \Carbon\CarbonImmutable::parse($value)->utc()->format('Y-m-d H:i:s.v') : null,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(SalesProspect::class, 'prospect_uuid', 'uuid');
    }
}
