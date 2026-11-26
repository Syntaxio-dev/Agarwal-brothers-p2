<?php

namespace App\Sales\Rep\Resources\Prospects\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Prospects\ProspectResource;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskUi;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** One prospect: details, contact persons (call / mail / LinkedIn) and every call the team logged, newest first. */
class ViewProspect extends ViewRecord
{
    use ChecksDeskArea;

    protected static string $resource = ProspectResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ProspectResource::logCallAction()->color('primary'),
            ProspectResource::planVisitAction(),
            EditAction::make()->label('Edit')->visible(fn () => ProspectResource::mine($this->record)),
            Action::make('delete')->label('Delete')->color('danger')->icon(Heroicon::OutlinedTrash)
                ->visible(fn () => ProspectResource::mine($this->record))
                ->requiresConfirmation()
                ->modalDescription('The prospect and its contact persons leave the pool for the whole team. The calls logged to it stay in the office records.')
                ->action(function () {
                    DeskUi::attempt(fn () => app(DeskProspects::class)->delete(auth()->user(), $this->record), 'Prospect deleted');
                    $this->redirect(ProspectResource::getUrl('index'));
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.sales.rep.prospect')->columnSpanFull()->viewData([
                'p' => $this->record->load(['people', 'addedBy', 'calls.user']),
                'mine' => ProspectResource::mine($this->record),
                'tz' => config('sales.timezone'),
            ]),
        ]);
    }
}
