<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasSeo;
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'heading',
        'model_group',
        'short_description',
        'overview',
        'features',
        'advantages',
        'specs',
        'image',
        'gallery',
        'video_url',
        'documents',
        'faqs',
        'is_active',
        'is_top_pick',
        'top_pick_order', 'seo_title', 'seo_description', 'og_image',];

    protected $casts = [
        'specs' => 'array',
        'features' => 'array',
        'advantages' => 'array',
        'gallery' => 'array',
        'documents' => 'array',
        'faqs' => 'array',
        'is_active' => 'boolean',
        'is_top_pick' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    protected function seoName(): string
    {
        return $this->name;
    }

    protected function seoFallbackDescription(): ?string
    {
        return $this->short_description
            ?: $this->overview
            ?: "{$this->name}" . ($this->category?->brand ? " from {$this->category->brand->name}" : '') . ', supplied and supported in India by Agarwal Brothers.';
    }

    protected function seoFallbackImage(): ?string
    {
        return $this->image ?: ($this->gallery[0] ?? null) ?: $this->category?->brand?->logo;
    }
}
