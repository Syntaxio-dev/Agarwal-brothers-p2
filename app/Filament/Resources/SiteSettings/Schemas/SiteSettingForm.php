<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Catalogue & contact')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
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
                    ]),

                Section::make('Page banners')
                    ->description('Large banner shown at the top of each insights page. Wide images (about 3:1, e.g. 1800x600) work best. If empty, a default banner is shown.')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        FileUpload::make('blogs_hero')
                            ->label('Blogs banner')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('banners'),
                        FileUpload::make('news_hero')
                            ->label('News & Events banner')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('banners'),
                        FileUpload::make('webinars_hero')
                            ->label('Webinars banner')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('banners'),
                    ]),
            ]);
    }
}
