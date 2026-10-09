<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = ['name', 'designation', 'photo', 'is_leader', 'quote', 'linkedin_url', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_leader' => 'boolean',
    ];
}
