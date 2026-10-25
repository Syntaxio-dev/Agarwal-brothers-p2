<?php

namespace App\Sales\Policies;

class ContactPolicy extends SharedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-clients';
    }
}
