<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Vertical extends Model
{
    use LogsActivity;
    use HasSeo;
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'image', 'sort_order', 'is_active', 'seo_title', 'seo_description', 'og_image',];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    protected function seoName(): string
    {
        return mb_strlen($this->name) <= 24 ? $this->name . ' Supplier, Jaipur' : $this->name;
    }

    protected function seoFallbackDescription(): ?string
    {
        // Always the same clear promise (a long page description used to be cut in the middle of a sentence).
        return "{$this->name}: models, specifications and quotes from leading global brands, supplied and supported in India by Agarwal Brothers, Jaipur.";
    }

    protected function seoFallbackImage(): ?string
    {
        return $this->image;
    }
}
