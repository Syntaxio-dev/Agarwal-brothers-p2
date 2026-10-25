<?php

namespace App\Sales\Filament\Resources\SalesProducts\Pages;

use App\Sales\Filament\Resources\SalesProducts\SalesProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesProduct extends CreateRecord
{
    protected static string $resource = SalesProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['updated_by' => auth()->id()];
    }
}
