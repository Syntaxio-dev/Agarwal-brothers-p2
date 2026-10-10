<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Edit page only: how the card looks on the site, plus a checklist
                Section::make('Card preview')
                    ->description('Saved version of this product card, as visitors see it on the category page.')
                    ->columnSpanFull()
                    ->collapsible()
                    ->visible(fn ($record) => filled($record))
                    ->schema([
                        View::make('filament.products.card-preview'),
                    ]),

                Section::make('Product details')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Brand · Category')
                            ->relationship('category', 'name', modifyQueryUsing: fn ($query) => $query->with('brand'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->brand->name . ' - ' . $record->name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('heading')
                            ->label('Page heading (optional)')
                            ->placeholder('e.g. Hei-VAP Core Rotary Evaporator, Distributor & Service Provider in India')
                            ->helperText('Shown in the large banner on the product page. Defaults to the product name.')
                            ->columnSpanFull(),
                        TextInput::make('model_group')
                            ->label('Model group')
                            ->placeholder('e.g. Standard Models, Control Models')
                            ->helperText('Products with the same group are listed together on the category page.')
                            ->columnSpanFull(),
                        Textarea::make('short_description')
                            ->label('Short description')
                            ->rows(3)
                            ->helperText('Shown on cards and in the product banner.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Images & video')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->label('Main image')
                            ->helperText('A clean product shot on a white or transparent background.')
                            ->image()
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('products'),
                        FileUpload::make('gallery')
                            ->label('Extra images (gallery)')
                            ->helperText('Opened by the "Show Image" button.')
                            ->image()
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('products/gallery'),
                        TextInput::make('video_url')
                            ->label('Video link (YouTube)')
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Overview')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        RichEditor::make('overview')
                            ->helperText('A longer introduction shown under "Overview". Falls back to the short description.'),
                    ]),

                Section::make('Key features')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('features')
                            ->label('')
                            ->addActionLabel('Add feature')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->defaultItems(0)
                            ->schema([
                                TextInput::make('title')->required(),
                                Textarea::make('text')->label('Description')->rows(2),
                            ]),
                    ]),

                Section::make('Key advantages')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('advantages')
                            ->label('')
                            ->addActionLabel('Add advantage')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->defaultItems(0)
                            ->schema([
                                TextInput::make('title')->required(),
                                Textarea::make('text')->label('Description')->rows(2),
                            ]),
                    ]),

                Section::make('Technical specifications')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        KeyValue::make('specs')
                            ->keyLabel('Specification')
                            ->valueLabel('Value')
                            ->addActionLabel('Add specification')
                            ->reorderable()
                            ->helperText('Add or remove rows freely: every product can have its own number of specifications. Use the same wording (for example "Capacity") on products you want to compare, so they line up on the compare page.'),
                    ]),

                Section::make('Documents & research papers')
                    ->description('Brochures, datasheets, application notes or research papers. Upload a PDF or paste a link.')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('documents')
                            ->label('')
                            ->addActionLabel('Add document')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                TextInput::make('title')->required()->columnSpanFull(),
                                Select::make('type')
                                    ->options([
                                        'Brochure' => 'Brochure',
                                        'Datasheet' => 'Datasheet',
                                        'Application note' => 'Application note',
                                        'Research paper' => 'Research paper',
                                        'Manual' => 'Manual',
                                    ])
                                    ->default('Brochure'),
                                FileUpload::make('file')
                                    ->label('PDF')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(20480)
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('products/documents'),
                                TextInput::make('url')
                                    ->label('Or external link')
                                    ->url()
                                    ->placeholder('https://')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('FAQs')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('faqs')
                            ->label('')
                            ->addActionLabel('Add question')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                            ->defaultItems(0)
                            ->schema([
                                TextInput::make('question')->required(),
                                Textarea::make('answer')->required()->rows(3),
                            ]),
                    ]),

                Section::make('Visibility')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Toggle::make('is_top_pick')
                            ->label('Show in Precision Picks')
                            ->helperText('Homepage shows the first 6 top picks.')
                            ->live()
                            ->default(false),
                        TextInput::make('top_pick_order')
                            ->label('Top pick order')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('1, 2, 3...')
                            ->helperText('Lower number appears first.')
                            ->visible(fn ($get) => $get('is_top_pick')),
                    ]),

                \App\Filament\Support\SeoSection::make(),
            ]);
    }
}
