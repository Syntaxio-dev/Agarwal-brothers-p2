<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\LogsActivity;
use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;
use App\Support\Seo;
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

    /** Industries that use this product (the same list as the client categories). */
    public function industries()
    {
        return $this->belongsToMany(ClientCategory::class, 'client_category_product');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function enquiries()
    {
        return $this->hasMany(Enquiry::class);
    }

    /**
     * Brand + model + type, in the order buyers type it into Google: "Aczet CY 224C Analytical Balance 220 g × 0.1 mg",
     * "Sartorius Cubis II Analytical Balance". When the page heading does not contain the model ("CY-FC Series
     * Flameproof Balance" for CY 224FC) the model and the product type are used instead ("Aczet CY 224FC Flame Proof Balance").
     */
    public function seoHeading(): string
    {
        $brand = $this->category?->brand?->name;
        $title = filled($this->heading) ? trim($this->heading) : trim($this->name);
        $compact = fn ($s) => preg_replace('/[^\p{L}\p{N}]+/u', '', Str::lower((string) $s));

        // the model part of the name: up to its last word with a digit ("CY 224FC", "CTG-T4LF" of "CTG-T4LF Flameproof Floor Scale")
        $model = preg_replace('/^(.*\d\S*).*$/su', '$1', trim($this->name));

        if (preg_match('/\d/', $this->name) && ! str_contains($compact($title), $compact($model))) {
            $title = trim($this->name . ' ' . $this->typeWords());
        }

        if ($brand && ! Str::contains($title, $brand, true)) {
            $title = $brand . ' ' . $title;
        }

        return $title;
    }

    /** The product line name as a singular product type, minus the words the model name already has ("Lab Balance"). */
    private function typeWords(): string
    {
        $have = array_flip(preg_split('/[^\p{L}\p{N}]+/u', Str::lower($this->name), -1, PREG_SPLIT_NO_EMPTY));
        $words = array_values(array_filter(
            preg_split('/\s+/u', trim((string) $this->category?->name), -1, PREG_SPLIT_NO_EMPTY),
            function ($word) use ($have) {
                $parts = preg_split('/[^\p{L}\p{N}]+/u', Str::lower($word), -1, PREG_SPLIT_NO_EMPTY);

                return $parts && array_diff($parts, array_keys($have));
            }
        ));

        if ($words) {
            $words[count($words) - 1] = Str::singular($words[count($words) - 1]);
        }

        return implode(' ', $words);
    }

    protected function seoName(): string
    {
        $title = $this->seoHeading();

        return mb_strlen($title) <= 27 ? $title . ': Specs & Quote' : $title;
    }

    /**
     * Complete sentences within 160 characters: brand and model first (unless the text already says them),
     * then the product's own description, then a short line about where to get it.
     */
    protected function seoFallbackDescription(): ?string
    {
        $brand = $this->category?->brand?->name;
        $sentences = Seo::sentences($this->short_description ?: $this->overview);
        $first = Str::lower($sentences[0] ?? '');
        $says = str_contains($first, Str::lower($this->name)) && (! $brand || str_contains($first, Str::lower($brand)));
        $lead = $says ? '' : rtrim($this->seoHeading(), '.') . '.';

        // the description's sentences from the start, as many as fit (a later sentence alone, "It has ...", reads badly)
        $text = $lead;
        foreach ($sentences as $sentence) {
            $next = trim($text . ' ' . $sentence);
            if (mb_strlen($next) > 160) {
                break;
            }
            $text = $next;
        }

        // not even the first sentence fits: the lead and the start of the description, cut on a word
        if ($sentences && $text === $lead) {
            return Seo::description(trim($lead . ' ' . implode(' ', $sentences)));
        }

        return Seo::compose([$text ?: 'Supplied, installed and supported in India by Agarwal Brothers, Jaipur.'],
            ['Specs, brochure and quote from Agarwal Brothers, Jaipur.', 'Quote from Agarwal Brothers, Jaipur.']);
    }

    protected function seoFallbackImage(): ?string
    {
        return $this->image ?: ($this->gallery[0] ?? null) ?: $this->category?->brand?->logo;
    }
}
