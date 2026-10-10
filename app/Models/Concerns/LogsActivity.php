<?php

namespace App\Models\Concerns;

use App\Support\ActivityLogger;

/**
 * Add to a model to record who created, changed or deleted it in the admin activity log.
 * A model can narrow it down by overriding activityEvents() (e.g. only ['deleted']).
 */
trait LogsActivity
{
    /** @return list<string> */
    public function activityEvents(): array
    {
        return ['created', 'updated', 'deleted'];
    }

    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if (in_array('created', $model->activityEvents(), true)) {
                ActivityLogger::record('created', $model);
            }
        });

        static::updated(function ($model) {
            if (! in_array('updated', $model->activityEvents(), true)) {
                return;
            }

            $changes = ActivityLogger::diff($model);

            if ($changes) {
                ActivityLogger::record('updated', $model, $changes);
            }
        });

        static::deleted(function ($model) {
            if (in_array('deleted', $model->activityEvents(), true)) {
                ActivityLogger::record('deleted', $model);
            }
        });
    }
}
