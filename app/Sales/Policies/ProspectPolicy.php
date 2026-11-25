<?php

namespace App\Sales\Policies;

/** The prospect pool (companies that do not work with us yet): the sales managers read it and can remove a record; reps use the phone app only. */
class ProspectPolicy extends ProductPolicy
{
    protected string $key = 'sales-prospects';
}
