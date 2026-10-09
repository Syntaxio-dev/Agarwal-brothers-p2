<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Brand details')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-filled from the name. Used in the brand page URL.'),
                        Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->unique('countries', 'name'),
                                TextInput::make('latitude')->required()->numeric()->minValue(-90)->maxValue(90),
                                TextInput::make('longitude')->required()->numeric()->minValue(-180)->maxValue(180),
                            ])
                            ->helperText('Brand origin. Its pin appears on the homepage map once a country is set.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Logo')
                    ->description('Shown in the homepage brand strip, the map and the brand page.')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('logo')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('brands'),
                    ]),

                \App\Filament\Support\SeoSection::make(),
            ]);
    }
}
