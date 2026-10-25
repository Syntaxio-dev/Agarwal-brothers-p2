<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** The shared position list: managers of the clients area keep it tidy (the reps add through the app). */
class PositionPolicy
{
    private function manages(User $user): bool
    {
        return $user->canManage('sales-positions');
    }

    public function viewAny(User $user): bool
    {
        return $this->manages($user);
    }

    public function view(User $user, Model $record): bool
    {
        return $this->manages($user);
    }

    public function create(User $user): bool
    {
        return $this->manages($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->manages($user);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->manages($user);
    }
}
