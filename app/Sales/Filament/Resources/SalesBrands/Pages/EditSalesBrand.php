<?php

namespace App\Sales\Filament\Resources\SalesBrands\Pages;

use App\Sales\Filament\Resources\SalesBrands\SalesBrandResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesBrand extends EditRecord
{
    protected static string $resource = SalesBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
