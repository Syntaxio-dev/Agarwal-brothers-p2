<?php

namespace App\Sales\Filament\Resources\SalesClients;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesClients\Pages\ListSalesClients;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\SalesExporter;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** The people and companies the reps met. Read only: it all comes from the phones. */
class SalesClientResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-clients';

    protected static ?string $model = SalesClient::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'client';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('owner');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium')->description(fn (SalesClient $r) => $r->company),
                TextColumn::make('phone')->placeholder('—')->searchable()->copyable(),
                TextColumn::make('city')->placeholder('—')->searchable(),
                TextColumn::make('owner.name')->label('Rep')->searchable(),
                TextColumn::make('notes')->limit(60)->tooltip(fn (SalesClient $r) => $r->notes)->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->label('Added')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Export clients')
                    ->modalDescription('Downloads every client that matches the filters and search you have set now.')
                    ->modalSubmitActionLabel('Download')
                    ->fillForm(['format' => 'xlsx'])
                    ->schema([
                        Select::make('format')->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => SalesExporter::clients($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            ->emptyStateHeading('No clients yet')
            ->emptyStateDescription('They appear here when a rep syncs the app.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesClients::route('/')];
    }
}
