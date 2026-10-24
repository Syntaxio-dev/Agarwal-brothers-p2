<?php

namespace App\Sales\Filament\Resources\SalesExpenses\Pages;

use App\Sales\Filament\Resources\SalesExpenses\SalesExpenseResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesExpenses extends ListRecords
{
    protected static string $resource = SalesExpenseResource::class;
}
