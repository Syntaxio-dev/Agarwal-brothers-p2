<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('catalogue_file')
                    ->label('Catalogue PDF')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('catalogue')
                    ->acceptedFileTypes(['application/pdf']),
            ]);
    }
}