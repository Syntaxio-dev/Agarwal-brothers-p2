<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Team member')
                    ->description('Shown on the Our Story page, either as a large leadership block or in the "Meet the team" grid.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required(),
                        TextInput::make('designation')->placeholder('e.g. Managing Director'),
                        FileUpload::make('photo')
                            ->helperText('A transparent PNG cut-out looks best in leadership blocks (about 4:5). Without a photo, a placeholder silhouette is shown.')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('team')
                            ->columnSpanFull(),
                        TextInput::make('linkedin_url')
                            ->label('LinkedIn profile (optional)')
                            ->url()
                            ->placeholder('https://www.linkedin.com/in/...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Leadership block')
                    ->description('Leaders appear as large blocks with a quote, alternating left and right. Everyone else goes in the team grid.')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('is_leader')
                            ->label('Show as leadership block')
                            ->live()
                            ->default(false),
                        Textarea::make('quote')
                            ->label('Quote / message')
                            ->rows(5)
                            ->maxLength(600)
                            ->visible(fn ($get) => (bool) $get('is_leader')),
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
