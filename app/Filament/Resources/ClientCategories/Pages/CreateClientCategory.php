<?php

namespace App\Filament\Resources\ClientCategories\Pages;

use App\Filament\Resources\ClientCategories\ClientCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClientCategory extends CreateRecord
{
    protected static string $resource = ClientCategoryResource::class;
}
