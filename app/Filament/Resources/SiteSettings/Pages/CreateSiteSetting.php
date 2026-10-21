<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteSetting extends CreateRecord
{
    protected static string $resource = SiteSettingResource::class;

    public function getTitle(): string
    {
        return 'Site settings';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
