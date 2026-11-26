<?php

namespace App\Sales\Rep\Resources\Prospects\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Prospects\ProspectResource;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskUi;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProspect extends CreateRecord
{
    use ChecksDeskArea;

    protected static string $resource = ProspectResource::class;

    protected static bool $canCreateAnother = false;

    protected function handleRecordCreation(array $data): Model
    {
        return DeskUi::attempt(fn () => app(DeskProspects::class)->save(auth()->user(), null, $data));
    }

    protected function getRedirectUrl(): string
    {
        return ProspectResource::getUrl('view', ['record' => $this->record]);
    }
}
