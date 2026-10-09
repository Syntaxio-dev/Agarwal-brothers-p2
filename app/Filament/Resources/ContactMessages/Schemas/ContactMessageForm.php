<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sender')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('phone')->disabled(),
                        TextInput::make('company')->disabled(),
                        TextInput::make('city')->disabled(),
                        TextInput::make('subject')->label('Application / Product')->disabled(),
                        Textarea::make('message')->rows(5)->disabled()->columnSpanFull(),
                    ]),

                Section::make('Follow-up')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'closed' => 'Closed',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}
