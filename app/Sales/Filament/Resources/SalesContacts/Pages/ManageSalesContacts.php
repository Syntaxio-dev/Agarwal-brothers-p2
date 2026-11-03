<?php

namespace App\Sales\Filament\Resources\SalesContacts\Pages;

use App\Sales\Filament\Resources\SalesContacts\SalesContactResource;
use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Resources\Pages\ManageRecords;

class ManageSalesContacts extends ManageRecords
{
    protected static string $resource = SalesContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add a person')->mutateDataUsing(fn (array $data) => $data + ['user_id' => auth()->id()]),
            Action::make('addClient')
                ->label('Add a client with location')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('gray')
                ->modalHeading('Add a client')
                ->modalDescription('Name, place and the exact spot are required. Add the people who work there with "Add a person".')
                ->modalSubmitActionLabel('Save client')
                ->schema(SalesClientResource::quickClientFields())
                ->action(function (array $data) {
                    $client = SalesClientResource::createQuickClient($data);
                    Notification::make()->title($client->name . ' was added')->success()->send();
                }),
        ];
    }
}
