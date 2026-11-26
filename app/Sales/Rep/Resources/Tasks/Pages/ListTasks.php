<?php

namespace App\Sales\Rep\Resources\Tasks\Pages;

use App\Sales\Models\SalesTask;
use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    use ChecksDeskArea;

    protected static string $resource = TaskResource::class;

    public function getDefaultActiveTab(): string|int|null
    {
        return 'open';
    }

    public function getTabs(): array
    {
        return [
            'open' => Tab::make('Open')->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', SalesTask::OPEN)),
            'done' => Tab::make('Done')->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'done')),
            'all' => Tab::make('All'),
        ];
    }
}
