<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
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
}
