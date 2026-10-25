<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Enquiries: a rep sees and changes their own, managers read every rep's but do not edit (the rep owns the follow-up). */
class EnquiryPolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-enquiries';
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->isRep($user) && $this->owns($user, $record);
    }
}
