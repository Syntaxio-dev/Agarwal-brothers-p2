<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use HasFactory;

protected $fillable = ['title', 'subtitle', 'image', 'alt_text', 'video_url', 'video', 'sort_order', 'is_active', 'link_url'];

    /** Slides with a picture but no description for screen readers. */
    public function scopeWithoutAlt($query)
    {
        return $query->whereNotNull('image')->where('image', '!=', '')
            ->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''));
    }
}
