<?php

namespace App\Filament\Resources\Insights\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
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

                Section::make('Article')
                    ->description('Build the article part by part, in the order you want it to appear: text, a picture, a photo slider (carousel), more text, and a list of sources at the end. The cover image above is shown at the top. Optional if you attach a PDF.')
                    ->columnSpanFull()
                    ->schema([
                        Builder::make('blocks')
                            ->label('')
                            ->addActionLabel('Add a part (text, picture, slider, sources)')
                            ->collapsible()
                            ->cloneable()
                            ->blocks([
                                Block::make('text')
                                    ->label('Text')
                                    ->icon('heroicon-o-bars-3-bottom-left')
                                    ->schema([
                                        RichEditor::make('content')
                                            ->fileAttachmentsDisk('public')->fileAttachmentsVisibility('public')->fileAttachmentsDirectory('editor')
                                            ->label('Text')
                                            ->required(),
                                    ]),
                                Block::make('image')
                                    ->label('Picture')
                                    ->icon('heroicon-o-photo')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->label('Picture')
                                            ->image()
                                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('insights/body')
                                            ->required(),
                                        TextInput::make('alt')
                                            ->label('Short description of the picture (for screen readers and Google)')
                                            ->maxLength(160),
                                        TextInput::make('caption')
                                            ->label('Caption (optional, shown under the picture)')
                                            ->maxLength(200),
                                        Select::make('size')
                                            ->label('Size')
                                            ->options(['wide' => 'Full width', 'medium' => 'Medium, centred', 'small' => 'Small, centred'])
                                            ->default('wide')
                                            ->native(false),
                                    ]),
                                Block::make('gallery')
                                    ->label('Photo slider / gallery')
                                    ->icon('heroicon-o-squares-2x2')
                                    ->schema([
                                        FileUpload::make('images')
                                            ->label('Pictures (2 to 12, drag to change the order)')
                                            ->image()
                                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                                            ->multiple()
                                            ->maxFiles(12)
                                            ->reorderable()
                                            ->appendFiles()
                                            ->panelLayout('grid')
                                            ->openable()
                                            ->disk('public')
                                            ->visibility('public')
                                            ->directory('insights/body')
                                            ->required(),
                                        Select::make('style')
                                            ->label('How to show them')
                                            ->options(['carousel' => 'Slider (one picture at a time, with arrows)', 'grid' => 'Grid (all pictures together)'])
                                            ->default('carousel')
                                            ->native(false),
                                        TextInput::make('caption')
                                            ->label('Caption (optional)')
                                            ->maxLength(200),
                                    ]),
                                Block::make('sources')
                                    ->label('Sources / references')
                                    ->icon('heroicon-o-link')
                                    ->schema([
                                        Repeater::make('items')
                                            ->label('Sources')
                                            ->addActionLabel('Add a source')
                                            ->schema([
                                                TextInput::make('label')->label('Name of the page or document')->required()->maxLength(200),
                                                TextInput::make('url')->label('Web address')->url()->required()->rules([new \App\Rules\SafeLink])->placeholder('https://'),
                                                TextInput::make('accessed')->label('Date read (optional)')->maxLength(40)->placeholder('2026-10-20'),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(1),
                                    ]),
                            ]),
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

                \App\Filament\Support\SeoSection::make(),
            ]);
    }
}
