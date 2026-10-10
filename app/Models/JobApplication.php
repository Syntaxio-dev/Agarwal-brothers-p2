<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasTeamNotes;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use LogsActivity;
    use HasTeamNotes;

    protected $fillable = [
        'job_opening_id', 'name', 'email', 'phone', 'position', 'preferred_location',
        'department', 'message', 'resume', 'answers', 'status', 'notes',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function opening()
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }

    /** Day-to-day work on these is written to their own team notes; only removal is added to the activity log. */
    public function activityEvents(): array
    {
        return ['deleted'];
    }
}
