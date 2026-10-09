<?php

namespace App\Filament\Resources\ApplicationResources\Pages;

use App\Filament\Resources\ApplicationResources\ApplicationResourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditApplicationResource extends EditRecord
{
    protected static string $resource = ApplicationResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
