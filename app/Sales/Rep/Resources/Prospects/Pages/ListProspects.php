<?php

namespace App\Sales\Rep\Resources\Prospects\Pages;

use App\Sales\Models\SalesProspect;
use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Prospects\ProspectResource;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\RepTerritories;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

/**
 * The prospects, like the app: capsules "My territories" / "All territories" / one per territory of the rep above the list, the
 * "My territories" chooser, and "New prospect" (with the territory of the open capsule already picked).
 */
class ListProspects extends ListRecords
{
    use ChecksDeskArea;

    protected static string $resource = ProspectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->territoriesAction(),
            Action::make('create')->label('New prospect')->icon(Heroicon::OutlinedPlus)
                ->url(fn () => ProspectResource::getUrl('create', array_filter(['territory' => $this->openTerritory()]))),
        ];
    }

    /** The territory of the capsule that is open (a single territory), for the new prospect. */
    private function openTerritory(): ?string
    {
        $tab = (string) $this->activeTab;
        $mine = RepTerritories::mine(auth()->user());

        if (str_starts_with($tab, 't') && ctype_digit(substr($tab, 1))) {
            return $mine[(int) substr($tab, 1)] ?? null;
        }

        return count($mine) === 1 ? $mine[0] : null;
    }

    private function territoriesAction(): Action
    {
        return Action::make('territories')->label('My territories')->icon(Heroicon::OutlinedMapPin)->color('gray')
            ->modalHeading('My territories')
            ->modalDescription('Pick the areas you work in. Your list shows the companies there. Up to ' . RepTerritories::MAX_MINE . '.')
            ->modalSubmitActionLabel('Save')
            ->fillForm(fn () => ['mine' => RepTerritories::mine(auth()->user())])
            ->schema(function () {
                $team = RepTerritories::team();
                $options = RepTerritories::picks(auth()->user());

                return [
                    CheckboxList::make('mine')->label('Territories')->options($options)->columns(3)->searchable()->bulkToggleable()
                        ->descriptions(collect($options)->map(fn ($name) => ($n = $team[$name] ?? 0) ? $n . ' ' . ($n === 1 ? 'prospect' : 'prospects') : 'No prospect yet')->all()),
                    TextInput::make('add')->label('Add another territory')->maxLength(100)->placeholder('For example Behror')
                        ->regex(RepTerritories::PATTERN)->validationMessages(['regex' => "A territory uses letters, spaces and . , ' - only."]),
                ];
            })
            ->action(function (array $data) {
                $list = array_values((array) ($data['mine'] ?? []));
                if (filled($data['add'] ?? null)) {
                    $list[] = (string) $data['add'];
                }
                DeskUi::attempt(fn () => RepTerritories::save(auth()->user(), $list), 'Your territories are saved');
                $this->activeTab = RepTerritories::mine(auth()->user()) === [] ? 'all' : 'mine';

                // the capsules are built once per request: open the list again, so they (and the list under them) show the new choice
                $this->redirect(ProspectResource::getUrl('index', ['tab' => $this->activeTab]));
            });
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return RepTerritories::mine(auth()->user()) === [] ? 'all' : 'mine';
    }

    public function getTabs(): array
    {
        $mine = RepTerritories::mine(auth()->user());
        $keys = array_map(fn ($t) => SalesProspect::keyOf($t), $mine);
        $counts = SalesProspect::query()->selectRaw('territory_key, COUNT(*) as n')->groupBy('territory_key')->pluck('n', 'territory_key');

        $tabs = [
            'mine' => Tab::make($mine ? 'My territories' : 'My territories (choose)')
                ->badge(array_sum(array_map(fn ($k) => (int) ($counts[$k] ?? 0), $keys)) ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $keys === [] ? $query->whereRaw('1 = 0') : $query->whereIn('sales_prospects.territory_key', $keys)),
            'all' => Tab::make('All territories')->badge((int) $counts->sum() ?: null),
        ];

        foreach ($mine as $i => $name) {
            $key = $keys[$i];
            $tabs['t' . $i] = Tab::make($name)->badge((int) ($counts[$key] ?? 0) ?: null)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sales_prospects.territory_key', $key));
        }

        return $tabs;
    }
}
