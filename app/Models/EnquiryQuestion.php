<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/** A quick question (the visitor must answer it) on the product enquiry form that helps the team understand the requirement. */
class EnquiryQuestion extends Model
{
    public const TYPES = ['choice' => 'Pick one answer', 'text' => 'Short typed answer'];

    /** The form never shows more than this many, so a visitor is not put off. */
    public const MAX_SHOWN = 4;

    protected $fillable = ['vertical_id', 'question', 'type', 'options', 'sort_order', 'is_active'];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function vertical()
    {
        return $this->belongsTo(Vertical::class);
    }

    /** @return list<string> */
    public function optionList(): array
    {
        return array_values(array_filter(array_map('trim', (array) $this->options), 'strlen'));
    }

    /**
     * The questions to ask for this product: those of the verticals its product line sits in first,
     * then the general ones (no vertical), at most MAX_SHOWN.
     *
     * @return Collection<int, static>
     */
    public static function forProduct(Product $product): Collection
    {
        $verticalIds = $product->category?->verticals()->pluck('verticals.id')->all() ?? [];

        return static::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('vertical_id')->orWhereIn('vertical_id', $verticalIds))
            ->orderByRaw('vertical_id is null')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (self $q) => $q->type === 'text' || count($q->optionList()) >= 2)
            ->take(static::MAX_SHOWN)
            ->values();
    }
}
