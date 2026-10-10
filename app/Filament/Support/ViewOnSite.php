<?php

namespace App\Filament\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Insight;
use App\Models\JobOpening;
use App\Models\Product;
use App\Models\Vertical;
use App\Support\Preview;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

/**
 * "View on site" button for edit pages. Opens the public page in a new tab. Pages that are not live yet
 * (inactive, draft, hidden brand) open in preview mode, which only signed-in staff can see.
 */
class ViewOnSite
{
    public static function for(Model $record): Action
    {
        [$url, $live] = static::resolve($record);

        return Action::make('view_on_site')
            ->label($live ? 'View on site' : 'Preview on site')
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->color('gray')
            ->tooltip($live ? 'Opens the live page in a new tab' : 'Not live yet: opens a preview that only staff can see')
            ->url(Preview::url($url, $live), shouldOpenInNewTab: true);
    }

    /** @return array{0: string, 1: bool} public URL and whether visitors can already see it */
    public static function resolve(Model $record): array
    {
        return match (true) {
            $record instanceof Product => [
                route('product.show', $record->slug),
                (bool) ($record->is_active && $record->category?->brand?->is_active),
            ],
            $record instanceof Brand => [route('brand.show', $record->slug), (bool) $record->is_active],
            $record instanceof Category => [
                route('category.show', [$record->brand->slug, $record->slug]),
                (bool) $record->brand?->is_active,
            ],
            $record instanceof Vertical => [route('vertical.show', $record->slug), (bool) $record->is_active],
            $record instanceof Insight => [route('insights.show', $record->slug), (bool) $record->is_active],
            $record instanceof JobOpening => [route('careers.show', $record->slug), (bool) $record->is_active],
            // Everything else shows on a fixed page of the site.
            default => [static::fixedPage($record), true],
        };
    }

    private static function fixedPage(Model $record): string
    {
        return match (class_basename($record)) {
            'TeamMember', 'SiteSetting' => route('our-story'),
            'ApplicationResource' => route('application-resources'),
            default => route('home'),   // slides, clients, reviews, countries
        };
    }
}
