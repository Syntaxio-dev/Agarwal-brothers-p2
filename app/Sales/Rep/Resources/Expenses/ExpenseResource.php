<?php

namespace App\Sales\Rep\Resources\Expenses;

use App\Sales\Models\SalesExpense;
use App\Sales\Rep\Resources\Expenses\Pages\ListExpenses;
use App\Sales\Rep\Support\RepDesk;
use App\Sales\Support\Money;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "My expenses" on the web desk, VIEW ONLY: the rep's own expenses (the same as on the phone), with the bill photo and the office's
 * decision. Nothing is added or changed here: a new expense is made on the phone, where the bill photo must come from the camera.
 */
class ExpenseResource extends Resource
{
    protected static ?string $model = SalesExpense::class;

    protected static ?string $slug = 'expenses';

    protected static ?string $modelLabel = 'expense';

    protected static ?string $navigationLabel = 'Expenses';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return RepDesk::allows(auth()->user(), 'expenses');
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

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
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_date')->label('Date')->date('D j M Y')->sortable(),
                TextColumn::make('category')->label('Category')->formatStateUsing(fn (?string $state) => SalesExpense::categoryLabel($state))
                    ->description(fn (SalesExpense $e) => $e->note),
                TextColumn::make('amount_paise')->label('Amount')->alignEnd()->sortable()
                    ->formatStateUsing(fn (int $state) => Money::rupees($state, false)),
                TextColumn::make('work_area')->label('Where')->formatStateUsing(fn (?string $state) => SalesExpense::WORK_AREAS[$state] ?? null)->placeholder('—')->toggleable(),
                TextColumn::make('bill_type')->label('Bill')
                    ->getStateUsing(fn (SalesExpense $e) => $e->hasBill() ? 'Photo' : (SalesExpense::BILL_TYPES[$e->bill_type] ?? '—')),
                TextColumn::make('status')->label('Office')->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) { 'approved' => 'Approved', 'rejected' => 'Rejected', default => 'Waiting' })
                    ->color(fn (string $state) => match ($state) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' })
                    ->description(fn (SalesExpense $e) => $e->status === SalesExpense::REJECTED && filled($e->reject_reason) ? $e->reject_reason : null),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                SelectFilter::make('category')->label('Category')->options(SalesExpense::CATEGORY_LABELS)->multiple(),
                SelectFilter::make('work_area')->label('Where')->options(SalesExpense::WORK_AREAS),
            ])
            ->recordActions([
                Action::make('bill')->label('See bill')->icon(Heroicon::OutlinedPhoto)->color('gray')
                    ->visible(fn (SalesExpense $record) => $record->hasBill())
                    ->url(fn (SalesExpense $record) => route('filament.rep.bill', ['uuid' => $record->uuid]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No expenses here')
            ->emptyStateDescription('Expenses are added on your phone (with the bill photo). They show here after the phone syncs.');
    }

    public static function getPages(): array
    {
        return ['index' => ListExpenses::route('/')];
    }
}
