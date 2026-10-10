<?php

namespace App\Filament\Resources\Verticals\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VerticalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vertical')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->rows(3)
                            ->helperText('Shown on the homepage card (about two lines).')
                            ->columnSpanFull(),
                        FileUpload::make('image')
                            ->label('Icon / image')
                            ->helperText('Shown as a small square on the homepage card; a square image works best.')
                            ->image()
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('verticals'),
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
                            ->helperText('Lower number appears first. The homepage shows the first 8.'),
                    ]),

                \App\Filament\Support\SeoSection::make(),
            ]);
    }
}
