<?php

namespace App\Sales\Rep\Resources\Prospects\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Prospects\ProspectResource;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskUi;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/** Only the rep who added the prospect reaches this page (ProspectResource::canEdit); the sync checks it again. */
class EditProspect extends EditRecord
{
    use ChecksDeskArea;

    protected static string $resource = ProspectResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['people'] = $this->record->people()->get()
            ->map(fn ($p) => ['name' => $p->name, 'position' => $p->position, 'phone' => $p->phone, 'email' => $p->email, 'linkedin' => $p->linkedin])
            ->all();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DeskUi::attempt(fn () => app(DeskProspects::class)->save(auth()->user(), $record, $data));
    }

    protected function getRedirectUrl(): string
    {
        return ProspectResource::getUrl('view', ['record' => $this->record]);
    }
}
