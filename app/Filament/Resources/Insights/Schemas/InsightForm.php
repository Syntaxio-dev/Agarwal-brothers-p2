<?php

namespace App\Filament\Resources\Insights\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
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
                            ->label('Event date')
                            ->visible(fn ($get) => $get('type') === 'news'),
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

                Section::make('Webinar details')
                    ->description('Shown on the Webinars page with a live countdown. All times are India time (IST).')
                    ->columnSpanFull()
                    ->columns(2)
                    ->visible(fn ($get) => $get('type') === 'webinar')
                    ->schema([
                        Select::make('brand_id')
                            ->label('Principal (brand)')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Its logo appears on the webinar and in the "Filter by principal" chips.'),
                        TextInput::make('venue')
                            ->label('Mode / venue')
                            ->placeholder('Online')
                            ->default('Online'),
                        DateTimePicker::make('starts_at')
                            ->label('Starts at (IST)')
                            ->seconds(false)
                            ->native(false)
                            ->required(fn ($get) => $get('type') === 'webinar'),
                        Toggle::make('time_tbd')
                            ->label('Time to be announced')
                            ->helperText('Only the date is shown; the countdown shows days left.')
                            ->inline(false),
                        TextInput::make('registration_url')
                            ->label('Registration link')
                            ->url()
                            ->placeholder('https://')
                            ->columnSpanFull(),
                        TextInput::make('recording_url')
                            ->label('Recording link (after the webinar)')
                            ->url()
                            ->placeholder('https://')
                            ->columnSpanFull(),
                    ]),

                Section::make('Cover image & PDF')
                    ->description('The cover image is shown on cards. A PDF is optional and appears on the detail page with a download button.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label('Cover image')
                            ->helperText('Blogs and webinars: wide (about 16:9). News posters: portrait (about 4:5).')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('insights'),
                        FileUpload::make('pdf')
                            ->label('PDF (optional)')
                            ->helperText('Brochure, invitation, white paper, etc. Max 20 MB.')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(20480)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('insights/pdfs')
                            ->downloadable(),
                    ]),

                Section::make('Content')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('content')
                            ->helperText('Optional if you attach a PDF.'),
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
