<?php

namespace App\Filament\Resources\JobApplications\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JobApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Candidate')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->disabled(),
                        TextInput::make('email')->disabled(),
                        TextInput::make('phone')->disabled(),
                        TextInput::make('position')->label('Applied for')->disabled(),
                        TextInput::make('preferred_location')->disabled(),
                        TextInput::make('department')->disabled(),
                        Textarea::make('message')->rows(3)->disabled()->columnSpanFull(),
                    ]),

                Section::make('Role-specific answers')
                    ->columnSpanFull()
                    ->visible(fn ($record) => filled($record?->answers))
                    ->schema([
                        Repeater::make('answers')
                            ->label('')
                            ->disabled()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->schema([
                                TextInput::make('label')->label('Question'),
                                TextInput::make('answer')->label('Answer'),
                            ])
                            ->columns(2),
                    ]),

                Section::make('Hiring progress')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'shortlisted' => 'Shortlisted',
                                'interviewed' => 'Interviewed',
                                'hired' => 'Hired',
                                'rejected' => 'Rejected',
                            ])
                            ->required(),
                        Textarea::make('notes')
                            ->label('Internal notes')
                            ->rows(3)
                            ->helperText('Only visible here in the admin panel.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
