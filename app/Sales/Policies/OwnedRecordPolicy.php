<?php

namespace App\Sales\Policies;

use App\Models\User;
use App\Sales\Models\SalesProfile;
use Illuminate\Database\Eloquent\Model;

/**
 * The rule for every record a rep creates: a manager (a role that can open the matching admin area) sees all of it,
 * a field rep sees and changes only their own. Nobody else gets anything.
 */
abstract class OwnedRecordPolicy
{
    /** The admin area key (config/roles.php) whose managers may see everything. */
    abstract protected function area(): string;

    protected function manages(User $user): bool
    {
        return $user->canManage($this->area());
    }

    protected function isRep(User $user): bool
    {
        return $user->is_active && $user->role === SalesProfile::ROLE;
    }

    protected function owns(User $user, Model $record): bool
    {
        return (int) $record->getAttribute('user_id') === (int) $user->getKey();
    }

    public function viewAny(User $user): bool
    {
        return $this->manages($user) || $this->isRep($user);
    }

    public function view(User $user, Model $record): bool
    {
        return $this->manages($user) || ($this->isRep($user) && $this->owns($user, $record));
    }

    public function create(User $user): bool
    {
        return $this->isRep($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->isRep($user) && $this->owns($user, $record);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->manages($user) || ($this->isRep($user) && $this->owns($user, $record));
    }
}
