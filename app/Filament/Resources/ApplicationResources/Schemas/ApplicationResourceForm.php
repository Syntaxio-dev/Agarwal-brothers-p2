<?php

namespace App\Filament\Resources\ApplicationResources\Schemas;

use App\Models\ApplicationResource;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Resource')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('category')
                            ->options(ApplicationResource::CATEGORIES)
                            ->default('appnote')
                            ->required(),
                        TextInput::make('source')
                            ->label('Brand · Product line')
                            ->placeholder('e.g. BUCHI · Rotary Evaporators'),
                        Textarea::make('description')
                            ->rows(3)
                            ->maxLength(300)
                            ->columnSpanFull(),
                    ]),

                Section::make('Files')
                    ->description('Upload a PDF to offer a download. For videos, paste a link instead. If neither is added, visitors get a "Request" button that opens the contact page.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('cover_image')
                            ->label('Cover image')
                            ->helperText('Shown on the card. Landscape (about 16:10) works best.')
                            ->image()
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('resources/covers'),
                        FileUpload::make('pdf')
                            ->label('PDF')
                            ->helperText('Max 20 MB.')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('resources/pdfs')
                            ->downloadable(),
                        TextInput::make('link_url')
                            ->label('External link (video / page)')
                            ->url()
                            ->placeholder('https://')
                            ->columnSpanFull(),
                    ]),

                Section::make('Visibility')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower number appears first. You can also drag rows in the list.'),
                    ]),
            ]);
    }
}
