<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product details')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Brand · Category')
                            ->relationship('category', 'name', modifyQueryUsing: fn ($query) => $query->with('brand'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->brand->name . ' - ' . $record->name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('short_description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Image & specifications')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('products'),
                        KeyValue::make('specs')
                            ->keyLabel('Specification')
                            ->valueLabel('Value'),
                    ]),

                Section::make('Visibility')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Toggle::make('is_top_pick')
                            ->label('Show in Precision Picks')
                            ->helperText('Homepage shows the first 6 top picks.')
                            ->live()
                            ->default(false),
                        TextInput::make('top_pick_order')
                            ->label('Top pick order')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('1, 2, 3...')
                            ->helperText('Lower number appears first.')
                            ->visible(fn ($get) => $get('is_top_pick')),
                    ]),
            ]);
    }
}
