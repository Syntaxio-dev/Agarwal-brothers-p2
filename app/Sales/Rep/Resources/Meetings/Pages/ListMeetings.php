<?php

namespace App\Sales\Rep\Resources\Meetings\Pages;

use App\Sales\Models\SalesMeeting;
use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Meetings\MeetingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMeetings extends ListRecords
{
    use ChecksDeskArea;

    protected static string $resource = MeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [MeetingResource::planVisitAction(), MeetingResource::planRouteAction()];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'upcoming';
    }

    /** Coming (from the start of today), not written up yet (planned time gone), done, everything. */
    public function getTabs(): array
    {
        $today = now()->setTimezone(config('sales.timezone'))->startOfDay()->utc();
        $open = fn (Builder $query) => $query->whereIn('status', SalesMeeting::OPEN);

        return [
            'upcoming' => Tab::make('Coming up')->modifyQueryUsing(fn (Builder $query) => $open($query)->where('meeting_at', '>=', $today)),
            'late' => Tab::make('Not written up')
                ->badge(fn () => SalesMeeting::where('user_id', auth()->id())->whereIn('status', SalesMeeting::OPEN)->where('meeting_at', '<', now()->subHour())->count() ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $open($query)->where('meeting_at', '<', now()->subHour())->reorder('meeting_at', 'desc')),
            'done' => Tab::make('Done')->modifyQueryUsing(fn (Builder $query) => $query->whereRaw("COALESCE(status, 'done') = 'done'")->reorder('meeting_at', 'desc')),
            'all' => Tab::make('All')->modifyQueryUsing(fn (Builder $query) => $query->reorder('meeting_at', 'desc')),
        ];
    }
}
