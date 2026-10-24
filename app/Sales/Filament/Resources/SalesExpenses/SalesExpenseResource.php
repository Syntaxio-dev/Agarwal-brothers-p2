<?php

namespace App\Sales\Filament\Resources\SalesExpenses;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesExpenses\Pages\ListSalesExpenses;
use App\Sales\Models\SalesExpense;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\Money;
use App\Sales\Support\SalesExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/** The reps' expenses: filter, look at the bill photo, approve or reject (with a reason the rep sees on the phone). */
class SalesExpenseResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-expenses';

    protected static ?string $model = SalesExpense::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Expenses';

    protected static ?string $modelLabel = 'expense';

    // Expenses come from the phones and are decided here: nobody types or deletes them in the admin.
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

    public static function getNavigationBadge(): ?string
    {
        $count = SalesExpense::where('status', SalesExpense::PENDING)->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'reviewer']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')->label('Date')->date('d M Y')->sortable(),
                TextColumn::make('user.name')->label('Rep')->searchable()->weight('medium'),
                TextColumn::make('category')->badge()->color('info')->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('amount_paise')->label('Amount')->formatStateUsing(fn ($state) => Money::rupees((int) $state))->alignEnd()->sortable(),
                TextColumn::make('note')->limit(40)->tooltip(fn (SalesExpense $r) => $r->note)->placeholder('—'),
                TextColumn::make('bill_path')
                    ->label('Bill')
                    ->formatStateUsing(fn ($state) => $state ? 'View photo' : '—')
                    ->color('primary')
                    ->url(fn (SalesExpense $r) => $r->bill_path ? route('sales.bill', $r->uuid) : null)
                    ->openUrlInNewTab(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        SalesExpense::APPROVED => 'success',
                        SalesExpense::REJECTED => 'danger',
                        default => 'warning',
                    })
                    ->description(fn (SalesExpense $r) => $r->status === SalesExpense::REJECTED ? $r->reject_reason : null),
                TextColumn::make('reviewed_at')->label('Reviewed')->since()->placeholder('—')
                    ->description(fn (SalesExpense $r) => $r->reviewer?->name)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                SelectFilter::make('status')->options(['pending' => 'Waiting', 'approved' => 'Approved', 'rejected' => 'Rejected']),
                SelectFilter::make('category')->options(collect(config('sales.expense_categories'))->mapWithKeys(fn ($c) => [$c => ucfirst($c)])->all()),
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('expense_date', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('expense_date', '<=', $d))),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Export expenses')
                    ->modalDescription('Downloads every expense that matches the filters and search you have set now.')
                    ->modalSubmitActionLabel('Download')
                    ->fillForm(['format' => 'xlsx'])
                    ->schema([
                        Select::make('format')->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => SalesExporter::expenses($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (SalesExpense $r) => $r->isPending() && auth()->user()?->can('review', $r))
                    ->action(function (SalesExpense $record) {
                        abort_unless(auth()->user()->can('review', $record) && $record->isPending(), 403);
                        $record->approve(auth()->user());
                        Notification::make()->title('Expense approved')->success()->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (SalesExpense $r) => $r->isPending() && auth()->user()?->can('review', $r))
                    ->modalHeading('Reject this expense')
                    ->modalDescription('The rep sees the reason on the phone.')
                    ->schema([
                        Textarea::make('reason')->label('Reason')->required()->minLength(3)->maxLength(500)->rows(3),
                    ])
                    ->action(function (SalesExpense $record, array $data) {
                        abort_unless(auth()->user()->can('review', $record) && $record->isPending(), 403);
                        $record->reject(auth()->user(), trim($data['reason']));
                        Notification::make()->title('Expense rejected')->success()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve_selected')
                        ->label('Approve selected')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->requiresConfirmation()
                        ->modalDescription('Only expenses that are still waiting are approved; the others are skipped.')
                        ->action(function (Collection $records) {
                            $done = $records->filter(fn (SalesExpense $r) => $r->isPending() && auth()->user()->can('review', $r))
                                ->each(fn (SalesExpense $r) => $r->approve(auth()->user()))->count();
                            Notification::make()->title($done . ' ' . str('expense')->plural($done) . ' approved')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('export_selected')
                        ->label('Export selected')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->schema([
                            Select::make('format')->label('File type')
                                ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                                ->default('xlsx')->required()->native(false),
                        ])
                        ->action(fn (Collection $records, array $data) => SalesExporter::expenses(SalesExpense::whereKey($records->modelKeys()), $data['format'])),
                ]),
            ])
            ->emptyStateHeading('No expenses yet')
            ->emptyStateDescription('They appear here when a rep syncs the app.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesExpenses::route('/')];
    }
}
