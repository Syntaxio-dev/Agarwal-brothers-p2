<?php

namespace App\Sales\Filament\Resources\SalesClients\Pages;

use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesClient extends CreateRecord
{
    protected static string $resource = SalesClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['user_id' => auth()->id()];
    }

    protected function getRedirectUrl(): string
    {
        // straight to the people list of the new client
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
