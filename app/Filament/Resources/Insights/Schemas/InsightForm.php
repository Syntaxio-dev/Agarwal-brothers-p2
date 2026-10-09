<?php

namespace App\Filament\Resources\Insights\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class InsightForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basics')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('type')
                            ->options([
                                'blog' => 'Blog',
                                'news' => 'News & Events',
                                'webinar' => 'Webinar',
                            ])
                            ->required()
                            ->live(),
                        DatePicker::make('event_date')
                            ->label('Event / webinar date')
                            ->visible(fn ($get) => in_array($get('type'), ['news', 'webinar'])),
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('excerpt')
                            ->rows(2)
                            ->helperText('Short summary shown on cards.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Cover image')
                    ->description('Blogs use a wide banner (about 16:8); news posters work best as portrait (about 4:5).')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('insights'),
                    ]),

                Section::make('Content')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('content'),
                    ]),

                Section::make('Visibility')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Published')
                            ->default(true),
                        Toggle::make('is_featured')
                            ->label('Feature on homepage')
                            ->helperText('Blogs: up to 4, News & Events: up to 3. If none are featured, the latest are shown.')
                            ->default(false),
                    ]),
            ]);
    }
}
