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

    protected $fillable = ['name', 'slug', 'logo', 'brochure', 'country_id', 'description', 'is_active', 'sort_order', 'seo_title', 'seo_description', 'og_image',];

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

    /**
     * What the brand mostly is on our site, from its product line names ("Analytical Balance", "Precision Lab Balances"
     * -> "Balances"), so the title says "Aczet Balances Supplier", the words people search. Null when the lines differ.
     */
    public function productNoun(): ?string
    {
        $names = $this->categories()->pluck('name');
        if ($names->isEmpty()) {
            return null;
        }

        $last = $names->map(fn ($n) => \Illuminate\Support\Str::singular(\Illuminate\Support\Str::lower(\Illuminate\Support\Arr::last(preg_split('/\s+/u', trim($n))))));
        [$word, $count] = [$last->countBy()->sortDesc()->keys()->first(), $last->countBy()->max()];

        return $count / $names->count() >= 0.6 && mb_strlen((string) $word) >= 3 ? \Illuminate\Support\Str::plural(ucfirst($word)) : null;
    }

    protected function seoName(): string
    {
        // The words people type into Google: brand + what it makes + where, short enough for the whole title to stay near 60 characters.
        $what = $this->productNoun();
        $base = $this->name . ($what ? ' ' . $what : '');

        foreach ([' Supplier in Jaipur, India', ' Supplier, Jaipur'] as $where) {
            if (mb_strlen($base . $where) <= 43) {
                return $base . $where;
            }
        }

        return $this->name . (mb_strlen($this->name) <= 16 ? ' Supplier in Jaipur, India' : ' Supplier, Jaipur');
    }

    protected function seoFallbackDescription(): ?string
    {
        $what = $this->productNoun();
        $lead = $this->name . ' ' . ($what ? \Illuminate\Support\Str::lower($what) : 'laboratory instruments') . ' supplied and supported in India by Agarwal Brothers, Jaipur.';
        $first = \App\Support\Seo::sentences($this->description)[0] ?? null;

        // "Ranges include analytical, precision, micro and flame proof balances." (line names without the shared noun)
        $ranges = null;
        if ($what) {
            $noun = \Illuminate\Support\Str::singular(\Illuminate\Support\Str::lower($what));
            $kinds = $this->categories()->orderBy('sort_order')->orderBy('name')->pluck('name')
                ->map(fn ($n) => trim(preg_replace('/\b(lab|laboratory)\b/iu', '', preg_replace('/\s+' . preg_quote($noun, '/') . '(e?s)?\s*$/iu', '', $n))))
                ->filter()->map(fn ($n) => \Illuminate\Support\Str::lower(preg_replace('/\s+/u', ' ', $n)))->unique()->values();
            for ($take = min(5, $kinds->count()); $take >= 2 && $ranges === null; $take--) {
                $list = $kinds->take($take);
                $sentence = 'Ranges include ' . $list->slice(0, -1)->implode(', ') . ' and ' . $list->last() . ' ' . \Illuminate\Support\Str::lower($what) . '.';
                if (mb_strlen($lead . ' ' . $sentence) <= 160) {
                    $ranges = $sentence;
                }
            }
        }

        return \App\Support\Seo::compose([$lead, $first, $ranges], [
            'Browse its product lines and specifications, and ask us for a quote.',
            'Ask us for a quote.',
        ]);
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
