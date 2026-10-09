<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
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
                TextInput::make('whatsapp_number')
                    ->label('WhatsApp number')
                    ->tel()
                    ->placeholder('919876543210')
                    ->helperText('With country code, digits only (e.g. 919876543210). Used by the floating WhatsApp button.'),
            ]);
    }
}