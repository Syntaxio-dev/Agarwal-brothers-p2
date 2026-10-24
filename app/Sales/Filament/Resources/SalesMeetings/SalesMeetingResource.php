<?php

namespace App\Sales\Filament\Resources\SalesMeetings;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesMeetings\Pages\ListSalesMeetings;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\SalesExporter;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** What the reps discussed with clients, and the follow-ups they set. Read only: it all comes from the phones. */
class SalesMeetingResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-meetings';

    protected static ?string $model = SalesMeeting::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Meetings';

    protected static ?string $modelLabel = 'meeting';

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
        return parent::getEloquentQuery()->with('user');
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('meeting_at')->label('When')->dateTime('d M Y, h:i A', $tz)->sortable(),
                TextColumn::make('user.name')->label('Rep')->searchable()->weight('medium'),
                TextColumn::make('client_name')->label('Client')->searchable(),
                TextColumn::make('purpose')->limit(40)->tooltip(fn (SalesMeeting $r) => $r->purpose)->placeholder('—')->searchable(),
                TextColumn::make('discussion')->limit(60)->tooltip(fn (SalesMeeting $r) => $r->discussion)->placeholder('—')->toggleable(),
                TextColumn::make('outcome')->limit(40)->placeholder('—')->toggleable(),
                TextColumn::make('next_followup_at')
                    ->label('Follow-up')
                    ->dateTime('d M Y, h:i A', $tz)
                    ->placeholder('—')
                    ->color(fn (SalesMeeting $r) => $r->next_followup_at?->isPast() ? 'danger' : null)
                    ->sortable(),
            ])
            ->defaultSort('meeting_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
                Filter::make('followup_due')->label('Follow-up due (next 7 days or overdue)')->toggle()
                    ->query(fn ($query) => $query->whereNotNull('next_followup_at')->where('next_followup_at', '<=', now()->addDays(7))),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->where('meeting_at', '>=', \Carbon\CarbonImmutable::parse($d, config('sales.timezone'))->startOfDay()->utc()))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->where('meeting_at', '<=', \Carbon\CarbonImmutable::parse($d, config('sales.timezone'))->endOfDay()->utc()))),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Export meetings')
                    ->modalDescription('Downloads every meeting that matches the filters and search you have set now.')
                    ->modalSubmitActionLabel('Download')
                    ->fillForm(['format' => 'xlsx'])
                    ->schema([
                        Select::make('format')->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => SalesExporter::meetings($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            ->emptyStateHeading('No meetings yet')
            ->emptyStateDescription('They appear here when a rep syncs the app.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesMeetings::route('/')];
    }
}
