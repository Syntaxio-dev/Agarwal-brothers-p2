<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->brand->name . ' - ' . $record->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('short_description')
                    ->columnSpanFull(),
                KeyValue::make('specs')
                    ->keyLabel('Specification')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('products'),
                Toggle::make('is_active')
                    ->default(true),
                Toggle::make('is_top_pick')
    ->label('Show in Top Picks')
    ->helperText('Display this product in the homepage Top Picks section.')
    ->default(false),

TextInput::make('top_pick_order')
    ->label('Top Pick Order')
    ->numeric()
    ->minValue(1)
    ->placeholder('1, 2, 3...')
    ->helperText('Lower number appears first.')
    ->visible(fn ($get) => $get('is_top_pick')),

            ]);
    }
}