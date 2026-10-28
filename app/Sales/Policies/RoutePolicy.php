<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Routes belong to the meetings screen: a rep owns their own (from the phone), managers read every rep's and do not edit. */
class RoutePolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-meetings';
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->isRep($user) && $this->owns($user, $record);
    }
}
