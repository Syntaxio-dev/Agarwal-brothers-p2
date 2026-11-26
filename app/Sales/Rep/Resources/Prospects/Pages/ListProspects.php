<?php

namespace App\Sales\Rep\Resources\Prospects\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Prospects\ProspectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProspects extends ListRecords
{
    use ChecksDeskArea;

    protected static string $resource = ProspectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New prospect')->icon(Heroicon::OutlinedPlus)];
    }
}
