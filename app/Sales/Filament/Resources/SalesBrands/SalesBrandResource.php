<?php

namespace App\Sales\Filament\Resources\SalesBrands;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesBrands\Pages\CreateSalesBrand;
use App\Sales\Filament\Resources\SalesBrands\Pages\EditSalesBrand;
use App\Sales\Filament\Resources\SalesBrands\Pages\ListSalesBrands;
use App\Sales\Models\SalesBrand;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/**
 * The brand guide the reps read on the phone before they pitch or defend a brand: what it is best at, its best-known lines, where it
 * fits, selling points, answers to a client's doubt, honest limits and competitors. Which of our products carry the brand (and in which
 * budget tier) the phone works out from the product guide, so it is never typed twice.
 */
class SalesBrandResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-products';

    protected static ?string $model = SalesBrand::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Brand guide';

    protected static ?string $modelLabel = 'brand';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $lines = fn (string $name, string $label, string $help, int $max) => Repeater::make($name)
            ->label($label)
            ->helperText($help)
            ->simple(Textarea::make('item')->rows(2)->required()->maxLength(300))
            ->addActionLabel('Add a line')
            ->defaultItems(0)
            ->maxItems($max)
            ->reorderable();

        return $schema->components([
            Section::make('Brand')->columns(2)->schema([
                TextInput::make('name')->required()->minLength(2)->maxLength(100)->unique(ignoreRecord: true)
                    ->helperText('Write it exactly as in the product guide\'s brand lists (for example "Ika", "Esco Lifesciences"), so the phone can show the products it sits in.'),
                TextInput::make('origin')->label('Country and company')->maxLength(160)->placeholder('Germany · Sartorius AG'),
                TextInput::make('channel')->label('We buy it through')->maxLength(160)->placeholder('Direct, or the distributor\'s name'),
                Grid::make(2)->schema([
                    TextInput::make('sort')->label('Order')->numeric()->integer()->minValue(0)->maxValue(65000)->default(0),
                    Toggle::make('is_active')->label('Shown on the phones')->default(true)->inline(false),
                ]),
                Textarea::make('about')->label('Who they are')->rows(3)->maxLength(1000)->columnSpanFull(),
            ]),

            Section::make('Why clients buy it')->columns(2)->schema([
                $lines('known_for', 'Best at', 'What this brand is strongest in.', 6),
                $lines('known_lines', 'Best-known lines', 'The product lines / models people know it for.', 8),
                $lines('best_for', 'Where it fits best', 'Kinds of labs and industries.', 6),
                $lines('pitch', 'Selling points', 'What the rep says to pitch it.', 6),
            ]),

            Section::make('Defending it')->schema([
                Repeater::make('objections')
                    ->label('When the client says ... answer ...')
                    ->schema([
                        TextInput::make('if')->label('The client says')->required()->maxLength(200),
                        Textarea::make('then')->label('Answer')->rows(2)->required()->maxLength(400),
                    ])
                    ->addActionLabel('Add an answer')
                    ->defaultItems(0)
                    ->maxItems(6)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['if'] ?? null),
                $lines('watch_out', 'Be honest about', 'Limits a rep should know so the client is never surprised.', 4),
                Repeater::make('competitors')
                    ->label('Competitors')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(100),
                        Textarea::make('note')->label('How we stand against them')->rows(2)->maxLength(400),
                    ])
                    ->addActionLabel('Add a competitor')
                    ->defaultItems(0)
                    ->maxItems(8)
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
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('origin')->label('Country and company')->searchable()->toggleable()->wrap(),
                TextColumn::make('channel')->label('Through')->searchable()->toggleable(),
                IconColumn::make('is_active')->label('Shown')->boolean(),
                TextColumn::make('updated_at')->label('Edited')->since()->sortable()->toggleable(),
            ])
            ->defaultSort('name')
            ->filters([TernaryFilter::make('is_active')->label('Shown on the phones')])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->modalDescription('The brand leaves the guide on every phone at the next sync.'),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesBrands::route('/'),
            'create' => CreateSalesBrand::route('/create'),
            'edit' => EditSalesBrand::route('/{record}/edit'),
        ];
    }
}
