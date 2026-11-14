<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->description('A product line of one brand (e.g. "Rotary Evaporators" under Heidolph). It appears as a link inside the brand card on each vertical page.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('verticals')
                            ->label('Shown in verticals')
                            ->relationship('verticals', 'name')
                            ->multiple()
                            ->preload()
                            ->helperText('This category is listed under each selected vertical.'),
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(
                                table: 'categories',
                                column: 'slug',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule, $get) => $rule->where('brand_id', $get('brand_id')),
                            ),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower number appears first inside the brand card.'),
                    ]),

                Section::make('Card on the vertical page')
                    ->description('When a visitor moves the mouse over this category on the vertical page, a card opens with this picture and line.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        self::cardImageField(),
                        self::cardSummaryField(),
                    ]),

                Section::make('Category page content')
                    ->description('Shown on the category page above the model list. Products are grouped there by their "Model group".')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('heading')
                            ->label('Page heading')
                            ->placeholder('e.g. Best Rotary Evaporator in India'),
                        Textarea::make('description')
                            ->label('Intro paragraph')
                            ->rows(4),
                        RichEditor::make('content')
                            ->fileAttachmentsDisk('public')->fileAttachmentsVisibility('public')->fileAttachmentsDirectory('editor')
                            ->label('Detailed content (below the models)')
                            ->helperText('Industries served, key benefits, guides, etc.'),
                    ]),

                Section::make('FAQs')
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Repeater::make('faqs')
                            ->label('')
                            ->addActionLabel('Add question')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                            ->defaultItems(0)
                            ->schema([
                                TextInput::make('question')->required(),
                                Textarea::make('answer')->required()->rows(3),
                            ]),
                    ]),

                \App\Filament\Support\SeoSection::make(),
            ]);
    }

    /** Shared with the "add a category" box inside the product form. */
    public static function cardImageField(): FileUpload
    {
        return FileUpload::make('image')
            ->label('Card image')
            ->helperText('Shown when someone hovers the category on the vertical page. Best size: 600 × 260 px (about 7:3, landscape) with the subject in the centre.')
            ->image()
            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
            ->disk('public')
            ->visibility('public')
            ->directory('categories');
    }

    public static function cardSummaryField(): Textarea
    {
        return Textarea::make('short_description')
            ->label('Card summary')
            ->helperText('One short line shown with the image on hover (up to 160 letters). Empty = the start of the intro paragraph is used.')
            ->maxLength(160)
            ->rows(3);
    }
}
