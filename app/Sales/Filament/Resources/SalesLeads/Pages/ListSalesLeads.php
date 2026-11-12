<?php

namespace App\Sales\Filament\Resources\SalesLeads\Pages;

use App\Sales\Filament\Resources\SalesLeads\SalesLeadResource;
use App\Sales\Support\SalesInsights;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSalesLeads extends ListRecords
{
    protected static string $resource = SalesLeadResource::class;

    /** The newest first, everything: the owner's default. */
    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    /** Today (what needs action now) / Last 7 days / Last month / All: the same four views as a rep has, over every rep. */
    public function getTabs(): array
    {
        $counts = SalesInsights::leadCounts();
        $tabs = [];

        foreach (SalesInsights::LEAD_RANGES as $range => $label) {
            $tab = Tab::make($label)->badge($counts[$range] ?: null)
                ->modifyQueryUsing(fn (Builder $query) => SalesInsights::leadRange($query, $range));
            $tabs[$range] = $range === 'today' ? $tab->badgeColor('danger') : $tab;
        }

        return $tabs;
    }
}
