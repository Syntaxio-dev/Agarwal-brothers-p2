<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Calls are a log: managers read them, nobody edits or deletes one (a rep's phone only ever adds). */
class CallPolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-calls';
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $record): bool
    {
        return false;
    }

    public function delete(User $user, Model $record): bool
    {
        return false;
    }
}
