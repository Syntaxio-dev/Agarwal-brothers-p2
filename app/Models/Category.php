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

    protected $fillable = ['brand_id', 'name', 'slug', 'heading', 'description', 'short_description', 'content', 'faqs', 'image', 'sort_order', 'seo_title', 'seo_description', 'og_image',];

    protected $casts = [
        'faqs' => 'array',
    ];

    protected static function booted(): void
    {
        // products are ON DELETE CASCADE; never wipe them implicitly.
        static::deleting(fn (Category $category) => $category->products()->exists() ? false : null);
    }

    /** Text on the vertical page's hover card: the short summary, else the start of the intro paragraph. */
    public function cardText(): ?string
    {
        $text = trim((string) $this->short_description);
        if ($text === '') {
            $text = \Illuminate\Support\Str::limit(trim(strip_tags((string) $this->description)), 110);
        }

        return $text !== '' ? $text : null;
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

    /** "Aczet Analytical Balance": brand first, the way people search for a range. */
    public function seoHeading(): string
    {
        $brand = $this->brand?->name;

        return $brand && ! \Illuminate\Support\Str::contains($this->name, $brand, true) ? $brand . ' ' . $this->name : $this->name;
    }

    protected function seoName(): string
    {
        $title = $this->seoHeading();
        $long = $title . ': Models & Specs';

        // keep " | Agarwal Brothers" on the title (62 characters in all)
        return mb_strlen($long) + mb_strlen(' | ' . \App\Support\Seo::SITE) <= 62 ? $long : $title;
    }

    /** "Aczet Analytical Balance: 7 models including CY 124C, CY 224 and CY 224C. <first sentence>. Quote from ..." */
    protected function seoFallbackDescription(): ?string
    {
        $names = $this->products()->where('is_active', true)->orderBy('id')->pluck('name');
        // short model names read best in a list ("CY 224C", not "CY-C Professional Analytical")
        $short = $names->sortBy(fn ($n) => [mb_strlen($n) > 18 ? 1 : 0])->values();

        $lead = null;
        for ($take = min(4, $short->count()); $take >= 1 && $lead === null; $take--) {
            $list = $short->take($take)->values();
            $listText = $list->count() > 1 ? $list->slice(0, -1)->implode(', ') . ' and ' . $list->last() : $list->first();
            $sentence = $this->seoHeading() . ': ' . $names->count() . ' ' . \Illuminate\Support\Str::plural('model', $names->count())
                . ($names->count() > $take ? ' including ' : ', ') . $listText . '.';
            if (mb_strlen($sentence) <= 115) {
                $lead = $sentence;
            }
        }

        return \App\Support\Seo::compose([
            $lead ?? $this->seoHeading() . ' from ' . ($this->brand?->name ?? 'leading brands') . '.',
            \App\Support\Seo::sentences($this->description ?: $this->short_description)[0] ?? null,
        ], ['Specs, brochures and quotes from Agarwal Brothers, Jaipur.', 'Quotes from Agarwal Brothers, Jaipur.']);
    }

    protected function seoFallbackImage(): ?string
    {
        return $this->image ?: $this->brand?->logo;
    }
}
