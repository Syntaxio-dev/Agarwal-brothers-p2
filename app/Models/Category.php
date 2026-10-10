<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use LogsActivity;
    use HasSeo;
    use HasFactory;

    protected $fillable = ['brand_id', 'name', 'slug', 'heading', 'description', 'content', 'faqs', 'image', 'sort_order', 'seo_title', 'seo_description', 'og_image',];

    protected $casts = [
        'faqs' => 'array',
    ];

    protected static function booted(): void
    {
        // products are ON DELETE CASCADE; never wipe them implicitly.
        static::deleting(fn (Category $category) => $category->products()->exists() ? false : null);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function verticals()
    {
        return $this->belongsToMany(Vertical::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    protected function seoName(): string
    {
        return $this->name . ($this->brand ? ' by ' . $this->brand->name : '');
    }

    protected function seoFallbackDescription(): ?string
    {
        return $this->description ?: "{$this->name}" . ($this->brand ? " from {$this->brand->name}" : '') . ', available with installation, training and service from Agarwal Brothers.';
    }

    protected function seoFallbackImage(): ?string
    {
        return $this->image ?: $this->brand?->logo;
    }
}
