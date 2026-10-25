<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** A rep's scripts: the rep owns them (the phone API), managers of the product guide read every rep's but do not edit them. */
class ScriptPolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-products';
    }

    public function create(User $user): bool
    {
        return false; // written on the phone
    }

    public function update(User $user, Model $record): bool
    {
        return false;
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->manages($user); // a manager can remove a script that should not be there
    }
}
