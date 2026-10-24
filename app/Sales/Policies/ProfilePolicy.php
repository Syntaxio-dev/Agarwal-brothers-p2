<?php

namespace App\Sales\Policies;

use App\Models\User;
use App\Sales\Models\SalesProfile;

/** Managers create and change reps; a rep can only look at their own profile. */
class ProfilePolicy
{
    private function manages(User $user): bool
    {
        return $user->canManage('sales-reps');
    }

    public function viewAny(User $user): bool
    {
        return $this->manages($user);
    }

    public function view(User $user, SalesProfile $profile): bool
    {
        return $this->manages($user) || (int) $profile->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $this->manages($user);
    }

    public function update(User $user, SalesProfile $profile): bool
    {
        return $this->manages($user);
    }

    /** Reps are switched off, never deleted: their expenses and meetings must stay readable. */
    public function delete(User $user, SalesProfile $profile): bool
    {
        return false;
    }
}
