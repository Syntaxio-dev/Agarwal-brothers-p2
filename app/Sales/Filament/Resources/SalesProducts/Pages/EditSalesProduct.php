<?php

namespace App\Sales\Filament\Resources\SalesProducts\Pages;

use App\Sales\Filament\Resources\SalesProducts\SalesProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesProduct extends EditRecord
{
    protected static string $resource = SalesProductResource::class;

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
