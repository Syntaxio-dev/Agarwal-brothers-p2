<?php

namespace App\Filament\Resources\Brands\Pages;

use App\Filament\Resources\Brands\BrandResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBrand extends EditRecord
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \App\Filament\Support\ViewOnSite::for($this->getRecord()),
            DeleteAction::make()
                ->modalDescription('A brand can only be deleted after its product lines are deleted or moved to another brand.')
                ->failureNotificationTitle('Not deleted: this brand still has product lines. Delete or move them first.'),
        ];
    }
}
