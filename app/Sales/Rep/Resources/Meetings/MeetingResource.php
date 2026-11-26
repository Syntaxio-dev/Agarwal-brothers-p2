<?php

namespace App\Sales\Rep\Resources\Meetings;

use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesMeeting;
use App\Sales\Rep\Resources\Meetings\Pages\ListMeetings;
use App\Sales\Rep\Support\DeskMeetings;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\RepDesk;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "My meetings" on the web desk: the rep's own visits and routes (the same records as the app's Meetings tab). From here a rep plans a
 * visit or a route, changes or moves a planned visit, marks it missed or cancels it. Logging a visit as done stays on the phone
 * (the check-in selfie at the place is the proof of visit). Writes go through DeskMeetings.
 */
class MeetingResource extends Resource
{
    protected static ?string $model = SalesMeeting::class;

    protected static ?string $slug = 'meetings';

    protected static ?string $modelLabel = 'meeting';

    protected static ?string $navigationLabel = 'Meetings';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return RepDesk::allows(auth()->user(), DeskMeetings::AREA);
    }

    // the office's policies are written for managers; on the desk the area and the list's own scope decide
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;   // "Plan a visit" is a header action
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id())->with('route');
    }

    public static function isOpen(SalesMeeting $m): bool
    {
        return in_array($m->status ?? 'done', SalesMeeting::OPEN, true);
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('meeting_at')->label('When')->dateTime('D j M Y, h:i A', $tz)->sortable()
                    ->color(fn (SalesMeeting $m) => static::isOpen($m) && $m->meeting_at->lt(now()->subHour()) ? 'danger' : null)
                    ->description(fn (SalesMeeting $m) => $m->moved_from ? 'Moved from ' . $m->moved_from->timezone($tz)->format('j M, h:i A') : null),
                TextColumn::make('client_name')->label('Client')->searchable()->weight('medium')
                    ->description(fn (SalesMeeting $m) => $m->prospect_uuid ? 'Prospect' : null),
                TextColumn::make('purpose')->label('Purpose')->placeholder('—')->limit(40),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (?string $state) => SalesMeeting::STATUSES[$state ?? 'done'] ?? $state)
                    ->color(fn (?string $state) => match ($state ?? 'done') { 'done' => 'success', 'missed' => 'danger', 'moved' => 'warning', default => 'info' }),
                TextColumn::make('route.title')->label('Route')->placeholder('—')->toggleable(),
                TextColumn::make('result')->label('Result')->formatStateUsing(fn (?string $s) => SalesMeeting::RESULTS[$s] ?? null)->placeholder('—')->toggleable(),
            ])
            ->defaultSort('meeting_at')
            ->recordActions([
                ActionGroup::make([
                    static::changeAction(),
                    static::moveAction(),
                    Action::make('missed')->label('Mark missed')->icon(Heroicon::OutlinedXCircle)
                        ->visible(fn (SalesMeeting $record) => static::isOpen($record))
                        ->requiresConfirmation()->modalDescription('The visit did not happen. It stays in your list as Missed.')
                        ->action(fn (SalesMeeting $record) => DeskUi::attempt(fn () => app(DeskMeetings::class)->missed(auth()->user(), $record), 'Marked as missed')),
                    Action::make('cancel')->label('Cancel the visit')->icon(Heroicon::OutlinedTrash)->color('danger')
                        ->visible(fn (SalesMeeting $record) => static::isOpen($record))
                        ->requiresConfirmation()->modalDescription('The planned visit is removed (also from your phone at its next sync).')
                        ->action(fn (SalesMeeting $record) => DeskUi::attempt(fn () => app(DeskMeetings::class)->cancel(auth()->user(), $record), 'Visit cancelled')),
                ])->visible(fn (SalesMeeting $record) => static::isOpen($record)),
            ])
            ->emptyStateHeading('No meetings here')
            ->emptyStateDescription('Plan a visit or a route. Visits are written up as done on the phone, with the check-in selfie.');
    }

    /** The client / prospect picker shared by "Plan a visit" and "Plan a route". */
    public static function partySelect(string $name = 'party'): Select
    {
        return Select::make($name)->label('Client or prospect')->required()->searchable()->native(false)
            ->getSearchResultsUsing(fn (string $search) => DeskMeetings::parties(auth()->user(), $search))
            ->getOptionLabelUsing(fn ($value) => DeskMeetings::partyLabel(auth()->user(), $value))
            ->options(fn () => DeskMeetings::parties(auth()->user()));
    }

    public static function whenPicker(string $name = 'at'): DateTimePicker
    {
        return DateTimePicker::make($name)->label('Day and time')->required()->seconds(false)->native(false)
            ->timezone(config('sales.timezone'))->minDate(now()->startOfDay())->maxDate(now()->addDays(SalesMeeting::PLAN_DAYS));
    }

    public static function peopleSelect(): Select
    {
        return Select::make('contact_uuids')->label('People to meet (optional)')->multiple()->native(false)
            ->options(function (Get $get, ?SalesMeeting $record) {
                $party = (string) ($get('party') ?? ($record?->client_uuid ? 'client:' . $record->client_uuid : ''));
                if (! str_starts_with($party, 'client:')) {
                    return [];
                }

                return SalesContact::query()->visibleTo(auth()->user())->where('client_uuid', substr($party, 7))->orderBy('name')->pluck('name', 'uuid')->all();
            })
            ->visible(fn (Get $get, ?SalesMeeting $record) => str_starts_with((string) ($get('party') ?? ($record?->client_uuid ? 'client:' : '')), 'client:'));
    }

    public static function planVisitAction(): Action
    {
        return Action::make('planVisit')->label('Plan a visit')->icon(Heroicon::OutlinedPlus)
            ->modalHeading('Plan a visit')->modalSubmitActionLabel('Plan it')
            ->schema([
                static::partySelect()->live(),
                static::whenPicker(),
                Select::make('purpose')->label('Purpose')->options(DeskMeetings::PURPOSES)->native(false)->placeholder('Not said'),
                static::peopleSelect(),
            ])
            ->action(fn (array $data) => DeskUi::attempt(fn () => app(DeskMeetings::class)->schedule(auth()->user(), $data), 'Visit planned'));
    }

    public static function planRouteAction(): Action
    {
        return Action::make('planRoute')->label('Plan a route')->icon(Heroicon::OutlinedMap)->color('gray')
            ->modalHeading('Plan a route')
            ->modalDescription('Several visits on one day, in this order. The first starts at the time you pick, the next ones ' . DeskMeetings::ROUTE_MINUTES . ' minutes apart (you can change any time later).')
            ->modalSubmitActionLabel('Plan route')
            ->schema([
                DatePicker::make('date')->label('Day')->required()->native(false)->minDate(now()->startOfDay())->maxDate(now()->addDays(SalesMeeting::PLAN_DAYS)),
                Select::make('start_time')->label('First visit at')->required()->native(false)->default('10:00')
                    ->options(collect(range(7 * 2, 20 * 2))->mapWithKeys(fn (int $half) => [sprintf('%02d:%02d', intdiv($half, 2), ($half % 2) * 30) => date('h:i A', mktime(intdiv($half, 2), ($half % 2) * 30))])->all()),
                TextInput::make('area')->label('Area (optional)')->maxLength(100)->regex('/^[^<>;{}\\\\0-9]*$/u')->placeholder('For example Bagru'),
                Repeater::make('stops')->label('Stops, in order')->minItems(2)->maxItems(DeskMeetings::MAX_STOPS)->defaultItems(2)
                    ->addActionLabel('Add a stop')->reorderable()->simple(static::partySelect()),
            ])
            ->action(function (array $data) {
                $stops = array_values(array_map(fn ($s) => is_array($s) ? ($s['party'] ?? '') : (string) $s, $data['stops'] ?? []));
                DeskUi::attempt(fn () => app(DeskMeetings::class)->planRoute(auth()->user(), (string) $data['date'], (string) $data['start_time'], $stops, $data['area'] ?? null), 'Route planned');
            });
    }

    private static function changeAction(): Action
    {
        return Action::make('change')->label('Change')->icon(Heroicon::OutlinedPencilSquare)
            ->visible(fn (SalesMeeting $record) => static::isOpen($record))
            ->modalHeading(fn (SalesMeeting $record) => 'Change the visit to ' . $record->client_name)
            ->fillForm(fn (SalesMeeting $record) => ['at' => $record->meeting_at, 'purpose' => $record->purpose, 'contact_uuids' => $record->contact_uuids ?? []])
            ->schema([
                static::whenPicker()->minDate(null),
                TextInput::make('purpose')->label('Purpose')->maxLength(200)->regex('/^[^<>;{}\\\\]*$/u')->datalist(array_values(DeskMeetings::PURPOSES)),
                static::peopleSelect(),
            ])
            ->action(fn (SalesMeeting $record, array $data) => DeskUi::attempt(fn () => app(DeskMeetings::class)->update(auth()->user(), $record, $data), 'Visit changed'));
    }

    private static function moveAction(): Action
    {
        return Action::make('move')->label('Move to another time')->icon(Heroicon::OutlinedArrowRightCircle)
            ->visible(fn (SalesMeeting $record) => static::isOpen($record))
            ->modalHeading(fn (SalesMeeting $record) => 'Move the visit to ' . $record->client_name)
            ->modalDescription('It is shown as Moved, with the time it was first planned for.')
            ->schema([static::whenPicker()->label('New day and time')])
            ->action(fn (SalesMeeting $record, array $data) => DeskUi::attempt(fn () => app(DeskMeetings::class)->move(auth()->user(), $record, $data['at']), 'Visit moved'));
    }

    public static function getPages(): array
    {
        return ['index' => ListMeetings::route('/')];
    }
}
