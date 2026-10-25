<?php

namespace App\Sales\Filament\Resources\SalesEnquiries\Pages;

use App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesEnquiries extends ListRecords
{
    protected static string $resource = SalesEnquiryResource::class;
}
