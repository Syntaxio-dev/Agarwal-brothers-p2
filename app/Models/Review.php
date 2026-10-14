<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'designation', 'organization', 'content', 'rating', 'sort_order', 'is_active', 'is_featured', 'avatar'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'rating' => 'integer',
    ];
}
