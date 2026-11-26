<?php

namespace App\Sales\Rep\Resources\Prospects\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Prospects\ProspectResource;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\RepTerritories;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProspect extends CreateRecord
{
    use ChecksDeskArea;

    protected static string $resource = ProspectResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'New prospect';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $data['territory'] = ProspectResource::chosenTerritory($data);
        unset($data['new_territory']);

        $prospect = DeskUi::attempt(fn () => app(DeskProspects::class)->save(auth()->user(), null, $data));

        // the territory joins "My territories", so the new company shows in the rep's own list (as long as there is room)
        RepTerritories::remember(auth()->user(), $prospect->territory);

        return $prospect;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Prospect saved';
    }

    protected function getRedirectUrl(): string
    {
        return ProspectResource::getUrl('view', ['record' => $this->record]);
    }
}
