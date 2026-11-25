<?php

namespace App\Sales\Filament\Resources\SalesProspects;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesProspects\Pages\ListSalesProspects;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesProspectNote;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The prospect pool (app v1.19): companies that do not work with us yet, found by the reps for cold calling, sorted by territory.
 * The reps write it on the phone; the manager reads it here (people, calls, who added it) and can remove a record that should not be there.
 */
class SalesProspectResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-prospects';

    protected static ?string $model = SalesProspect::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Prospects';

    protected static ?string $modelLabel = 'prospect';

    public static function canCreate(): bool
    {
        return false; // written on the phone
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /** The newest call's outcome and time, worked out in the same query (a list of thousands must not ask once per row). */
    public static function getEloquentQuery(): Builder
    {
        $latest = fn (string $column) => SalesProspectNote::query()->select($column)->whereColumn('prospect_uuid', 'sales_prospects.uuid')->orderByDesc('noted_at')->limit(1);

        return parent::getEloquentQuery()
            ->select('sales_prospects.*')
            ->addSelect(['last_outcome' => $latest('outcome'), 'last_called_at' => $latest('noted_at')])
            ->with('addedBy.salesProfile')
            ->withCount(['people', 'calls']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Company')->searchable()->sortable()->wrap(),
                TextColumn::make('territory')->searchable()->sortable(),
                TextColumn::make('people_count')->label('People')->alignCenter(),
                TextColumn::make('last_outcome')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => SalesProspectNote::labelOf($state))
                    ->color(fn (?string $state) => match ($state) {
                        'interested', 'meeting_fixed', 'converted' => 'success',
                        'not_interested', 'wrong_number', 'do_not_call' => 'gray',
                        null => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('calls_count')->label('Calls')->alignCenter(),
                TextColumn::make('last_called_at')->label('Last call')->since()->placeholder('Never'),
                TextColumn::make('addedBy.name')->label('Added by')->description(fn (SalesProspect $p) => $p->addedBy?->salesProfile?->employee_code)->searchable(),
                TextColumn::make('source')->formatStateUsing(fn (?string $state) => SalesProspect::SOURCES[$state] ?? '')->toggleable(),
                TextColumn::make('created_at')->label('Added')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('territory')
                    ->options(fn () => SalesProspect::query()->selectRaw('MIN(territory) as territory')->groupBy('territory_key')->orderBy('territory')->pluck('territory', 'territory')->all())
                    ->searchable(),
                SelectFilter::make('added_by')->label('Added by')->relationship('addedBy', 'name')->searchable()->preload(),
                SelectFilter::make('status')->label('Status')
                    ->options(['new' => 'Not called yet'] + collect(SalesProspectNote::OUTCOMES)->map(fn ($o) => $o[0])->all())
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if (! filled($value)) {
                            return $query;
                        }
                        $latest = '(select outcome from sales_prospect_notes where prospect_uuid = sales_prospects.uuid order by noted_at desc limit 1)';

                        return $value === 'new'
                            ? $query->whereRaw("$latest is null")
                            : $query->whereRaw("$latest = ?", [$value]);
                    }),
            ])
            ->recordActions([
                Action::make('details')
                    ->label('People and calls')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (SalesProspect $p) => $p->name)
                    ->modalContent(fn (SalesProspect $p) => view('filament.sales.prospect-details', ['prospect' => $p->load(['people', 'calls.user.salesProfile', 'addedBy'])]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->slideOver(),
                DeleteAction::make()->modalDescription('The prospect leaves the pool for the whole team. Its calls stay in the office records.'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesProspects::route('/')];
    }
}
