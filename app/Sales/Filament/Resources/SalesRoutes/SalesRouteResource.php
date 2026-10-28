<?php

namespace App\Sales\Filament\Resources\SalesRoutes;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesRoutes\Pages\ListSalesRoutes;
use App\Sales\Models\SalesMeeting;
use App\Sales\Models\SalesProfile;
use App\Sales\Models\SalesRoute;
use Carbon\CarbonImmutable;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * The trips the reps planned: several clients on one day, in the order the rep will go. Read only: the rep owns the route on the phone.
 * It uses the same permission as the Meetings screen.
 */
class SalesRouteResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-meetings';

    protected static ?string $model = SalesRoute::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Routes';

    protected static ?string $modelLabel = 'route';

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

    /** What the "Open" window shows: the route and its stops in order. */
    public static function infolistComponents(): array
    {
        $tz = config('sales.timezone');

        return [
            Section::make()->columns(3)->schema([
                TextEntry::make('title')->label('Route'),
                TextEntry::make('user.name')->label('Rep'),
                TextEntry::make('route_date')->label('Day')->date('d M Y'),
                TextEntry::make('area')->label('Place')->placeholder('—'),
                TextEntry::make('start_time')->label('Starts at'),
                TextEntry::make('progress')->label('Progress')->state(function (SalesRoute $r) {
                    $p = $r->progress();

                    return "{$p['finished']} of {$p['total']} done";
                }),
            ]),
            Section::make('Stops, in order')->schema([
                TextEntry::make('stops')->hiddenLabel()->html()->state(function (SalesRoute $r) use ($tz) {
                    $rows = $r->stops()->map(fn (SalesMeeting $m, int $i) => '<tr><td style="padding:4px 12px 4px 0">' . ($i + 1) . '</td>'
                        . '<td style="padding:4px 12px"><strong>' . e($m->client_name) . '</strong>' . ($m->purpose ? '<br><span style="opacity:.6">' . e($m->purpose) . '</span>' : '') . '</td>'
                        . '<td style="padding:4px 12px">' . e($m->plannedOrActualAt()->timezone($tz)->format('h:i A')) . '</td>'
                        . '<td style="padding:4px 12px">' . e($m->statusLabel()) . ($m->resultLabel() ? ' (' . e($m->resultLabel()) . ')' : '') . '</td></tr>')->implode('');

                    return new HtmlString($rows
                        ? '<table style="width:100%;font-size:.9rem"><thead><tr style="text-align:left;opacity:.6"><th>#</th><th>Client</th><th>Time</th><th>Status</th></tr></thead><tbody>' . $rows . '</tbody></table>'
                        : '<span style="opacity:.6">No stops have reached the office yet.</span>');
                }),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('route_date')->label('Day')->date('d M Y')->sortable(),
                TextColumn::make('user.name')->label('Rep')->searchable()->weight('medium'),
                TextColumn::make('title')->label('Route')->searchable()->description(fn (SalesRoute $r) => $r->area),
                TextColumn::make('start_time')->label('Starts')->sortable(),
                TextColumn::make('stops_count')->label('Stops')->alignEnd()
                    ->getStateUsing(fn (SalesRoute $r) => count($r->stops_order ?? [])),
                TextColumn::make('progress')->label('Progress')->badge()
                    ->getStateUsing(function (SalesRoute $r) {
                        $p = $r->progress();

                        return "{$p['finished']} of {$p['total']} done";
                    })
                    ->color(function (SalesRoute $r) {
                        $p = $r->progress();

                        return $p['total'] > 0 && $p['finished'] === $p['total'] ? 'success' : 'gray';
                    }),
            ])
            ->defaultSort('route_date', 'desc')
            ->filters([
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
                Filter::make('upcoming')->label('Today and later')->toggle()
                    ->query(fn (Builder $q) => $q->where('route_date', '>=', CarbonImmutable::now(config('sales.timezone'))->toDateString())),
                Filter::make('date')
                    ->schema([DatePicker::make('from')->label('From'), DatePicker::make('until')->label('Until')])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->where('route_date', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->where('route_date', '<=', $d))),
            ])
            ->recordActions([ViewAction::make()->label('Open')->schema(static::infolistComponents())])
            ->emptyStateHeading('No routes yet')
            ->emptyStateDescription('A route appears here when a rep plans several visits of one day as one trip and syncs the app.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesRoutes::route('/')];
    }
}
