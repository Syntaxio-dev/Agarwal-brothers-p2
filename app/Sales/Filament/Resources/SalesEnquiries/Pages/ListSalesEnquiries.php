<?php

namespace App\Sales\Filament\Resources\SalesEnquiries\Pages;

use App\Sales\Actions\MoveEnquiryStage;
use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource;
use App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\Money;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

/**
 * Enquiries as a pipeline board (the six open stages side by side, the closed ones as a strip) or as the old List (the Filament table, unchanged).
 * The rep owns an enquiry; a manager with the "move" right (policy `moveStage`) may put it in another stage by drag and drop, by "Move to...",
 * or from the keyboard. Every move goes through MoveEnquiryStage: audited, locked, refused when the phone has a newer version, and undoable
 * for ten seconds. All figures come from SalesInsights.
 */
class ListSalesEnquiries extends ListRecords
{
    protected static string $resource = SalesEnquiryResource::class;

    #[Url(as: 'view')]
    public string $viewMode = 'board';

    #[Url(as: 'rep')]
    public ?string $boardRep = null;

    #[Url(as: 'source')]
    public ?string $boardSource = null;

    #[Url(as: 'chip')]
    public string $boardChip = 'all';

    #[Url(as: 'q')]
    public string $boardSearch = '';

    public function getTitle(): string
    {
        return 'Enquiries';
    }

    private function isBoard(): bool
    {
        return $this->viewMode !== 'list';
    }

    /** The old table keeps its own header (and gets a way back to the board); the board draws its own. */
    protected function getHeaderActions(): array
    {
        return $this->isBoard() ? [] : [Action::make('showBoard')->label('Board view')->icon('heroicon-o-view-columns')->color('gray')->action(fn () => $this->viewMode = 'board')];
    }

    public function content(Schema $schema): Schema
    {
        if (! $this->isBoard()) {
            return parent::content($schema);
        }

        return $schema->components([View::make('filament.sales.enquiries.board')->viewData(fn () => $this->boardData())]);
    }

    // ---- who may do what ---------------------------------------------------------------------------------------------

    /** May this user put an enquiry in another stage? (the policy: the area AND its own "move" right) */
    private function canMove(?SalesEnquiry $enquiry = null): bool
    {
        return (bool) auth()->user()?->can('moveStage', $enquiry ?? new SalesEnquiry);
    }

    private function filters(): array
    {
        return [
            'rep' => ctype_digit((string) $this->boardRep) && (int) $this->boardRep > 0 ? (int) $this->boardRep : null,
            'source' => array_key_exists((string) $this->boardSource, SalesEnquiry::SOURCES) ? $this->boardSource : null,
            'q' => mb_substr(trim($this->boardSearch), 0, 100),
        ];
    }

    private function chip(): string
    {
        return in_array($this->boardChip, ['all', 'overdue', 'today', 'idle'], true) ? $this->boardChip : 'all';
    }

    // ---- small actions -----------------------------------------------------------------------------------------------

    public function setMode(string $mode): void
    {
        static::authorizeResourceAccess();

        $this->viewMode = $mode === 'list' ? 'list' : 'board';
    }

    public function setChip(string $chip): void
    {
        static::authorizeResourceAccess();

        $this->boardChip = in_array($chip, ['all', 'overdue', 'today', 'idle'], true) ? $chip : 'all';
    }

    /** "+N more" under a column: the List, filtered to that stage. */
    public function showStage(string $stage): void
    {
        static::authorizeResourceAccess();

        if (array_key_exists($stage, SalesEnquiry::STATUSES)) {
            $this->viewMode = 'list';
            $this->tableFilters = ['status' => ['values' => [$stage]]];
        }
    }

    // ---- moving an enquiry ---------------------------------------------------------------------------------------------

    /**
     * A card was dropped. The key of a card is `uuid|version|from` (the version and the stage the board loaded it with), so the server can tell
     * when the enquiry changed in between. Nothing the browser sends is trusted: the stage is validated, the enquiry looked up, the right checked.
     */
    public function dropEnquiry(string $item, string $to, int $position = 0): void
    {
        static::authorizeResourceAccess();

        [$uuid, $version, $from] = array_pad(explode('|', $item, 3), 3, null);

        $this->moveEnquiry((string) $uuid, $to, (string) $from, (string) $version);
    }

    public function moveEnquiry(string $uuid, string $to, string $from, string $version, ?string $note = null, bool $confirmed = false): void
    {
        static::authorizeResourceAccess();

        $enquiry = Str::isUuid($uuid) ? SalesEnquiry::where('uuid', $uuid)->first() : null;
        abort_unless($enquiry, 404);
        abort_unless($this->canMove($enquiry), 403);

        if (! array_key_exists($to, SalesEnquiry::STATUSES)) {
            Notification::make()->title('That stage does not exist')->warning()->send();

            return;
        }

        // closing an enquiry asks first (it also clears the follow-up)
        if (in_array($to, SalesEnquiry::CLOSED, true) && $enquiry->isOpen() && ! $confirmed) {
            $this->mountAction('confirmClose', ['uuid' => $uuid, 'to' => $to, 'from' => $from, 'version' => $version]);

            return;
        }

        try {
            $expected = CarbonImmutable::parse($version, 'UTC');
        } catch (\Throwable) {
            $expected = CarbonImmutable::parse('1970-01-01', 'UTC');   // not a version we handed out: it can only conflict
        }

        $result = app(MoveEnquiryStage::class)->handle($enquiry, $to, auth()->user(), $note, $expected, $from);
        $this->report($enquiry, $to, $result);
    }

    private function report(SalesEnquiry $enquiry, string $to, array $result): void
    {
        $rep = $enquiry->user?->name ?? 'The rep';

        match ($result['result']) {
            'moved' => $this->movedToast($enquiry, $to, $result['undo']),
            'conflict' => Notification::make()->title($rep . ' just updated this enquiry on their phone. Their change is newer, so the card shows their version.')->warning()->send(),
            'closed' => Notification::make()->title('A closed enquiry stays closed here: reopening is the rep\'s decision, on the phone.')->warning()->send(),
            'gone' => Notification::make()->title('That enquiry is no longer there')->warning()->send(),
            default => null,   // same stage: nothing to do
        };
    }

    private function movedToast(SalesEnquiry $enquiry, string $to, array $undo): void
    {
        $token = Str::random(40);
        session()->put('enquiry_undo.' . $token, ['undo' => $undo, 'user' => auth()->id(), 'at' => now()->getTimestamp()]);

        Notification::make()->title($enquiry->client_name . ' moved to ' . SalesEnquiry::STATUSES[$to])->success()->duration(10000)
            ->actions([Action::make('undo')->label('Undo')->button()->dispatch('undo-enquiry-move', ['token' => $token])->close()])->send();
    }

    /** "Undo" on the toast: the token points at what the move wrote (kept in this manager's session); nothing else is taken from the browser. */
    #[On('undo-enquiry-move')]
    public function undoMove(string $token): void
    {
        static::authorizeResourceAccess();

        $saved = session()->pull('enquiry_undo.' . $token);

        if (! $saved || (int) $saved['user'] !== (int) auth()->id() || now()->getTimestamp() - (int) $saved['at'] > 120) {
            Notification::make()->title('Nothing to undo any more')->warning()->send();

            return;
        }

        abort_unless($this->canMove(), 403);

        $result = app(MoveEnquiryStage::class)->undo($saved['undo'], auth()->user());

        Notification::make()->title(match ($result) {
            'undone' => 'Move undone',
            'gone' => 'That enquiry is no longer there',
            default => 'It changed since. Nothing was undone.',
        })->{$result === 'undone' ? 'success' : 'warning'}()->send();
    }

    /** The question before an enquiry is closed (Order received or Lost). */
    public function confirmCloseAction(): Action
    {
        $find = fn (array $arguments) => SalesEnquiry::where('uuid', (string) ($arguments['uuid'] ?? ''))->first();

        return Action::make('confirmClose')
            ->modalHeading(fn (array $arguments) => 'Mark ' . ($find($arguments)?->client_name ?? 'this enquiry') . ' as ' . (SalesEnquiry::STATUSES[$arguments['to'] ?? ''] ?? '') . '?')
            ->modalDescription('This closes the enquiry and clears its follow-up. The rep sees it on the phone after the next sync, and only the rep can reopen it.')
            ->modalSubmitActionLabel('Close the enquiry')
            ->schema([Textarea::make('note')->label('Note (optional)')->maxLength(300)->rows(2)])
            ->action(function (array $data, array $arguments) {
                $this->moveEnquiry((string) ($arguments['uuid'] ?? ''), (string) ($arguments['to'] ?? ''), (string) ($arguments['from'] ?? ''), (string) ($arguments['version'] ?? ''), $data['note'] ?? null, true);
            });
    }

    // ---- the side panel ------------------------------------------------------------------------------------------------

    public function openEnquiry(string $uuid): void
    {
        static::authorizeResourceAccess();

        abort_unless(Str::isUuid($uuid) && SalesEnquiry::where('uuid', $uuid)->exists(), 404);

        $this->mountAction('openEnquiry', ['uuid' => $uuid]);
    }

    /** J / K in the panel: the next / previous enquiry of the same column. */
    public function openNeighbour(string $uuid, int $step): void
    {
        static::authorizeResourceAccess();

        $enquiry = SalesEnquiry::where('uuid', $uuid)->first();
        abort_unless($enquiry, 404);

        $column = SalesInsights::pipelineBoard($this->filters(), $this->chip(), 100000)['columns'][$enquiry->status]['cards'] ?? [];
        $at = collect($column)->search(fn (array $c) => $c['uuid'] === $uuid);
        $next = $at === false ? null : ($column[$at + ($step >= 0 ? 1 : -1)]['uuid'] ?? null);

        $next && $this->replaceMountedAction('openEnquiry', ['uuid' => $next]);
    }

    public function openEnquiryAction(): Action
    {
        return Action::make('openEnquiry')
            ->slideOver()
            ->modalWidth('lg')
            ->modalHeading(fn (array $arguments) => SalesEnquiry::where('uuid', (string) ($arguments['uuid'] ?? ''))->value('client_name') ?? 'Enquiry')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->closeModalByClickingAway()
            ->closeModalByEscaping()
            ->modalContent(function (array $arguments) {
                static::authorizeResourceAccess();

                $enquiry = SalesEnquiry::with('user')->where('uuid', (string) ($arguments['uuid'] ?? ''))->firstOrFail();

                return view('filament.sales.enquiries.panel', $this->panelData($enquiry));
            });
    }

    private function panelData(SalesEnquiry $e): array
    {
        $user = auth()->user();
        $can = fn (string $area) => (bool) $user?->canManage($area);
        $urgency = SalesInsights::enquiryUrgency($e);
        $tz = config('sales.timezone');
        $contact = $e->contact_uuid ? SalesContact::where('uuid', $e->contact_uuid)->first() : null;
        $client = $e->client_uuid ? SalesClient::where('uuid', $e->client_uuid)->first() : null;
        $profile = SalesProfile::where('user_id', $e->user_id)->first();
        $repDigits = $can('sales-reps') && $profile?->phone ? preg_replace('/\D+/', '', $profile->phone) : null;

        return [
            'e' => $e,
            'urgency' => $urgency,
            'timeline' => SalesInsights::enquiryTimeline($e),
            'followup' => $e->next_followup_at ? CarbonImmutable::instance($e->next_followup_at)->timezone($tz) : null,
            'opened' => CarbonImmutable::instance($e->opened_at)->timezone($tz),
            'items' => collect($e->items ?? []),
            'canMove' => $this->canMove($e) && $e->isOpen(),
            'stages' => SalesEnquiry::STATUSES,
            'version' => $e->client_updated_at?->format('Y-m-d H:i:s.v'),
            'whatsapp' => $repDigits ? 'https://wa.me/' . $repDigits . '?text=' . rawurlencode($e->client_name . ' follow-up' . ($urgency['key'] === 'overdue' ? ' is ' . strtolower($urgency['label']) : '')) : null,
            'callPhone' => $contact?->phone,
            'callText' => $contact ? $contact->name . ' · ' . PhoneNumber::display($contact->phone) : null,
            'clientUrl' => $client && $can('sales-clients') ? SalesClientResource::getUrl('edit', ['record' => $client->id]) : null,
            'meetingsUrl' => $can('sales-meetings') ? SalesMeetingResource::getUrl('index', ['tableSearch' => $e->client_name]) : null,
            'money' => fn (int $paise) => Money::rupees($paise, false),
            'short' => fn (int $paise) => Money::short($paise),
        ];
    }

    // ---- export (the board's filters) --------------------------------------------------------------------------------------

    public function exportBoardAction(): Action
    {
        return Action::make('exportBoard')
            ->label('Export')
            ->color('gray')
            ->modalHeading('Export enquiries')
            ->modalDescription('Downloads every enquiry that matches the filters and search you have set now.')
            ->modalSubmitActionLabel('Download')
            ->fillForm(['format' => 'xlsx'])
            ->schema([Select::make('format')->label('File type')->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])->required()->native(false)])
            ->action(function (array $data) {
                static::authorizeResourceAccess();

                return SalesExporter::enquiries(SalesInsights::enquiryQuery($this->filters()), $data['format']);
            });
    }

    // ---- the board's data --------------------------------------------------------------------------------------------------

    private function boardData(): array
    {
        $user = auth()->user();
        $filters = $this->filters();
        $board = SalesInsights::pipelineBoard($filters, $this->chip());
        $canMove = $this->canMove();

        return [
            'board' => $board,
            'closed' => SalesInsights::closedSummary($filters),
            'canMove' => $canMove,
            'chip' => $this->chip(),
            'mode' => 'board',
            'filters' => $filters,
            'reps' => SalesProfile::with('user')->get()->mapWithKeys(fn (SalesProfile $p) => [$p->user_id => $p->user->name])->sort()->all(),
            'sources' => SalesEnquiry::SOURCES,
            'stages' => SalesEnquiry::STATUSES,
            'open' => SalesEnquiry::OPEN,
            'short' => fn (int $paise) => Money::short($paise),
            'lateText' => fn (array $u) => $u['label'],
            'canSeeReps' => (bool) $user?->canManage('sales-reps'),
        ];
    }
}
