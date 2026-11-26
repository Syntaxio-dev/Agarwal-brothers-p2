<?php

namespace App\Sales\Rep\Resources\Prospects\Pages;

use App\Sales\Models\SalesProspectPerson;
use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Prospects\ProspectResource;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskUi;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * One prospect: the company name as the page title, its territory and who added it, the contact persons (call / mail / LinkedIn, and for
 * the rep who added it: add, change, remove) and every call the team logged, newest first.
 */
class ViewProspect extends ViewRecord
{
    use ChecksDeskArea;

    protected static string $resource = ProspectResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function getSubheading(): ?string
    {
        $who = ProspectResource::mine($this->record) ? 'you' : ($this->record->addedBy?->name ?? 'the office');

        return $this->record->territory . ' · added by ' . $who;
    }

    protected function getHeaderActions(): array
    {
        $mine = fn () => ProspectResource::mine($this->record);

        return [
            ProspectResource::logCallAction()->color('primary'),
            $this->addPersonAction(),
            ProspectResource::planVisitAction(),
            ActionGroup::make([
                EditAction::make()->label('Edit details')->visible($mine),
                Action::make('delete')->label('Delete prospect')->color('danger')->icon(Heroicon::OutlinedTrash)
                    ->visible($mine)
                    ->requiresConfirmation()
                    ->modalDescription('The prospect and its contact persons leave the pool for the whole team. The calls logged to it stay in the office records.')
                    ->action(function () {
                        DeskUi::attempt(fn () => app(DeskProspects::class)->delete(auth()->user(), $this->record), 'Prospect deleted');
                        $this->redirect(ProspectResource::getUrl('index'));
                    }),
            ])->visible($mine)->label('More')->icon(Heroicon::OutlinedEllipsisVertical)->color('gray')->button(),
        ];
    }

    /** Only the rep who added the prospect adds people (they are part of its details, the same rule as the app). */
    public function addPersonAction(): Action
    {
        return Action::make('addPerson')->label('Add contact person')->icon(Heroicon::OutlinedUserPlus)->color('gray')
            ->visible(fn () => ProspectResource::mine($this->record))
            ->modalHeading('Add contact person')
            ->modalDescription(fn () => 'At ' . $this->record->name . '. At most ' . DeskProspects::MAX_PEOPLE . ' people per company.')
            ->modalSubmitActionLabel('Add person')
            ->schema(ProspectResource::personFields())
            ->action(function (array $data) {
                DeskUi::attempt(fn () => app(DeskProspects::class)->addPerson(auth()->user(), $this->record, $data), $data['name'] . ' added');
                $this->refreshRecord();
            });
    }

    public function editPersonAction(): Action
    {
        return Action::make('editPerson')->label('Change')
            ->visible(fn () => ProspectResource::mine($this->record))
            ->modalHeading(fn (array $arguments) => 'Change ' . ($this->person($arguments)?->name ?? 'person'))
            ->modalSubmitActionLabel('Save person')
            ->fillForm(fn (array $arguments) => $this->person($arguments)?->only(['name', 'position', 'phone', 'email', 'linkedin']) ?? [])
            ->schema(ProspectResource::personFields())
            ->action(function (array $data, array $arguments) {
                DeskUi::attempt(fn () => app(DeskProspects::class)->updatePerson(auth()->user(), $this->record, (int) ($arguments['person'] ?? 0), $data), 'Person saved');
                $this->refreshRecord();
            });
    }

    public function removePersonAction(): Action
    {
        return Action::make('removePerson')->label('Remove')->color('danger')
            ->visible(fn () => ProspectResource::mine($this->record))
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => 'Remove ' . ($this->person($arguments)?->name ?? 'this person') . '?')
            ->modalDescription('The person and their number leave this prospect for the whole team.')
            ->modalSubmitActionLabel('Remove')
            ->action(function (array $arguments) {
                DeskUi::attempt(fn () => app(DeskProspects::class)->removePerson(auth()->user(), $this->record, (int) ($arguments['person'] ?? 0)), 'Person removed');
                $this->refreshRecord();
            });
    }

    /** A person of THIS prospect only. */
    private function person(array $arguments): ?SalesProspectPerson
    {
        return $this->record->people()->whereKey((int) ($arguments['person'] ?? 0))->first();
    }

    private function refreshRecord(): void
    {
        $this->record = ProspectResource::getEloquentQuery()->whereKey($this->record->getKey())->firstOrFail();
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.sales.rep.prospect')->columnSpanFull()->viewData(fn () => [
                'p' => $this->record->load(['people', 'addedBy', 'calls.user']),
                'mine' => ProspectResource::mine($this->record),
                'tz' => config('sales.timezone'),
            ]),
        ]);
    }
}
