<?php

namespace App\Sales\Filament\Resources\SalesMeetings\Pages;

use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use App\Sales\Filament\Resources\SalesEnquiries\SalesEnquiryResource;
use App\Sales\Filament\Resources\SalesMeetings\SalesMeetingResource;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesMeeting;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

/**
 * The visit review (design 7a): the visits on the left, and for the open visit the proof first (selfie, distance from the client,
 * check-in details) and then the visit itself on the right. Read only: the one write is "Use as client location", moved here from the table.
 * Every figure comes from SalesInsights; the view only formats. The table (all columns, filters, export) is the resource's index page.
 */
class ReviewSalesMeetings extends Page
{
    protected static string $resource = SalesMeetingResource::class;

    protected string $view = 'filament.sales.meetings.review';

    public const CHIPS = ['away', 'none', 'not_logged'];

    #[Url(as: 'q')]
    public string $search = '';

    /** @var list<string> the toggled quick chips (they combine) */
    #[Url(as: 'show')]
    public array $chips = [];

    #[Url]
    public ?string $rep = null;

    /** @var list<string> */
    #[Url]
    public array $statuses = [];

    #[Url]
    public ?string $result = null;

    #[Url]
    public ?string $checkin = null;

    #[Url]
    public bool $inRoute = false;

    #[Url]
    public bool $followupDue = false;

    #[Url]
    public ?string $from = null;

    #[Url]
    public ?string $until = null;

    /** The open visit (its uuid). */
    #[Url(as: 'visit')]
    public ?string $visit = null;

    public int $pageNumber = 1;

    public bool $filtersOpen = false;

    public function getTitle(): string
    {
        return 'Meetings';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('listView')->label('List view')->icon(Heroicon::OutlinedTableCells)->color('gray')
                ->url(SalesMeetingResource::getUrl('index')),
            Action::make('export')->label('Export')->icon(Heroicon::OutlinedArrowDownTray)->color('gray')
                ->modalHeading('Export meetings')
                ->modalDescription('Downloads every visit that matches the search, chips and filters you have set now. No selfies are included.')
                ->modalSubmitActionLabel('Download')
                ->fillForm(['format' => 'xlsx'])
                ->schema([Select::make('format')->label('File type')->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])->required()->native(false)])
                ->action(function (array $data) {
                    static::authorizeResourceAccess();

                    return SalesExporter::meetings(SalesInsights::meetingQuery($this->filters()), $data['format']);
                }),
        ];
    }

    // ---- what the manager does -----------------------------------------------------------------------------------------------------

    public function toggleChip(string $chip): void
    {
        static::authorizeResourceAccess();
        if (! in_array($chip, self::CHIPS, true)) {
            return;
        }
        $this->chips = in_array($chip, $this->chips, true) ? array_values(array_diff($this->chips, [$chip])) : [...$this->chips, $chip];
        $this->pageNumber = 1;
    }

    public function clearChips(): void
    {
        $this->chips = [];
        $this->pageNumber = 1;
    }

    public function select(string $uuid): void
    {
        static::authorizeResourceAccess();
        $this->visit = SalesMeeting::where('uuid', $uuid)->exists() ? $uuid : null;
    }

    /** J / K or the arrows: the next (+1) or the previous (-1) visit of the list. */
    public function move(int $step): void
    {
        static::authorizeResourceAccess();
        $uuids = SalesInsights::meetingReviewList($this->filters(), $this->pageNumber)['rows']->pluck('uuid')->all();
        if ($uuids === []) {
            return;
        }
        $at = array_search($this->visit ?: $uuids[0], $uuids, true);
        $this->visit = $uuids[max(0, min(count($uuids) - 1, ($at === false ? 0 : $at) + ($step >= 0 ? 1 : -1)))];
    }

    public function goToPage(int $page): void
    {
        $this->pageNumber = max(1, $page);
    }

    /** Any change of a search or a filter starts again at the first page. */
    public function updated(string $name): void
    {
        if (! in_array($name, ['pageNumber', 'visit', 'filtersOpen'], true)) {
            $this->pageNumber = 1;
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'chips', 'rep', 'statuses', 'result', 'checkin', 'inRoute', 'followupDue', 'from', 'until']);
        $this->pageNumber = 1;
    }

    /**
     * The one write of this screen (it was a table action before): the client has no spot, the rep checked in, the manager looked at the
     * selfie: that spot becomes the client's. The model's saving hook then measures the old check-ins of that client again.
     */
    public function useAsClientSpotAction(): Action
    {
        return Action::make('useAsClientSpot')
            ->label('Use as client location')
            ->icon(Heroicon::OutlinedMapPin)
            ->color('gray')
            ->visible(fn () => $this->canUseSpot($this->openMeeting()))
            ->requiresConfirmation()
            ->modalHeading('Use this check-in as the client\'s location?')
            ->modalDescription('Only do this after you have seen the selfie and are sure the rep was at the client. From then on, check-ins are compared with this spot.')
            ->action(function () {
                abort_unless(auth()->user()?->canManage('sales-clients'), 403);

                $meeting = $this->openMeeting();
                $client = $meeting?->checkin_at && $meeting->client_uuid ? SalesClient::where('uuid', $meeting->client_uuid)->whereNull('lat')->first() : null;
                $client?->forceFill(['lat' => $meeting->checkin_lat, 'lng' => $meeting->checkin_lng, 'location_accuracy_m' => $meeting->checkin_accuracy_m, 'location_at' => now()])->save();

                Notification::make()->title($client ? 'Location saved for ' . $client->name : 'That client already has a location')->success()->send();
            });
    }

    private function openMeeting(): ?SalesMeeting
    {
        $uuid = $this->visit ?: SalesInsights::meetingReviewList($this->filters(), $this->pageNumber)['rows']->first()['uuid'] ?? null;

        return $uuid ? SalesMeeting::where('uuid', $uuid)->first() : null;
    }

    /** The same conditions as the old table action: a check-in, a client that has no spot yet, and the right to manage clients. */
    private function canUseSpot(?SalesMeeting $m): bool
    {
        return $m && $m->checkin_at && $m->client_uuid && auth()->user()?->canManage('sales-clients')
            && SalesClient::where('uuid', $m->client_uuid)->whereNull('lat')->exists();
    }

    // ---- what the view shows ---------------------------------------------------------------------------------------------------------

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'search' => $this->search, 'rep' => $this->rep, 'statuses' => $this->statuses, 'result' => $this->result, 'checkin' => $this->checkin,
            'in_route' => $this->inRoute, 'followup_due' => $this->followupDue, 'from' => $this->from, 'until' => $this->until, 'chips' => $this->chips,
        ];
    }

    /** @return array<string, mixed> */
    public function screen(): array
    {
        $user = auth()->user();
        $can = fn (string $area) => (bool) $user?->canManage($area);
        $list = SalesInsights::meetingReviewList($this->filters(), $this->pageNumber);
        $uuid = $this->visit ?: ($list['rows']->first()['uuid'] ?? null);
        $detail = $uuid ? SalesInsights::meetingReviewDetail($uuid) : null;

        $links = [];
        if ($detail) {
            $m = $detail['m'];
            $digits = $can('sales-reps') && $detail['profile']?->phone ? preg_replace('/\D+/', '', $detail['profile']->phone) : '';
            $repFirst = explode(' ', (string) $m->user?->name)[0];
            $links = [
                'whatsapp' => $digits !== '' ? 'https://wa.me/' . $digits . '?text=' . rawurlencode($m->client_name . ' visit on ' . $detail['when']->format('j M') . ': please write it up') : null,
                'repFirst' => $repFirst,
                'client' => $detail['client'] && $can('sales-clients') ? SalesClientResource::getUrl('edit', ['record' => $detail['client']->id]) : null,
                'enquiries' => $can('sales-enquiries') ? SalesEnquiryResource::getUrl('index', ['tableSearch' => $m->client_name]) : null,
                'selfie' => $m->selfie_path ? route('sales.selfie', $m->uuid) : null,
                'checkinMap' => $m->checkin_lat !== null ? 'https://www.google.com/maps?q=' . $m->checkin_lat . ',' . $m->checkin_lng : null,
                'clientMap' => $detail['client']?->lat !== null && $detail['client'] ? 'https://www.google.com/maps?q=' . $detail['client']->lat . ',' . $detail['client']->lng : null,
                'canSpot' => $this->canUseSpot($m),
            ];
        }

        return [
            'list' => $list,
            'chipCounts' => SalesInsights::meetingChipCounts($this->filters()),
            'detail' => $detail,
            'links' => $links,
            'selected' => $uuid,
            'reps' => \App\Sales\Models\SalesProfile::with('user:id,name')->get()->pluck('user.name', 'user_id')->sort()->all(),
            'tz' => config('sales.timezone'),
            'today' => now(config('sales.timezone'))->startOfDay(),
        ];
    }
}
