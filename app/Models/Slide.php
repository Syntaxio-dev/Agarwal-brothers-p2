<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use LogsActivity;
    use HasFactory;

protected $fillable = ['title', 'subtitle', 'image', 'alt_text', 'video_url', 'video', 'sort_order', 'is_active', 'link_url', 'product_id'];

    /** Slides with a picture but no description for screen readers. */
    public function scopeWithoutAlt($query)
    {
        return $query->whereNotNull('image')->where('image', '!=', '')
            ->where(fn ($q) => $q->whereNull('alt_text')->orWhere('alt_text', ''));
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** The featured product only if it (and its brand) are live; otherwise null. */
    public function resolvedProduct(): ?Product
    {
        $p = $this->product;

        return ($p && $p->is_active && $p->category?->brand?->is_active) ? $p : null;
    }

    /** Where the slide should link: the featured product's page, else the button link, else nothing. */
    public function linkTarget(): ?string
    {
        if ($p = $this->resolvedProduct()) {
            return route('product.show', $p->slug);
        }

        return \App\Rules\SafeLink::passes($this->link_url) ? $this->link_url : null;
    }

    /** Label for the slide button. */
    public function linkLabel(): string
    {
        return $this->resolvedProduct() ? 'View product' : 'Explore';
    }
}
