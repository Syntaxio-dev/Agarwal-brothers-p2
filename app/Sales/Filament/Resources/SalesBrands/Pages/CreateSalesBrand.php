<?php

namespace App\Sales\Filament\Resources\SalesBrands\Pages;

use App\Sales\Filament\Resources\SalesBrands\SalesBrandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesBrand extends CreateRecord
{
    protected static string $resource = SalesBrandResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['updated_by' => auth()->id()];
    }
}
