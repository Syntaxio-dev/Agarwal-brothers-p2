<?php

namespace App\Filament\Resources\ClientCategories\Pages;

use App\Filament\Resources\ClientCategories\ClientCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientCategories extends ListRecords
{
    protected static string $resource = ClientCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
