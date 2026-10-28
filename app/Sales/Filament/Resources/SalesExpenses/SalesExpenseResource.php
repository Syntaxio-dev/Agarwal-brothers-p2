<?php

namespace App\Sales\Filament\Resources\SalesExpenses;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesExpenses\Pages\ListSalesExpenses;
use App\Sales\Models\SalesExpense;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The reps' expenses as a review queue (see ListSalesExpenses): what waits, the bill photo next to Approve / Reject (with a reason the
 * rep sees on the phone). Nobody creates, edits or deletes an expense here.
 */
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

    public static function getPages(): array
    {
        return ['index' => ListSalesExpenses::route('/')];
    }
}
