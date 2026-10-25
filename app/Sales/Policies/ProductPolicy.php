<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** The product guide: managers of the Sales team area write it. Reps read it through the phone API only, never the admin. */
class ProductPolicy
{
    protected string $key = 'sales-products';

    private function manages(User $user): bool
    {
        return $user->canManage($this->key);
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
