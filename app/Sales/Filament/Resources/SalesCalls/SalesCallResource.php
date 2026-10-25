<?php

namespace App\Sales\Filament\Resources\SalesCalls;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesCalls\Pages\ListSalesCalls;
use App\Sales\Models\SalesCall;
use App\Sales\Models\SalesProfile;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The calls the reps made and took with the numbers of the client pool. Read only: the phone sends only calls that match a saved
 * number (a personal call never reaches the office), and a log is never edited.
 */
class SalesCallResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-calls';

    protected static ?string $model = SalesCall::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Calls';

    protected static ?string $modelLabel = 'call';

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
        $day = fn (string $d, bool $end) => ($end ? CarbonImmutable::parse($d, config('sales.timezone'))->endOfDay() : CarbonImmutable::parse($d, config('sales.timezone'))->startOfDay())->utc();

        return $table
            ->columns([
                TextColumn::make('started_at')->label('When')->dateTime('d M, h:i A', config('sales.timezone'))->sortable(),
                TextColumn::make('user.name')->label('Rep')->searchable(),
                TextColumn::make('direction')->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) { 'missed' => 'danger', 'incoming' => 'info', default => 'success' }),
                TextColumn::make('client_name')->label('Client')->placeholder('—')->searchable()->description(fn (SalesCall $r) => $r->contact_name),
                TextColumn::make('number')->searchable()->copyable(),
                TextColumn::make('duration_seconds')->label('Talk time')->alignEnd()
                    ->formatStateUsing(fn ($state) => $state > 0 ? sprintf('%d:%02d', intdiv((int) $state, 60), (int) $state % 60) : '—')->sortable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
                SelectFilter::make('direction')->options(['incoming' => 'Incoming', 'outgoing' => 'Outgoing', 'missed' => 'Missed']),
                Filter::make('date')
                    ->schema([DatePicker::make('from')->label('From'), DatePicker::make('until')->label('Until')])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->where('started_at', '>=', $day($d, false)))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->where('started_at', '<=', $day($d, true)))),
            ])
            ->emptyStateHeading('No calls yet')
            ->emptyStateDescription('They appear here when a rep allows call matching in the app and syncs. Only calls with numbers saved in the client pool are sent.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesCalls::route('/')];
    }
}
