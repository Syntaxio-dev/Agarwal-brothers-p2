<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
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

                Section::make('Our Story page: photos')
                    ->description('All optional. Without a photo the page shows a designed placeholder.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('story_banner')
                            ->label('Top banner')
                            ->helperText('Wide image, about 1800x500 (your office / building photo). If empty, the default office photo is shown.')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('story'),
                        FileUpload::make('story_mission_image')
                            ->label('Mission / Vision image')
                            ->helperText('About 4:3.')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('story'),
                        FileUpload::make('leadership_bg')
                            ->label('Leadership blocks background')
                            ->helperText('Office / building photo shown (softly blurred) behind the leader blocks. Optional.')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('story'),
                        FileUpload::make('story_image_1')
                            ->label('About photo 1 (large)')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('story'),
                        FileUpload::make('story_image_2')
                            ->label('About photo 2 (small, overlapping)')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('story'),
                    ]),

                Section::make('Our Story page: text')
                    ->description('Leave a field empty to use the default text.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('founded_year')
                            ->label('Founded in')
                            ->numeric()
                            ->minValue(1900)
                            ->maxValue((int) date('Y'))
                            ->default(1981)
                            ->helperText('Years of experience are calculated from this automatically.'),
                        Textarea::make('story_intro')
                            ->label('About intro')
                            ->rows(4)
                            ->columnSpanFull(),
                        Repeater::make('story_points')
                            ->label('"What sets us apart" checklist')
                            ->schema([
                                TextInput::make('text')->label('Point')->required()->maxLength(90),
                            ])
                            ->addActionLabel('Add point')
                            ->defaultItems(0)
                            ->reorderable()
                            ->columnSpanFull(),
                        Textarea::make('story_mission')->label('Mission')->rows(3),
                        Textarea::make('story_vision')->label('Vision')->rows(3),
                        Textarea::make('story_goal')->label('Goal')->rows(3)->columnSpanFull(),
                    ]),

                Section::make('Home page SEO & sharing')
                    ->description('Default title and description for the home page, and the share image used when a page has none of its own.')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('seo_title')
                            ->label('Home page title')
                            ->maxLength(70)
                            ->placeholder('Laboratory Equipment Supplier in India')
                            ->helperText('" | Agarwal Brothers" is added automatically.'),
                        Textarea::make('seo_description')
                            ->label('Home page description')
                            ->rows(2)
                            ->maxLength(160)
                            ->helperText('Up to 160 characters. Also used as the default description.'),
                        FileUpload::make('default_og_image')
                            ->label('Default share image')
                            ->helperText('Used when a page has no image of its own. 1200x630 works best.')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('seo'),
                    ]),
            ]);
    }
}
