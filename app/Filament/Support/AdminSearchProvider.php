<?php

namespace App\Filament\Support;

use App\Filament\Pages\HelpGuide as HelpPage;
use App\Filament\Resources\Brands\BrandResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Support\HelpGuide;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\Contracts\GlobalSearchProvider;

/**
 * The search box in the admin top bar: products, brands and enquiries (only what the signed-in role may open),
 * plus matching topics from the How-to Guide, so "how to add a brand" finds its answer.
 */
class AdminSearchProvider implements GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $query = trim($query);
        $builder = GlobalSearchResults::make();

        if (mb_strlen($query) < 2) {
            return $builder;
        }

        foreach ([ProductResource::class, BrandResource::class, EnquiryResource::class] as $resource) {
            if (! $resource::canGloballySearch()) {
                continue;
            }

            $results = $resource::getGlobalSearchResults($query);

            if ($results->count()) {
                $builder->category(ucfirst($resource::getPluralModelLabel()), $results);
            }
        }

        $help = HelpGuide::search($query, auth()->user());

        if ($help) {
            $builder->category('How-to guide', array_map(
                fn (array $topic) => new GlobalSearchResult(
                    title: $topic['title'],
                    url: HelpPage::getUrl() . '#' . $topic['id'],
                    details: array_filter(['Tip' => $topic['snippet']]),
                ),
                $help,
            ));
        }

        return $builder;
    }
}
