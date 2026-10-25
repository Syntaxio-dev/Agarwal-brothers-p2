<?php

namespace App\Sales\Models\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * `client_updated_at` is the phone's own "last changed" time, and the rule "the newest change wins" compares it. The phone
 * counts in milliseconds: two edits within one second must stay two different versions. Laravel's normal date handling
 * cuts the time to whole seconds, so this column is written and read as text with milliseconds (DATETIME(3)).
 *
 * A model that sets `$bumpsPhoneTimeOnEdit = true` (the shared client pool) also gets a new `client_updated_at` whenever it is
 * changed WITHOUT one, which is exactly an edit made in the admin: phones must see it as newer than what they hold.
 */
trait HasPhoneTime
{
    protected static function bootHasPhoneTime(): void
    {
        static::saving(function ($model) {
            if (! property_exists($model, 'bumpsPhoneTimeOnEdit') || ! $model->exists || $model->isDirty('client_updated_at')) {
                return;
            }

            if (array_diff(array_keys($model->getDirty()), ['updated_at']) !== []) {
                $model->client_updated_at = now();
            }
        });
    }

    protected function clientUpdatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? CarbonImmutable::parse($value, 'UTC') : null,
            set: fn ($value) => $value ? CarbonImmutable::parse($value)->utc()->format('Y-m-d H:i:s.v') : null,
        );
    }
}
