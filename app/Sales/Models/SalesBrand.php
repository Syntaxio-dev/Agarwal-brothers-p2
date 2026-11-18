<?php

namespace App\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * One brand of the brand guide (admin: Sales team > Brand guide). Only managers write it; the phones read it with the product guide.
 * Which products the brand sits in, and in which budget tier, is NOT stored here: the phone works it out from the product guide.
 */
class SalesBrand extends Model
{
    protected $table = 'sales_brands';

    protected $fillable = [
        'name', 'origin', 'channel', 'about', 'known_for', 'known_lines', 'best_for', 'pitch', 'objections', 'watch_out', 'competitors',
        'sort', 'is_active', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'known_for' => 'array',
            'known_lines' => 'array',
            'best_for' => 'array',
            'pitch' => 'array',
            'objections' => 'array',
            'watch_out' => 'array',
            'competitors' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(fn (self $brand) => $brand->uuid ??= (string) Str::uuid());
    }

    /** What the phones get (the same shape the app's cleaner expects). */
    public function toWire(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'origin' => $this->origin ?? '',
            'channel' => $this->channel ?? '',
            'about' => $this->about ?? '',
            'known_for' => $this->known_for ?? [],
            'known_lines' => $this->known_lines ?? [],
            'best_for' => $this->best_for ?? [],
            'pitch' => $this->pitch ?? [],
            'objections' => $this->objections ?? [],
            'watch_out' => $this->watch_out ?? [],
            'competitors' => $this->competitors ?? [],
            'sort' => (int) $this->sort,
        ];
    }
}
