<?php

namespace App\Sales\Filament\Resources\SalesTasks\Pages;

use App\Sales\Filament\Resources\SalesTasks\SalesTaskResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesTasks extends ListRecords
{
    protected static string $resource = SalesTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New task')];
    }
}
