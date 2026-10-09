<?php

namespace App\Filament\Resources\ApplicationResources\Pages;

use App\Filament\Resources\ApplicationResources\ApplicationResourceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApplicationResource extends CreateRecord
{
    protected static string $resource = ApplicationResourceResource::class;
}
