<?php

namespace App\Sales\Filament\Resources\SalesProducts\Pages;

use App\Sales\Filament\Resources\SalesProducts\SalesProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesProducts extends ListRecords
{
    protected static string $resource = SalesProductResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
