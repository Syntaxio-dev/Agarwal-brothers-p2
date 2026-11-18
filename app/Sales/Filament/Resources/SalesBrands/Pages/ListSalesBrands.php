<?php

namespace App\Sales\Filament\Resources\SalesBrands\Pages;

use App\Sales\Filament\Resources\SalesBrands\SalesBrandResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesBrands extends ListRecords
{
    protected static string $resource = SalesBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
