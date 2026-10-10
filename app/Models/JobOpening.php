<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    use LogsActivity;
    protected $fillable = [
        'title', 'slug', 'location', 'employment_type', 'department',
        'summary', 'description', 'questions', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
    ];

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
