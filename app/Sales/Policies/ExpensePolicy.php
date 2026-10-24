<?php

namespace App\Sales\Policies;

use App\Models\User;
use App\Sales\Models\SalesExpense;
use Illuminate\Database\Eloquent\Model;

class ExpensePolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-expenses';
    }

    /** A rep can correct or delete an expense only until a manager has decided on it. */
    public function update(User $user, Model $record): bool
    {
        return parent::update($user, $record) && $record instanceof SalesExpense && $record->isPending();
    }

    public function delete(User $user, Model $record): bool
    {
        if ($this->manages($user)) {
            return true;
        }

        return $this->isRep($user) && $this->owns($user, $record) && $record instanceof SalesExpense && $record->isPending();
    }

    /** Approve or reject: managers only, never the rep who wrote it. */
    public function review(User $user, SalesExpense $expense): bool
    {
        return $this->manages($user) && ! $this->owns($user, $expense);
    }
}
