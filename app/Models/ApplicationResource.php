<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationResource extends Model
{
    public const CATEGORIES = [
        'appnote' => 'Application Note',
        'guide' => 'Technical Guide',
        'video' => 'Video / Webinar',
        'brochure' => 'Brochure',
    ];

    protected $fillable = [
        'title', 'category', 'source', 'description', 'cover_image',
        'pdf', 'link_url', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
