<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Creates a draft copy of a product so a similar model can be added quickly.
 *
 * Copied: model group, short description, overview, specifications, features, advantages and FAQs.
 * Not copied (they belong to the original product): page heading, documents, video, SEO fields, top-pick status,
 * and the pictures unless asked for. The copy starts inactive so nothing half-finished goes live.
 */
class ProductDuplicator
{
    public static function copy(Product $source, string $name, int $categoryId, bool $copyImages = false): Product
    {
        $copy = new Product([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => static::uniqueSlug($name),
            'model_group' => $source->model_group,
            'short_description' => $source->short_description,
            'overview' => $source->overview,
            'specs' => $source->specs,
            'features' => $source->features,
            'advantages' => $source->advantages,
            'faqs' => $source->faqs,
            'is_active' => false,
            'is_top_pick' => false,
        ]);

        if ($copyImages) {
            $copy->image = static::copyFile($source->image);
            $copy->gallery = collect($source->gallery ?? [])->map(fn ($f) => static::copyFile($f))->filter()->values()->all();
        }

        $copy->save();

        return $copy;
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $n = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    /** Own copy of an uploaded file, so deleting one product's picture never breaks the other. */
    private static function copyFile(?string $path): ?string
    {
        $disk = Storage::disk('public');

        if (blank($path) || ! $disk->exists($path)) {
            return null;
        }

        $new = trim(dirname($path), './') . '/' . Str::ulid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
        $disk->copy($path, $new);

        return $new;
    }
}
