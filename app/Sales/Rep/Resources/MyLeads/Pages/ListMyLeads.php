<?php

namespace App\Sales\Rep\Resources\MyLeads\Pages;

use App\Sales\Rep\Resources\MyLeads\MyLeadResource;
use App\Sales\Support\SalesInsights;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMyLeads extends ListRecords
{
    protected static string $resource = MyLeadResource::class;

    /** Everything, the newest activity first (the owner's default). */
    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    /** Today (what needs action now) / Last 7 days / Last month / All: only this rep's leads. */
    public function getTabs(): array
    {
        $counts = SalesInsights::leadCounts(['rep' => (int) auth()->id()]);
        $tabs = [];

        foreach (SalesInsights::LEAD_RANGES as $range => $label) {
            $tab = Tab::make($label)->badge($counts[$range] ?: null)
                ->modifyQueryUsing(fn (Builder $query) => SalesInsights::leadRange($query, $range));
            $tabs[$range] = $range === 'today' ? $tab->badgeColor('danger') : $tab;
        }

        return $tabs;
    }
}
