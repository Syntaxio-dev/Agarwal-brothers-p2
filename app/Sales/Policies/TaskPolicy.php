<?php

namespace App\Sales\Policies;

/** Tasks for the field reps: the sales managers give them out, change them and check the status. Reps use the phone app only. */
class TaskPolicy extends ProductPolicy
{
    protected string $key = 'sales-tasks';
}
