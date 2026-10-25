<?php

namespace App\Sales\Filament\Resources\SalesProductRequests\Pages;

use App\Sales\Filament\Resources\SalesProductRequests\SalesProductRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesProductRequests extends ListRecords
{
    protected static string $resource = SalesProductRequestResource::class;
}
