<?php

namespace App\Sales\Filament\Resources\SalesBugReports;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesBugReports\Pages\ListSalesBugReports;
use App\Sales\Models\SalesBugReport;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** What the reps reported with "Report a bug" in the app (v1.19). The manager reads it here and marks it fixed or declined, with a short answer. */
class SalesBugReportResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-bug-reports';

    protected static ?string $model = SalesBugReport::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBugAnt;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Bug reports';

    protected static ?string $modelLabel = 'bug report';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $open = SalesBugReport::query()->where('status', 'open')->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user.salesProfile');
    }

    public static function table(Table $table): Table
    {
        $answer = fn (string $status, string $label, string $color, string $icon) => Action::make($status)
            ->label($label)
            ->color($color)
            ->icon($icon)
            ->visible(fn (SalesBugReport $r) => $r->status === 'open')
            ->schema([Textarea::make('reply')->label('A short answer for the rep (optional)')->rows(2)->maxLength(500)])
            ->action(fn (SalesBugReport $r, array $data) => $r->update([
                'status' => $status,
                'reply' => filled($data['reply'] ?? null) ? trim($data['reply']) : null,
                'handled_by' => auth()->id(),
                'handled_at' => now(),
            ]));

        return $table
            ->columns([
                TextColumn::make('created_at')->label('Reported')->since()->sortable(),
                TextColumn::make('user.name')->label('Rep')->description(fn (SalesBugReport $r) => $r->user?->salesProfile?->employee_code)->searchable(),
                TextColumn::make('area')->label('Where')->badge()->formatStateUsing(fn (string $state) => SalesBugReport::AREAS[$state] ?? $state),
                TextColumn::make('body')->label('What went wrong')->searchable()->wrap(),
                TextColumn::make('app_version')->label('App')->placeholder('?')->toggleable(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (string $state) => SalesBugReport::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'fixed' => 'success', 'declined' => 'gray', default => 'warning' }),
                TextColumn::make('reply')->placeholder('-')->wrap()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(SalesBugReport::STATUSES)->default('open'),
                SelectFilter::make('area')->options(SalesBugReport::AREAS),
            ])
            ->recordActions([
                $answer('fixed', 'Fixed', 'success', 'heroicon-o-check'),
                $answer('declined', 'Decline', 'gray', 'heroicon-o-x-mark'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesBugReports::route('/')];
    }
}
