<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Models\ClientCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        FileUpload::make('logo')
                            ->image()
                            ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                            ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                            ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('clients'),
                        Select::make('client_category_id')
                            ->label('Category')
                            ->relationship('category', 'name', modifyQueryUsing: fn ($query) => $query->orderBy('sort_order')->orderBy('name'))
                            ->searchable()
                            ->preload()
                            ->placeholder('Search or pick a category')
                            ->helperText('Type to search. Not in the list? Use the + button to add a new category right here.')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('New category name')
                                    ->required()
                                    ->maxLength(80)
                                    ->unique(table: 'client_categories', column: 'name'),
                            ])
                            ->createOptionUsing(fn (array $data) => ClientCategory::create([
                                'name' => trim($data['name']),
                                'sort_order' => (int) ClientCategory::max('sort_order') + 1,
                                'is_active' => true,
                            ])->getKey()),
                        TextInput::make('city')
                            ->maxLength(80)
                            ->helperText('For example Jaipur or Mumbai.'),
                        TextInput::make('since_year')
                            ->label('Client since (year)')
                            ->numeric()
                            ->minValue(1950)
                            ->maxValue((int) date('Y')),
                        TextInput::make('supplied')
                            ->label('What we supply them')
                            ->maxLength(160)
                            ->helperText('For example "HPLC, analytical balances, service contract".'),
                        Textarea::make('testimonial')
                            ->label('Short note (shown in the client card)')
                            ->helperText('A line or two about the client, e.g. "Trusted partner for HPLC installation and AMC since 2012."')
                            ->maxLength(180)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Homepage')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_featured')
                            ->label('Top client')
                            ->helperText('Top clients are listed first on the homepage.')
                            ->default(false),
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
