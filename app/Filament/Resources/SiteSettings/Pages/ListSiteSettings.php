<?php

namespace App\Filament\Resources\SiteSettings\Pages;

use App\Filament\Resources\SiteSettings\SiteSettingResource;
use Filament\Resources\Pages\ListRecords;

/** There is only one set of site settings, so the list address forwards straight to it. */
class ListSiteSettings extends ListRecords
{
    protected static string $resource = SiteSettingResource::class;

    public function mount(): void
    {
        $this->redirect(SiteSettingResource::settingsUrl(), navigate: false);
    }
}
