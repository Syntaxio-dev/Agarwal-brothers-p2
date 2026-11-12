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

    protected $fillable = ['name', 'slug', 'logo', 'brochure', 'country_id', 'description', 'is_active', 'seo_title', 'seo_description', 'og_image',];

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
        // The words people type into Google: brand + what we do + where.
        // Kept short enough for the whole title to stay near 60 characters.
        return $this->name . (mb_strlen($this->name) <= 16 ? ' Supplier in Jaipur, India' : ' Supplier, Jaipur');
    }

    protected function seoFallbackDescription(): ?string
    {
        $lead = "{$this->name} laboratory instruments supplied and supported in India by Agarwal Brothers, Jaipur.";
        $first = $this->description ? trim(strtok(strip_tags((string) $this->description), '.')) : '';

        return $first !== '' ? "{$lead} {$first}." : $lead;
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
