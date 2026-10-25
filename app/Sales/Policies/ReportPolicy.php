<?php

namespace App\Sales\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/** Daily reports: managers read them, the rep who wrote one corrects it from the phone, nobody deletes one. */
class ReportPolicy extends OwnedRecordPolicy
{
    protected function area(): string
    {
        return 'sales-reports';
    }

    public function delete(User $user, Model $record): bool
    {
        return false;
    }
}
