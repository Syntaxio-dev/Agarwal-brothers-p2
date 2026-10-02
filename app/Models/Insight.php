<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'title', 'slug', 'excerpt', 'content', 'image', 'event_date', 'is_active'];
    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
    ];
}