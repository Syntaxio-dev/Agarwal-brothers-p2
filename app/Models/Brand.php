<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use LogsActivity;
    use HasSeo;
    use HasFactory;

    protected $fillable = ['name', 'slug', 'logo', 'country_id', 'description', 'is_active', 'seo_title', 'seo_description', 'og_image',];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // categories (and their products) are ON DELETE CASCADE; never wipe them implicitly.
        static::deleting(fn (Brand $brand) => $brand->categories()->exists() ? false : null);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    protected function seoName(): string
    {
        return $this->name;
    }

    protected function seoFallbackDescription(): ?string
    {
        return $this->description ?: "{$this->name} laboratory instruments and solutions, supplied and supported in India by Agarwal Brothers.";
    }

    protected function seoFallbackImage(): ?string
    {
        return $this->logo;
    }

    public function scopeWithoutCountry($query)
    {
        return $query->whereNull('country_id');
    }

    public function scopeWithoutLogo($query)
    {
        return $query->where(fn ($q) => $q->whereNull('logo')->orWhere('logo', ''));
    }
}
