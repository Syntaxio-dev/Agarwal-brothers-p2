<?php

namespace App\Sales\Rep\Resources\Expenses\Pages;

use App\Sales\Models\SalesExpense;
use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Expenses\ExpenseResource;
use App\Sales\Support\Money;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListExpenses extends ListRecords
{
    use ChecksDeskArea;

    protected static string $resource = ExpenseResource::class;

    public function getSubheading(): ?string
    {
        $mine = SalesExpense::where('user_id', auth()->id());
        $month = (clone $mine)->where('expense_date', '>=', now(config('sales.timezone'))->startOfMonth()->toDateString())->sum('amount_paise');
        $waiting = (clone $mine)->where('status', SalesExpense::PENDING)->sum('amount_paise');

        return 'This month ' . Money::rupees((int) $month, false) . ' · waiting for the office ' . Money::rupees((int) $waiting, false)
            . ' · new expenses are added on the phone.';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'waiting' => Tab::make('Waiting')->modifyQueryUsing(fn (Builder $query) => $query->where('status', SalesExpense::PENDING)),
            'approved' => Tab::make('Approved')->modifyQueryUsing(fn (Builder $query) => $query->where('status', SalesExpense::APPROVED)),
            'rejected' => Tab::make('Rejected')->modifyQueryUsing(fn (Builder $query) => $query->where('status', SalesExpense::REJECTED)),
        ];
    }
}
