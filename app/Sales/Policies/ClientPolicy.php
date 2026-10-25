<?php

namespace App\Sales\Policies;

class ClientPolicy extends SharedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-clients';
    }
}
