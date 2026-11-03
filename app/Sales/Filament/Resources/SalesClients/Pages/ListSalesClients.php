<?php

namespace App\Sales\Filament\Resources\SalesClients\Pages;

use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use App\Sales\Filament\Resources\SalesContacts\SalesContactResource;
use App\Sales\Models\SalesContact;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListSalesClients extends ListRecords
{
    protected static string $resource = SalesClientResource::class;

    /** The same two buttons as in the app: add a client, and add a contact person (to a client, or without a company). */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add client')->icon(Heroicon::OutlinedPlus),
            Action::make('addContactPerson')
                ->label('Add contact person')
                ->icon(Heroicon::OutlinedUserPlus)
                ->color('gray')
                ->modalHeading('Add a contact person')
                ->modalDescription('Pick the company they work at, or leave it empty for a customer who buys on their own.')
                ->modalSubmitActionLabel('Save person')
                ->schema(SalesContactResource::personFields())
                ->action(function (array $data) {
                    abort_unless(auth()->user()?->canManage('sales-clients'), 403);

                    $person = SalesContact::create($data + ['user_id' => auth()->id()]);
                    Notification::make()->title($person->name . ' was added')->success()->send();
                }),
        ];
    }
}
