<?php

namespace App\Sales\Filament\Resources\SalesProducts;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesProducts\Pages\CreateSalesProduct;
use App\Sales\Filament\Resources\SalesProducts\Pages\EditSalesProduct;
use App\Sales\Filament\Resources\SalesProducts\Pages\ListSalesProducts;
use App\Sales\Models\SalesProduct;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The product guide the reps read on the phone: what a product is, which brands fit a premium / economy / lite budget, what to ask,
 * what goes wrong early and late, and the top brands. Only managers write it here; reps ask for changes from the app.
 */
class SalesProductResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-products';

    protected static ?string $model = SalesProduct::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationLabel = 'Product guide';

    protected static ?string $modelLabel = 'product';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $lines = fn (string $name, string $label, string $help) => Repeater::make($name)
            ->label($label)
            ->helperText($help)
            ->simple(Textarea::make('item')->rows(2)->required()->maxLength(1000))
            ->addActionLabel('Add a line')
            ->defaultItems(0)
            ->maxItems(40)
            ->reorderable()
            ->collapsible()
            ->columnSpanFull();

        $short = fn (string $name, string $label, string $help, int $max, int $length) => Repeater::make($name)
            ->label($label)
            ->helperText($help)
            ->simple(TextInput::make('item')->required()->maxLength($length))
            ->addActionLabel('Add a line')
            ->defaultItems(0)
            ->maxItems($max)
            ->reorderable();

        return $schema->components([
            Section::make('Product')->columns(2)->schema([
                TextInput::make('name')->required()->minLength(2)->maxLength(200)->columnSpanFull(),
                TextInput::make('group')->label('Family')->maxLength(100)->datalist(fn () => SalesProduct::groups())
                    ->helperText('Products of one family (for example Centrifuges) are shown together on the phone.'),
                TextInput::make('sort')->label('Order')->numeric()->integer()->minValue(0)->maxValue(65000)->default(0),
                Toggle::make('is_active')->label('Shown on the phones')->default(true),
            ]),

            Section::make('Brands by budget')
                ->description('What a rep suggests when the client has a big, a middle or a small budget.')
                ->columns(3)
                ->schema([
                    TagsInput::make('premium_brands')->label('Premium (big budget)')->placeholder('Type a brand, press Enter'),
                    TagsInput::make('economy_brands')->label('Economy (middle budget)')->placeholder('Type a brand, press Enter'),
                    TagsInput::make('lite_brands')->label('Lite (small budget)')->placeholder('Type a brand, press Enter'),
                ]),

            Section::make('About the product')->schema([
                Textarea::make('summary')->label('What it is and where it is used')->rows(5)->maxLength(6000),
                Repeater::make('variants')
                    ->label('Types in the market')
                    ->schema([
                        TextInput::make('name')->maxLength(200),
                        Textarea::make('text')->label('Description')->rows(2)->required()->maxLength(2000),
                    ])
                    ->addActionLabel('Add a type')
                    ->defaultItems(0)
                    ->maxItems(20)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                Textarea::make('industries')->label('Industries')->rows(2)->maxLength(2000),
            ]),

            Section::make('Before the meeting')
                ->description('What the rep sees first on the phone. Put **two stars** around the words that should be highlighted, for example "spins a sample fast so the **heavy parts settle**". Everything here is optional.')
                ->schema([
                    CheckboxList::make('client_types')->label('Usually bought by')->options(SalesProduct::CLIENT_TYPES)->columns(3)
                        ->helperText('The product is suggested when the rep says they are meeting this kind of client.'),
                    Textarea::make('pitch')->label('The 20-second pitch')->rows(3)->maxLength(400)->helperText('At most two sentences.'),
                    $short('key_notes', 'Key notes for the cheat sheet', 'Three to five short lines a rep can memorise.', 5, 160),
                    TextInput::make('formula')->label('A formula (optional)')->maxLength(300),
                    Grid::make(2)->schema([
                        $short('do_list', 'Do', 'Up to three short lines.', 3, 140),
                        $short('avoid_list', 'Avoid', 'Up to three short lines.', 3, 140),
                    ])->columnSpanFull(),
                    Repeater::make('match_rules')
                        ->label('If the client says ... offer ...')
                        ->schema([
                            TextInput::make('if')->label('The client says')->required()->maxLength(160),
                            TextInput::make('then')->label('Offer')->required()->maxLength(80),
                        ])
                        ->addActionLabel('Add an answer')
                        ->defaultItems(0)
                        ->maxItems(4)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['if'] ?? null),
                ]),

            Section::make('Talking to the client')->schema([
                $lines('key_parameters', 'Questions to ask the client', 'What the rep should find out before quoting.'),
            ]),

            Section::make('What goes wrong')->columns(2)->schema([
                $lines('short_term_problems', 'First 12 months', 'Setup, calibration and early faults.'),
                $lines('long_term_problems', 'After a year', 'Wear, running cost and the things a client should know before buying.'),
            ]),

            Section::make('Top brands')->schema([
                Repeater::make('top_brands')
                    ->label('Why these brands are good')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(150),
                        Textarea::make('note')->label('Why')->rows(2)->maxLength(1000),
                    ])
                    ->addActionLabel('Add a brand')
                    ->defaultItems(0)
                    ->maxItems(20)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort')->label('#')->sortable()->toggleable(),
                TextColumn::make('name')->searchable()->weight('medium')->wrap(),
                TextColumn::make('group')->label('Family')->badge()->color('gray')->sortable()->searchable(),
                TextColumn::make('premium_brands')->label('Premium')->badge()->color('info')->separator(',')->limitList(2)->searchable(),
                TextColumn::make('economy_brands')->label('Economy')->badge()->color('success')->separator(',')->limitList(2)->searchable(),
                TextColumn::make('lite_brands')->label('Lite')->badge()->color('warning')->separator(',')->limitList(2)->searchable(),
                IconColumn::make('is_active')->label('Shown')->boolean(),
                TextColumn::make('updated_at')->label('Edited')->since()->sortable()->toggleable(),
            ])
            ->defaultSort('sort')
            ->filters([
                SelectFilter::make('group')->label('Family')->options(fn () => collect(SalesProduct::groups())->mapWithKeys(fn ($g) => [$g => $g])->all()),
                TernaryFilter::make('is_active')->label('Shown on the phones'),
                SelectFilter::make('tier')->label('Has brands in')->options(['premium_brands' => 'Premium', 'economy_brands' => 'Economy', 'lite_brands' => 'Lite'])
                    ->query(fn (Builder $q, array $data) => filled($data['value'] ?? null) ? $q->whereNotNull($data['value'])->where($data['value'], '!=', '[]') : $q),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->modalDescription('The product leaves the guide on every phone at the next sync.'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesProducts::route('/'),
            'create' => CreateSalesProduct::route('/create'),
            'edit' => EditSalesProduct::route('/{record}/edit'),
        ];
    }
}
