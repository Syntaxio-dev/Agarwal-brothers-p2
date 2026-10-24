<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesReps extends ListRecords
{
    protected static string $resource = SalesRepResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Add rep')];
    }
}
