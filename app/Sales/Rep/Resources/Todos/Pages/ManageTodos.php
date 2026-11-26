<?php

namespace App\Sales\Rep\Resources\Todos\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Todos\TodoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageTodos extends ListRecords
{
    use ChecksDeskArea;

    protected static string $resource = TodoResource::class;

    protected function getHeaderActions(): array
    {
        return [TodoResource::addAction()];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'open';
    }

    public function getTabs(): array
    {
        return [
            'open' => Tab::make('To do')->modifyQueryUsing(fn (Builder $query) => $query->where('done', false)),
            'done' => Tab::make('Done')->modifyQueryUsing(fn (Builder $query) => $query->where('done', true)),
            'all' => Tab::make('All'),
        ];
    }
}
