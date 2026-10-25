<?php

namespace App\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One product of the guide. Only managers write it (admin: Sales team > Product guide); the phones only read it.
 * The lists are stored as JSON so a product is always one row.
 */
class SalesProduct extends Model
{
    protected $table = 'sales_products';

    protected $fillable = [
        'name', 'group', 'sort', 'premium_brands', 'economy_brands', 'lite_brands', 'summary', 'variants', 'industries',
        'key_parameters', 'short_term_problems', 'long_term_problems', 'top_brands', 'is_active', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'premium_brands' => 'array',
            'economy_brands' => 'array',
            'lite_brands' => 'array',
            'variants' => 'array',
            'key_parameters' => 'array',
            'short_term_problems' => 'array',
            'long_term_problems' => 'array',
            'top_brands' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            $product->uuid ??= (string) Str::uuid();
            $product->slug = static::uniqueSlug($product->name);
        });

        static::updating(function (self $product) {
            if ($product->isDirty('name')) {
                $product->slug = static::uniqueSlug($product->name, $product->getKey());
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::limit(Str::slug($name) ?: 'product', 200, '');
        $slug = $base;

        for ($n = 2; static::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists(); $n++) {
            $slug = "{$base}-{$n}";
        }

        return $slug;
    }

    /** The groups already in use (suggestions in the admin form). */
    public static function groups(): array
    {
        return static::query()->whereNotNull('group')->distinct()->orderBy('group')->pluck('group')->all();
    }

    /**
     * A short stamp of the whole active list: the phone sends the stamp it holds, and the office answers "nothing new" when it is
     * the same one. Any edit, add, hide or delete changes it.
     */
    public static function version(): string
    {
        $rows = static::query()->where('is_active', true)->orderBy('id')->get(['id', 'updated_at'])
            ->map(fn (self $p) => $p->id . ':' . $p->updated_at?->getTimestamp())
            ->implode(',');

        return substr(sha1($rows), 0, 16);
    }
}
