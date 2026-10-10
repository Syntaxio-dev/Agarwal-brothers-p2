<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasTeamNotes;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use LogsActivity;
    use HasTeamNotes;

    protected $fillable = ['name', 'email', 'phone', 'company', 'city', 'subject', 'message', 'status'];

    /** Day-to-day work on these is written to their own team notes; only removal is added to the activity log. */
    public function activityEvents(): array
    {
        return ['deleted'];
    }
}
