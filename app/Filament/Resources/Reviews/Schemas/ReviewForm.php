<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reviewer')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('designation')->placeholder('e.g. QC Manager'),
                        TextInput::make('organization')->placeholder('e.g. Sun Pharmaceuticals'),
                    ]),

                Section::make('Review')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Textarea::make('content')
                            ->label('Review text')
                            ->required()
                            ->rows(4)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('rating')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5)
                            ->default(5)
                            ->required(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower number appears first.'),
                        Toggle::make('is_active')
                            ->label('Active (show on homepage)')
                            ->default(true),
                    ]),
            ]);
    }
}
