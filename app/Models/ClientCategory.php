<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

/** A group clients belong to (Pharma, Dairy, Universities ...). Shown in the Our Clients section. */
class ClientCategory extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'slug', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        // the web address of the industry page (/industries/<slug>) is made from the name
        static::saving(function (ClientCategory $category) {
            if (blank($category->slug)) {
                $base = \Illuminate\Support\Str::slug($category->name) ?: 'industry';
                $slug = $base;
                $n = 2;
                while (static::where('slug', $slug)->where('id', '!=', (int) $category->id)->exists()) {
                    $slug = $base . '-' . $n++;
                }
                $category->slug = $slug;
            }
        });

        // a category that still has clients is never deleted by accident
        static::deleting(fn (ClientCategory $category) => $category->clients()->exists() ? false : null);
    }

    /** Products that are used in this industry (set in the product form). */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'client_category_product');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
