<?php

namespace App\Sales\Policies;

class ClientPolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-clients';
    }
}
