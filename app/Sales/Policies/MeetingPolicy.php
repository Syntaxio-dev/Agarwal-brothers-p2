<?php

namespace App\Sales\Policies;

class MeetingPolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-meetings';
    }
}
