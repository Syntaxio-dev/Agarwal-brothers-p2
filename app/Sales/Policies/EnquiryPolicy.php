<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Enquiries: a rep sees and changes their own, managers read every rep's and only those with the move right put one in another stage (the rep owns the follow-up). */
class EnquiryPolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-enquiries';
    }

    /** Drag an enquiry to another stage from the office board: needs the area AND its own "move" right (the rep still owns the enquiry). */
    public function moveStage(User $user, Model $record): bool
    {
        return $this->manages($user) && $user->canManage('sales-enquiries-move');
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->isRep($user) && $this->owns($user, $record);
    }
}
