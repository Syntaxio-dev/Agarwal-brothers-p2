<?php

namespace App\Sales\Filament\Resources\SalesPositions\Pages;

use App\Sales\Filament\Resources\SalesPositions\SalesPositionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSalesPositions extends ManageRecords
{
    protected static string $resource = SalesPositionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->mutateDataUsing(fn (array $data) => $data + ['is_preset' => false, 'created_by' => auth()->id()])];
    }
}
