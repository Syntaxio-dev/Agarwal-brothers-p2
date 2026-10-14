<?php

namespace App\Filament\Resources\ClientCategories\Pages;

use App\Filament\Resources\ClientCategories\ClientCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientCategory extends EditRecord
{
    protected static string $resource = ClientCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->failureNotificationTitle('Not deleted: this category still has clients. Move them to another category first.'),
        ];
    }
}
