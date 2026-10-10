<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        FileUpload::make('logo')
                            ->image()
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('clients'),
                        Textarea::make('testimonial')
                            ->label('Short note (shown on homepage)')
                            ->helperText('A line or two about the client, e.g. "Trusted partner for HPLC installation and AMC since 2012."')
                            ->maxLength(180)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Homepage carousel')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_featured')
                            ->label('Top client')
                            ->helperText('Shown one at a time in the homepage carousel (max 10). If none are marked, the first 10 active clients appear.')
                            ->default(false),
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
