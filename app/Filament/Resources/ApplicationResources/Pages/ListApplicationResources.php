<?php

namespace App\Filament\Resources\ApplicationResources\Pages;

use App\Filament\Resources\ApplicationResources\ApplicationResourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApplicationResources extends ListRecords
{
    protected static string $resource = ApplicationResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
