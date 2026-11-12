<?php

namespace App\Sales\Rep\Resources\MyEnquiries\Pages;

use App\Sales\Models\SalesEnquiry;
use App\Sales\Rep\Resources\MyEnquiries\MyEnquiryResource;
use App\Sales\Support\SalesInsights;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListMyEnquiries extends ListRecords
{
    protected static string $resource = MyEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New enquiry')->icon(Heroicon::OutlinedPlus)];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'needs';
    }

    /** The follow-ups first: what needs action today, then all the open ones, the closed ones and everything. */
    public function getTabs(): array
    {
        $needs = SalesInsights::repDesk((int) auth()->id());

        return [
            'needs' => Tab::make('Needs action')->badge($needs['overdue'] + $needs['today'] + $needs['idle'] ?: null)->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', SalesEnquiry::OPEN)
                    ->whereRaw('(next_followup_at < ? OR (next_followup_at IS NULL AND client_updated_at < ?))', [
                        now()->setTimezone(config('sales.timezone'))->endOfDay()->utc()->format('Y-m-d H:i:s'),
                        now()->subDays(SalesInsights::ENQUIRY_IDLE_DAYS)->utc()->format('Y-m-d H:i:s.v'),
                    ])),
            'open' => Tab::make('All open')->badge($needs['open'] ?: null)->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', SalesEnquiry::OPEN)),
            'closed' => Tab::make('Closed')->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', SalesEnquiry::CLOSED)),
            'all' => Tab::make('All'),
        ];
    }
}
