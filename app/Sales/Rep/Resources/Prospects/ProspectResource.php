<?php

namespace App\Sales\Rep\Resources\Prospects;

use App\Sales\Models\SalesPosition;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesProspectNote;
use App\Sales\Models\SalesProspectPerson;
use App\Sales\Rep\Resources\Prospects\Pages\CreateProspect;
use App\Sales\Rep\Resources\Prospects\Pages\EditProspect;
use App\Sales\Rep\Resources\Prospects\Pages\ListProspects;
use App\Sales\Rep\Resources\Prospects\Pages\ViewProspect;
use App\Sales\Rep\Support\DeskMeetings;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\RepDesk;
use App\Sales\Rep\Support\RepTerritories;
use App\Sales\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Prospects on the web desk: the team's one pool of companies that do not work with us yet (the same records as the app's Prospects).
 * Every rep reads all of them, adds new ones with their contact persons and logs calls; only the rep who added a prospect changes or
 * deletes it. Territories work like in the app: the rep keeps "My territories" (capsules above the list) and picks a territory from
 * capsules in the form (one spelling per team). Writes go through DeskProspects (the phone's rules); nothing can be exported from here.
 */
class ProspectResource extends Resource
{
    protected static ?string $model = SalesProspect::class;

    protected static ?string $slug = 'prospects';

    protected static ?string $modelLabel = 'prospect';

    protected static ?string $navigationLabel = 'Prospects';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 4;

    /** A person's name: a letter first, then letters, spaces and . ' - (the app's PERSON_NAME). */
    public const NAME_RULE = "/^\\p{L}[\\p{L}\\p{M} .'-]*$/u";

    /** A company, an address or a note: no < > ; { } or backslash (as in the app). */
    public const TEXT_RULE = '/^[^<>;{}\\\\]*$/u';

    /** Outcomes after which the company is still worth calling (the app's "open" outcomes). */
    public const OPEN_OUTCOMES = ['no_answer', 'call_back', 'interested', 'meeting_fixed'];

    public const NEW_TERRITORY = '__new';

    public static function canAccess(): bool
    {
        return RepDesk::allows(auth()->user(), DeskProspects::AREA);
    }

    // the office's policies are written for managers; on the desk the area and the list's own scope decide
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView(Model $record): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function mine(Model $record): bool
    {
        return (int) $record->user_id === (int) auth()->id();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canAccess() && static::mine($record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::canAccess() && static::mine($record);
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    // ---- the newest call and the first person, in the same query ----------------------------------------------------------------

    private static function lastCall(string $column): string
    {
        return "(select n.$column from sales_prospect_notes n where n.prospect_uuid = sales_prospects.uuid order by n.noted_at desc, n.id desc limit 1)";
    }

    private static function firstPerson(string $column): string
    {
        return "(select pp.$column from sales_prospect_people pp where pp.prospect_id = sales_prospects.id order by pp.id limit 1)";
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['addedBy'])->withCount('people')
            ->select('sales_prospects.*')
            ->selectRaw(self::lastCall('outcome') . ' as last_outcome')
            ->selectRaw(self::lastCall('noted_at') . ' as last_called_at')
            ->selectRaw(self::lastCall('next_followup_at') . ' as last_followup_at')
            ->selectRaw(self::firstPerson('name') . ' as first_person_name')
            ->selectRaw(self::firstPerson('phone') . ' as first_person_phone');
    }

    /** The app's order: follow-up due first, then not called yet, then being worked, closed ones last; A to Z inside. */
    public static function appOrder(Builder $query): Builder
    {
        $open = "'" . implode("','", self::OPEN_OUTCOMES) . "'";
        $outcome = self::lastCall('outcome');
        $followup = self::lastCall('next_followup_at');

        return $query->orderByRaw(
            "CASE WHEN $outcome IN ($open) AND $followup IS NOT NULL AND $followup <= ? THEN 0 WHEN $outcome IS NULL THEN 1 WHEN $outcome IN ($open) THEN 2 ELSE 3 END",
            [now()->utc()->format('Y-m-d H:i:s')],
        )->orderBy('sales_prospects.name');
    }

    // ---- the form ---------------------------------------------------------------------------------------------------------------

    /** @return array<string,string> the territory capsules; the prospect's own territory is always one of them */
    public static function territoryOptions(?SalesProspect $record): array
    {
        $picks = RepTerritories::picks(auth()->user());
        if ($record) {
            $key = SalesProspect::keyOf($record->territory);
            $picks = [$record->territory => $record->territory] + array_filter($picks, fn ($name) => SalesProspect::keyOf($name) !== $key);
        }

        return $picks + [self::NEW_TERRITORY => '+ New territory'];
    }

    /** The form's territory: a capsule, or the typed new one. */
    public static function chosenTerritory(array $data): string
    {
        return ($data['territory'] ?? '') === self::NEW_TERRITORY ? (string) ($data['new_territory'] ?? '') : (string) ($data['territory'] ?? '');
    }

    /** @return list<\Filament\Forms\Components\Field> the boxes of one contact person (form repeater and the person pop-up) */
    public static function personFields(): array
    {
        return [
            TextInput::make('name')->label('Name')->required()->minLength(2)->maxLength(150)->regex(self::NAME_RULE)
                ->validationMessages(['regex' => "A name uses letters, spaces and . ' - only."]),
            TextInput::make('position')->label('Position')->maxLength(100)->regex(self::TEXT_RULE)->datalist(SalesPosition::names())
                ->placeholder('Purchase head, Lab in-charge ...'),
            TextInput::make('phone')->label('Phone')->tel()->maxLength(20)->regex('/^\+?[0-9 ()-]{6,20}$/')
                ->helperText('10 digits, or with the country code.')
                ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value) && PhoneNumber::normalize((string) $value) === null) {
                        $fail('This phone number is not valid.');
                    }
                }),
            TextInput::make('email')->label('E-mail')->email()->maxLength(150),
            TextInput::make('linkedin')->label('LinkedIn')->maxLength(300)->placeholder('https://www.linkedin.com/in/...')
                ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value) && SalesProspectPerson::cleanLinkedin((string) $value) === null) {
                        $fail('Only a linkedin.com address.');
                    }
                }),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Company')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->label('Company name')->required()->minLength(2)->maxLength(150)->regex(self::TEXT_RULE)
                    ->placeholder('For example Shree Dyes Pvt Ltd')->columnSpanFull(),
                ToggleButtons::make('territory')->label('Territory')->required()->inline()->live()->columnSpanFull()
                    ->options(fn (?SalesProspect $record) => static::territoryOptions($record))
                    ->colors(fn (?SalesProspect $record) => array_fill_keys(array_keys(static::territoryOptions($record)), 'info'))
                    ->default(function () {
                        $asked = (string) request()->query('territory', '');

                        return array_key_exists($asked, RepTerritories::picks(auth()->user())) ? $asked : null;
                    })
                    ->helperText('Pick one. Your own territories come first; the whole team uses the same spelling.'),
                TextInput::make('new_territory')->label('New territory')->maxLength(100)->columnSpanFull()
                    ->placeholder('For example Behror')
                    ->visible(fn (Get $get) => $get('territory') === self::NEW_TERRITORY)
                    ->required(fn (Get $get) => $get('territory') === self::NEW_TERRITORY)
                    ->regex(RepTerritories::PATTERN)
                    ->validationMessages(['regex' => "A territory uses letters, spaces and . , ' - only."]),
                TextInput::make('address')->label('Address or landmark (optional)')->maxLength(200)->regex(self::TEXT_RULE)
                    ->placeholder('Plot, road, industrial area')->columnSpanFull(),
                ToggleButtons::make('source')->label('Where did you find it? (optional)')->inline()->columnSpanFull()
                    ->options(SalesProspect::SOURCES)->colors(array_fill_keys(array_keys(SalesProspect::SOURCES), 'info')),
                Textarea::make('notes')->label('Notes (optional)')->rows(3)->maxLength(1000)->columnSpanFull(),
            ]),
            Section::make('Contact persons')->columnSpanFull()
                ->description('The people you can call there. At most ' . DeskProspects::MAX_PEOPLE . '.')
                ->schema([
                    Repeater::make('people')->hiddenLabel()->maxItems(DeskProspects::MAX_PEOPLE)->defaultItems(0)
                        ->addActionLabel('Add contact person')->reorderable(false)->collapsible()
                        ->itemLabel(fn (array $state) => filled($state['name'] ?? null) ? $state['name'] : 'New person')
                        ->schema(static::personFields())->columns(2),
                ]),
        ]);
    }

    // ---- the list -----------------------------------------------------------------------------------------------------------------

    public static function statusColor(?string $outcome): string
    {
        return match (true) {
            $outcome === null => 'gray',
            in_array($outcome, ['interested', 'meeting_fixed', 'converted'], true) => 'success',
            in_array($outcome, self::OPEN_OUTCOMES, true) => 'info',
            default => 'danger',
        };
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('name')->label('Company')->searchable()->sortable()->weight('medium')
                    ->description(fn (SalesProspect $p) => $p->address ?: null),
                TextColumn::make('territory')->label('Territory')->badge()->color('info')->searchable()->sortable(),
                TextColumn::make('first_person_name')->label('Contact')->placeholder('No contact yet')
                    ->description(fn (SalesProspect $p) => trim(PhoneNumber::display($p->first_person_phone) . ($p->people_count > 1 ? '  +' . ($p->people_count - 1) . ' more' : ''))  ?: null)
                    ->searchable(query: fn (Builder $query, string $search) => $query->orWhereHas('people', fn (Builder $q) => $q
                        ->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%')
                        ->orWhere('phone', 'like', '%' . addcslashes(preg_replace('/\D+/', '', $search) ?: $search, '%_\\') . '%'))),
                TextColumn::make('last_outcome')->label('Status')->badge()
                    ->getStateUsing(fn (SalesProspect $p) => SalesProspectNote::labelOf($p->last_outcome))
                    ->color(fn (SalesProspect $p) => static::statusColor($p->last_outcome))
                    ->description(fn (SalesProspect $p) => $p->last_called_at ? 'Called ' . CarbonImmutable::parse($p->last_called_at, 'UTC')->timezone($tz)->format('j M') : null),
                TextColumn::make('last_followup_at')->label('Next follow-up')->placeholder('—')
                    ->formatStateUsing(fn ($state) => $state ? CarbonImmutable::parse($state, 'UTC')->timezone($tz)->format('D j M, h:i A') : null)
                    ->color(fn (SalesProspect $p) => $p->last_followup_at && in_array($p->last_outcome, self::OPEN_OUTCOMES, true) && CarbonImmutable::parse($p->last_followup_at, 'UTC')->isPast() ? 'danger' : null),
                TextColumn::make('added')->label('Added by')
                    ->getStateUsing(fn (SalesProspect $p) => static::mine($p) ? 'You' : ($p->addedBy?->name ?? 'Office')),
            ])
            ->defaultSort(fn (Builder $query) => static::appOrder($query))
            ->filters([
                Filter::make('mine')->label('Added by me')->query(fn (Builder $query) => $query->where('sales_prospects.user_id', auth()->id())),
                Filter::make('due')->label('Follow-up due')->query(fn (Builder $query) => $query->whereRaw(
                    self::lastCall('next_followup_at') . ' <= ? AND ' . self::lastCall('outcome') . " IN ('" . implode("','", self::OPEN_OUTCOMES) . "')",
                    [now()->utc()->format('Y-m-d H:i:s')],
                )),
                Filter::make('uncalled')->label('Not called yet')->query(fn (Builder $query) => $query->whereRaw(self::lastCall('outcome') . ' IS NULL')),
            ])
            ->searchPlaceholder('Company, person, number, territory')
            ->recordUrl(fn (SalesProspect $p) => static::getUrl('view', ['record' => $p]))
            ->recordActions([static::logCallAction()])
            ->emptyStateHeading(fn ($livewire) => $livewire->activeTab === 'mine' && RepTerritories::mine(auth()->user()) === [] ? 'Choose your territories' : 'No prospects here')
            ->emptyStateDescription(fn ($livewire) => $livewire->activeTab === 'mine' && RepTerritories::mine(auth()->user()) === []
                ? 'Pick the areas you work in, for example Bagru or Behror, with "My territories". Your list then shows the companies there.'
                : 'Add a company that does not work with us yet, with the people you can call there. Prospects added on a phone appear here after its sync.');
    }

    // ---- actions shared by the list and the prospect page ----------------------------------------------------------------------

    /** Log one call (any rep, also on somebody else's prospect). The outcome and the next follow-up are capsules, like in the app. */
    public static function logCallAction(): Action
    {
        return Action::make('logCall')->label('Log a call')->icon(Heroicon::OutlinedPhone)->color('gray')
            ->modalHeading(fn (SalesProspect $record) => 'Call to ' . $record->name)
            ->modalSubmitActionLabel('Save the call')
            ->schema([
                ToggleButtons::make('outcome')->label('How did it go?')->required()->inline()->live()
                    ->options(array_map(fn (array $o) => $o[0], array_diff_key(SalesProspectNote::OUTCOMES, ['converted' => 1])))
                    ->colors(fn () => collect(SalesProspectNote::OUTCOMES)->map(fn ($o, $k) => static::statusColor($k))->all()),
                Textarea::make('remark')->label('What did they say? (optional)')->rows(2)->maxLength(300),
                ToggleButtons::make('when')->label('Next follow-up')->inline()->live()->default('none')
                    ->visible(fn (Get $get) => in_array($get('outcome'), self::OPEN_OUTCOMES, true))
                    ->options(['tomorrow' => 'Tomorrow 10 am', 'three' => 'In 3 days', 'week' => 'Next week', 'pick' => 'Pick a time', 'none' => 'No follow-up'])
                    ->colors(['tomorrow' => 'info', 'three' => 'info', 'week' => 'info', 'pick' => 'info', 'none' => 'gray']),
                DateTimePicker::make('next_followup_at')->label('Follow-up on')->seconds(false)->native(false)
                    ->timezone(config('sales.timezone'))->minDate(now()->startOfDay())->maxDate(now()->addYears(2))
                    ->visible(fn (Get $get) => in_array($get('outcome'), self::OPEN_OUTCOMES, true) && $get('when') === 'pick')
                    ->required(fn (Get $get) => in_array($get('outcome'), self::OPEN_OUTCOMES, true) && $get('when') === 'pick'),
            ])
            ->action(fn (SalesProspect $record, array $data) => DeskUi::attempt(
                fn () => app(DeskProspects::class)->logCall(auth()->user(), $record, $data['outcome'], $data['remark'] ?? null, static::followupFrom($data)),
                'Call saved',
            ));
    }

    /** The follow-up capsule as a time in the business time zone ("Tomorrow 10 am" = tomorrow at 10:00 there). */
    public static function followupFrom(array $data): ?string
    {
        if (! in_array($data['outcome'] ?? null, self::OPEN_OUTCOMES, true)) {
            return null;
        }

        $at10 = fn (int $days) => CarbonImmutable::now(config('sales.timezone'))->startOfDay()->addDays($days)->setTime(10, 0)->format('Y-m-d H:i:s');

        return match ($data['when'] ?? 'none') {
            'tomorrow' => $at10(1),
            'three' => $at10(3),
            'week' => $at10(7),
            'pick' => filled($data['next_followup_at'] ?? null) ? CarbonImmutable::parse($data['next_followup_at'], config('sales.timezone'))->format('Y-m-d H:i:s') : null,
            default => null,
        };
    }

    /** Plan a visit to this prospect (only when Meeting plans is open too). */
    public static function planVisitAction(): Action
    {
        return Action::make('planVisit')->label('Plan a visit')->icon(Heroicon::OutlinedCalendarDays)->color('gray')
            ->visible(fn () => RepDesk::allows(auth()->user(), DeskMeetings::AREA))
            ->modalHeading(fn (SalesProspect $record) => 'Visit ' . $record->name)
            ->modalSubmitActionLabel('Plan it')
            ->schema([
                DateTimePicker::make('at')->label('Day and time')->required()->seconds(false)->native(false)->timezone(config('sales.timezone'))
                    ->minDate(now()->startOfDay())->maxDate(now()->addDays(90)),
                ToggleButtons::make('purpose')->label('Purpose (optional)')->inline()->options(DeskMeetings::PURPOSES)->colors(array_fill_keys(array_keys(DeskMeetings::PURPOSES), 'info')),
            ])
            ->action(fn (SalesProspect $record, array $data) => DeskUi::attempt(
                fn () => app(DeskMeetings::class)->schedule(auth()->user(), ['party' => 'prospect:' . $record->uuid] + $data),
                'Visit planned',
            ));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProspects::route('/'),
            'create' => CreateProspect::route('/create'),
            'view' => ViewProspect::route('/{record}'),
            'edit' => EditProspect::route('/{record}/edit'),
        ];
    }
}
