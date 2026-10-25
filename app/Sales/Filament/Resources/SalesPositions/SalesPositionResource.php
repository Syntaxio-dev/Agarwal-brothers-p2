<?php

namespace App\Sales\Filament\Resources\SalesPositions;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesPositions\Pages\ManageSalesPositions;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesPosition;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** The position (job title) list behind the dropdown on the phones. The reps add to it from the app; a manager keeps it tidy. */
class SalesPositionResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-positions';

    protected static ?string $model = SalesPosition::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Positions';

    protected static ?string $modelLabel = 'position';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->minLength(2)->maxLength(60)
                ->regex('/^[\p{L}\p{N} &\/().,\'-]+$/u')
                ->validationMessages(['regex' => 'Use letters, digits and & / ( ) . , - only.'])
                ->unique(table: 'sales_positions', column: 'name', ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                IconColumn::make('is_preset')->label('Built in')->boolean(),
                TextColumn::make('people')->label('People')->alignCenter()
                    ->getStateUsing(fn (SalesPosition $r) => SalesContact::where('position', $r->name)->count()),
                TextColumn::make('created_at')->label('Added')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()->visible(fn (SalesPosition $r) => ! $r->is_preset),
                DeleteAction::make()->visible(fn (SalesPosition $r) => ! $r->is_preset)
                    ->modalDescription('People who already have this position keep it; it only leaves the dropdown list.'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSalesPositions::route('/')];
    }
}
