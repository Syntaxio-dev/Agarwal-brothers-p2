<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Something sold with the instruments, for example a calibration test kit. It belongs to one brand, or to no brand
 * (a general accessory shown for every brand). It is only a small card with a picture and a short text: visitors add it
 * to their enquiry list, it has no page of its own.
 */
class Accessory extends Model
{
    use LogsActivity;

    /** Prefix of an accessory in the browser's enquiry list, so it can never clash with a product slug. */
    public const LIST_PREFIX = 'accessory--';

    protected $fillable = ['brand_id', 'name', 'slug', 'short_description', 'image', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::saving(function (Accessory $accessory) {
            if (blank($accessory->slug)) {
                $accessory->slug = static::uniqueSlug($accessory->name);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'accessory';
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function listKey(): string
    {
        return static::LIST_PREFIX . $this->slug;
    }

    /** Active accessories that may be shown: general ones, or those of an active brand. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('brand_id')->orWhereHas('brand', fn ($b) => $b->where('is_active', true)));
    }

    /** The accessories to offer with a brand: the brand's own first, then the general ones. */
    public static function forBrand(?Brand $brand)
    {
        return static::visible()
            ->when($brand,
                fn ($q) => $q->where(fn ($w) => $w->where('brand_id', $brand->id)->orWhereNull('brand_id')),
                fn ($q) => $q->whereNull('brand_id'))
            ->orderByRaw('brand_id IS NULL')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
