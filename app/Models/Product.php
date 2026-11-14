<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use LogsActivity;
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
        'specs_general',
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
        'specs_general' => 'boolean',
        'features' => 'array',
        'advantages' => 'array',
        'gallery' => 'array',
        'documents' => 'array',
        'faqs' => 'array',
        'is_active' => 'boolean',
        'is_top_pick' => 'boolean',
    ];

    /** Scopes behind the "to do" filters and the dashboard counts. */
    public function scopeWithoutImage($query)
    {
        return $query->where(fn ($q) => $q->whereNull('image')->orWhere('image', ''));
    }

    public function scopeWithoutSpecs($query)
    {
        return $query->where(fn ($q) => $q->whereNull('specs')->orWhereRaw('JSON_LENGTH(specs) = 0'));
    }

    /** No custom Google title and description (the site then makes them automatically). */
    public function scopeWithoutSeo($query)
    {
        return $query
            ->where(fn ($q) => $q->whereNull('seo_title')->orWhere('seo_title', ''))
            ->where(fn ($q) => $q->whereNull('seo_description')->orWhere('seo_description', ''));
    }

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
        // "BSA224S Standard Laboratory Balance by Sartorius": model + type + brand, the way buyers search.
        $title = filled($this->heading) ? $this->heading : $this->name;
        $brand = $this->category?->brand?->name;

        if ($brand && ! Str::contains($title, $brand)) {
            $title .= ' by ' . $brand;
        }

        return mb_strlen($title) <= 27 ? $title . ': Specs & Quote' : $title;
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
