<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use LogsActivity;
    protected $fillable = [
        'catalogues', 'whatsapp_number', 'blogs_hero', 'news_hero', 'webinars_hero',
        'story_banner', 'story_image_1', 'story_image_2', 'story_mission_image',
        'story_intro', 'story_points', 'story_mission', 'story_vision', 'story_goal', 'founded_year', 'leadership_bg',
        'seo_title', 'seo_description', 'default_og_image',
        'clients_stat_number', 'clients_stat_label',
    ];

    protected $casts = [
        'story_points' => 'array',
        'catalogues' => 'array',
        'founded_year' => 'integer',
    ];

    /**
     * The catalogue PDFs to offer visitors: [['title' => 'Equipment catalogue', 'url' => '.../storage/catalogue/x.pdf'], ...].
     * Rows without a file are left out; a row without a title is called "Catalogue".
     */
    public function catalogueList(): array
    {
        return collect($this->catalogues ?? [])
            ->filter(fn ($c) => is_array($c) && filled($c['file'] ?? null))
            ->map(fn ($c) => ['title' => trim((string) ($c['title'] ?? '')) ?: 'Catalogue', 'url' => asset('storage/' . $c['file'])])
            ->values()
            ->all();
    }

    /** The site reads the most recent settings row. */
    public static function current(): ?self
    {
        return static::latest()->first();
    }
}
