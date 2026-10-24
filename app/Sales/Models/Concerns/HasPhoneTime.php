<?php

namespace App\Sales\Models\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * `client_updated_at` is the phone's own "last changed" time, and the rule "the newest change wins" compares it. The phone
 * counts in milliseconds: two edits within one second must stay two different versions. Laravel's normal date handling
 * cuts the time to whole seconds, so this column is written and read as text with milliseconds (DATETIME(3)).
 */
trait HasPhoneTime
{
    protected function clientUpdatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? CarbonImmutable::parse($value, 'UTC') : null,
            set: fn ($value) => $value ? CarbonImmutable::parse($value)->utc()->format('Y-m-d H:i:s.v') : null,
        );
    }
}
