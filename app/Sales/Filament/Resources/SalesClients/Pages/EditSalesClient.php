<?php

namespace App\Sales\Filament\Resources\SalesClients\Pages;

use App\Sales\Actions\SetClientAccess;
use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Support\SalesInsights;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

/**
 * "Who sees this client" (design 6a): the client list on the left, and on the right who sees the selected client: the main rep,
 * the reps it is also open for, and the rep who added it. Everything the manager ticks stays on the screen until "Save who sees
 * it"; the one write path is SetClientAccess. The old edit form is behind "Edit details".
 */
class EditSalesClient extends EditRecord
{
    protected static string $resource = SalesClientResource::class;

    /** Search and chip of the client list on the left. */
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'show')]
    public string $chip = 'all';

    /** 'one' | 'several' */
    public string $mode = 'one';

    /** @var list<int> the ticked reps (not the rep who added the client: that one is always in) */
    public array $selected = [];

    public ?int $main = null;

    /** What is saved now, to know whether anything changed. */
    public array $savedSelected = [];

    public ?int $savedMain = null;

    public bool $details = false;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->loadAccess();
    }

    public function getTitle(): string
    {
        return $this->getRecord()->name;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    /** The same buttons the old list had, so nothing is lost: add a client / person, import, export, and the plain table. */
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('addClient')->label('Add client')->icon(\Filament\Support\Icons\Heroicon::OutlinedPlus)
                ->url(SalesClientResource::getUrl('create')),
            \Filament\Actions\Action::make('addPerson')->label('Add contact person')->icon(\Filament\Support\Icons\Heroicon::OutlinedUserPlus)->color('gray')
                ->modalHeading('Add a contact person')->modalSubmitActionLabel('Save person')
                ->schema(\App\Sales\Filament\Resources\SalesContacts\SalesContactResource::personFields())
                ->action(function (array $data) {
                    abort_unless(auth()->user()?->canManage('sales-clients'), 403);
                    $person = SalesContact::create($data + ['user_id' => auth()->id()]);
                    Notification::make()->title($person->name . ' was added')->success()->send();
                }),
            SalesClientResource::importAction(),
            SalesClientResource::exportAction(),
            \Filament\Actions\Action::make('tableView')->label('Table view')->icon(\Filament\Support\Icons\Heroicon::OutlinedTableCells)->color('gray')
                ->url(SalesClientResource::getUrl('index')),
        ];
    }

    /** The screen first; the old form and the people list only when "Edit details" is open. */
    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.sales.clients.access'),
            Group::make([$this->getFormContentComponent()])->visible(fn () => $this->details),
            Group::make([$this->getRelationManagersContentComponent()])->visible(fn () => $this->details),
        ]);
    }

    // ---- the choice ---------------------------------------------------------------------------------------------------------

    private function loadAccess(): void
    {
        $id = $this->getRecord()->getKey();
        $access = SalesInsights::clientAccessList([$id])[$id] ?? ['main' => null, 'also' => [], 'added_by' => null];

        $locked = $access['added_by']['id'] ?? null;
        $this->main = $access['main']['id'] ?? null;
        $this->selected = collect($access['also'])->pluck('id')->map(fn ($rep) => (int) $rep)->values()->all();
        if ($this->main !== null && $this->main !== $locked && ! in_array($this->main, $this->selected, true)) {
            $this->selected[] = $this->main;
        }
        $this->mode = SetClientAccess::modeFor($this->selected);
        $this->savedSelected = $this->normalised($this->selected);
        $this->savedMain = $this->main;
    }

    /** The rep who added the client (only when that is an active rep: a client the office added has nobody locked). */
    public function lockedId(): ?int
    {
        $id = $this->getRecord()->getKey();

        return SalesInsights::clientAccessList([$id])[$id]['added_by']['id'] ?? null;
    }

    private function guard(): void
    {
        abort_unless(auth()->user()?->canManage('sales-clients'), 403);
        abort_unless(config('sales.private_pool', true), 403);
    }

    public function setMode(string $mode): void
    {
        $this->guard();
        if (! in_array($mode, ['one', 'several'], true)) {
            return;
        }
        $this->mode = $mode;
        $this->apply(SetClientAccess::switchMode($mode, $this->selected, $this->main, $this->lockedId()));
    }

    public function toggleRep(int $repId): void
    {
        $this->guard();
        $this->assertActiveRep($repId);
        $this->apply(SetClientAccess::toggle($this->mode, $this->selected, $this->main, $repId, $this->lockedId()));
    }

    public function makeMain(int $repId): void
    {
        $this->guard();
        $this->assertActiveRep($repId);
        $this->apply(SetClientAccess::makeMain($this->mode, $this->selected, $repId, $this->lockedId()));
    }

    public function cancelChanges(): void
    {
        $this->selected = $this->savedSelected;
        $this->main = $this->savedMain;
        $this->mode = SetClientAccess::modeFor($this->selected);
    }

    /**
     * Picking a client in the list: swap the record in place (no page load). The browser address is updated so a refresh or a shared
     * link opens the same client; the page's own unsaved-changes question is asked in the browser before this is called.
     */
    public function selectClient(int $id): void
    {
        $this->record = $this->resolveRecord($id);
        $this->authorizeAccess();
        $this->fillForm();
        $this->details = false;
        $this->loadAccess();

        $this->js('window.history.replaceState(null, "", ' . json_encode(SalesClientResource::getUrl('edit', ['record' => $this->record]), JSON_UNESCAPED_SLASHES) . ')');
    }

    public function toggleDetails(): void
    {
        $this->details = ! $this->details;
    }

    public function saveAccess(): void
    {
        $this->guard();

        $result = app(SetClientAccess::class)->handle($this->getRecord(), SetClientAccess::seers($this->selected, $this->main, null), $this->main, auth()->user());

        $this->record = $this->getRecord()->refresh();
        $this->loadAccess();

        if (! $result['changed']) {
            Notification::make()->title('Nothing changed')->send();

            return;
        }

        $who = $result['new'] === [] ? 'nobody' : $this->names($result['new']);
        Notification::make()->title($this->getRecord()->name . ' is now seen by ' . $who)->success()
            ->body('Phones update on their next sync. A rep who was taken off no longer sees it.')->send();
    }

    public function isDirty(): bool
    {
        return $this->normalised($this->selected) !== $this->savedSelected || $this->main !== $this->savedMain;
    }

    private function apply(array $state): void
    {
        $this->selected = $state['selected'];
        $this->main = $state['main'];
    }

    private function normalised(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        sort($ids);

        return $ids;
    }

    /** Never trust an id from the browser: only an active rep can be ticked. */
    private function assertActiveRep(int $repId): void
    {
        abort_unless(SalesInsights::activeReps()->contains('id', $repId), 422, 'Only an active rep can be picked.');
    }

    /** "Rahul and Neha", "Rahul, Neha and Ishaan". */
    private function names(array $names): string
    {
        $names = array_values($names);

        return count($names) <= 1 ? ($names[0] ?? '') : implode(', ', array_slice($names, 0, -1)) . ' and ' . end($names);
    }

    // ---- what the view shows (all queries here; the view only formats) --------------------------------------------------------

    /** @return array<string, mixed> */
    public function screen(): array
    {
        $client = $this->getRecord();
        $reps = SalesInsights::activeReps();
        $locked = $this->lockedId();
        $people = $client->contacts()->orderBy('name')->get(['name', 'position']);

        $seers = SetClientAccess::seers($this->selected, $this->main, $locked);
        $seerNames = $reps->whereIn('id', $seers)->pluck('name')->values()->all();
        $peopleCount = $people->count();
        $peopleWord = $peopleCount . ' ' . str('person')->plural($peopleCount);

        $summary = match (true) {
            $seers === [] => 'Nobody will see ' . $client->name . ' except the office.',
            count($seers) === 1 => 'Only ' . $seerNames[0] . ' will see ' . $client->name . ' and its ' . $peopleWord . '.',
            default => count($seers) . ' reps will see ' . $client->name . ' and its ' . $peopleWord . ': ' . $this->names($seerNames) . '.',
        };

        return [
            'client' => $client,
            'reps' => $reps->reject(fn ($r) => $r['id'] === $locked)->values(),
            'locked' => $locked ? $reps->firstWhere('id', $locked) : null,
            'counts' => SalesInsights::repClientCounts(),
            'people' => $people->take(6),
            'peopleCount' => $peopleCount,
            'summary' => $summary,
            'canManage' => (bool) auth()->user()?->canManage('sales-clients'),
            'poolOn' => (bool) config('sales.private_pool', true),
            'dirty' => $this->isDirty(),
            'list' => $this->listRows(),
            'chips' => SalesInsights::clientListCounts(),
            'addedBy' => $client->owner?->name,
        ];
    }

    /** The client list on the left: at most 60 rows, a fixed number of queries. */
    private function listRows(): Collection
    {
        $ids = SalesInsights::clientIdsByChip($this->chip);
        $term = trim($this->search);

        $clients = SalesClient::query()
            ->withCount('contacts')
            ->when($ids !== null, fn ($q) => $q->whereIn('id', $ids ?: [0]))
            ->when($term !== '', fn ($q) => $q->where(function ($w) use ($term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $w->where('name', 'like', $like)->orWhere('city', 'like', $like)
                    ->orWhereIn('uuid', SalesContact::query()->where('name', 'like', $like)->select('client_uuid'));
            }))
            ->orderBy('name')->limit(60)->get(['id', 'uuid', 'name', 'city']);

        $access = SalesInsights::clientAccessList($clients->pluck('id')->all());

        return $clients->map(fn (SalesClient $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'city' => $c->city,
            'people' => $c->contacts_count,
            'seers' => $access[$c->id]['ids'] ?? [],
            'reps' => collect([$access[$c->id]['main'] ?? null])->merge($access[$c->id]['also'] ?? [])->push($access[$c->id]['added_by'] ?? null)->filter()->unique('id')->values(),
            'url' => SalesClientResource::getUrl('edit', ['record' => $c]),
        ]);
    }
}
