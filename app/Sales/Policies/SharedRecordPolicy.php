<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * The client pool belongs to the whole team: every active rep (and every manager of the area) sees all of it and can add to it
 * to it. Changing or deleting is narrower: the rep who added a record, or a manager.
 */
abstract class SharedRecordPolicy extends OwnedRecordPolicy
{
    public function view(User $user, Model $record): bool
    {
        return $this->manages($user) || $this->isRep($user);
    }

    public function create(User $user): bool
    {
        return $this->manages($user) || $this->isRep($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->manages($user) || ($this->isRep($user) && $this->owns($user, $record));
    }
}
