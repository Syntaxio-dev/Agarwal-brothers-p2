<?php

namespace App\Sales\Filament\Resources\SalesMeetings\Pages;

use App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesMeetings extends ListRecords
{
    protected static string $resource = SalesMeetingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('review')->label('Visit review')->icon(\Filament\Support\Icons\Heroicon::OutlinedRectangleStack)
                ->url(SalesMeetingResource::getUrl('review')),
        ];
    }
}
