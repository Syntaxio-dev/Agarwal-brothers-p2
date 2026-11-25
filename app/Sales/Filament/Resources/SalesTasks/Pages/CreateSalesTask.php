<?php

namespace App\Sales\Filament\Resources\SalesTasks\Pages;

use App\Sales\Filament\Resources\SalesTasks\SalesTaskResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesTask extends CreateRecord
{
    protected static string $resource = SalesTaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['created_by' => auth()->id(), 'status' => 'new'];
    }

    protected function afterCreate(): void
    {
        SalesTaskResource::attachUploads($this->getRecord(), $this->data);
    }
}
