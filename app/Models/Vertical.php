<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Vertical extends Model
{
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
        return $this->name;
    }

    protected function seoFallbackDescription(): ?string
    {
        return $this->description ?: "Explore {$this->name} instruments and solutions from leading global brands, supplied and supported by Agarwal Brothers.";
    }

    protected function seoFallbackImage(): ?string
    {
        return $this->image;
    }
}
