<?php

namespace App\Sales\Policies;

/** The reps' bug reports: the sales managers read and answer them. */
class BugReportPolicy extends ProductPolicy
{
    protected string $key = 'sales-bug-reports';
}
