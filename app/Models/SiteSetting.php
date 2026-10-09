<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'catalogue_file', 'whatsapp_number', 'blogs_hero', 'news_hero', 'webinars_hero',
        'story_banner', 'story_image_1', 'story_image_2', 'story_mission_image',
        'story_intro', 'story_points', 'story_mission', 'story_vision', 'story_goal', 'founded_year', 'leadership_bg',
    ];

    protected $casts = [
        'story_points' => 'array',
        'founded_year' => 'integer',
    ];

    /** The site reads the most recent settings row. */
    public static function current(): ?self
    {
        return static::latest()->first();
    }
}
