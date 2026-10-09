<?php

namespace App\Filament\Resources\JobOpenings\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class JobOpeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the role page URL.'),
                        TextInput::make('location')
                            ->required()
                            ->placeholder('e.g. Hyderabad'),
                        Select::make('employment_type')
                            ->options([
                                'Full Time' => 'Full Time',
                                'Part Time' => 'Part Time',
                                'Contract' => 'Contract',
                                'Internship' => 'Internship',
                            ])
                            ->default('Full Time')
                            ->required(),
                        TextInput::make('department')
                            ->placeholder('e.g. Sales, Service, Marketing'),
                        Textarea::make('summary')
                            ->rows(2)
                            ->maxLength(250)
                            ->helperText('One or two lines shown in the careers list.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Role description')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('description'),
                    ]),

                Section::make('Application questions')
                    ->description('Extra questions candidates answer when applying for this role. Name, email, phone, resume and a message are always asked.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('questions')
                            ->label('')
                            ->addActionLabel('Add question')
                            ->collapsible()
                            ->reorderable()
                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                TextInput::make('label')
                                    ->label('Question')
                                    ->required()
                                    ->columnSpanFull(),
                                Select::make('type')
                                    ->label('Answer type')
                                    ->options([
                                        'text' => 'Short text',
                                        'textarea' => 'Long text',
                                        'select' => 'Dropdown (choose one)',
                                        'yes_no' => 'Yes / No',
                                    ])
                                    ->default('text')
                                    ->required()
                                    ->live(),
                                Toggle::make('required')
                                    ->label('Required')
                                    ->default(false)
                                    ->inline(false),
                                TextInput::make('options')
                                    ->label('Dropdown options')
                                    ->placeholder('Option 1, Option 2, Option 3')
                                    ->helperText('Separate options with commas.')
                                    ->visible(fn ($get) => $get('type') === 'select')
                                    ->required(fn ($get) => $get('type') === 'select')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Hiring status')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Open for hiring')
                            ->helperText('Turn off to hide this role from the careers page without deleting it.')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower number appears first. You can also drag rows in the list.'),
                    ]),
            ]);
    }
}
