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
                        FileUpload::make('image')
                            ->image()
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('categories'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower number appears first inside the brand card.'),
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
}
