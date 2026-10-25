<?php

namespace App\Sales\Filament\Resources\SalesContacts\Pages;

use App\Sales\Filament\Resources\SalesContacts\SalesContactResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSalesContacts extends ManageRecords
{
    protected static string $resource = SalesContactResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Add a person')->mutateDataUsing(fn (array $data) => $data + ['user_id' => auth()->id()])];
    }
}
