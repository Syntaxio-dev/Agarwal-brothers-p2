<?php

namespace App\Sales\Rep\Resources\Prospects;

use App\Sales\Models\SalesPosition;
use App\Sales\Models\SalesProspect;
use App\Sales\Models\SalesProspectNote;
use App\Sales\Rep\Resources\Prospects\Pages\CreateProspect;
use App\Sales\Rep\Resources\Prospects\Pages\EditProspect;
use App\Sales\Rep\Resources\Prospects\Pages\ListProspects;
use App\Sales\Rep\Resources\Prospects\Pages\ViewProspect;
use App\Sales\Rep\Support\DeskMeetings;
use App\Sales\Rep\Support\DeskProspects;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\RepDesk;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Prospects on the web desk: the team's one pool of companies that do not work with us yet (the same records as the app's Prospects).
 * Every rep reads all of them, adds new ones with their contact persons and logs calls; only the rep who added a prospect changes or
 * deletes it. Writes go through DeskProspects (the phone's rules); nothing can be exported from here.
 */
class ProspectResource extends Resource
{
    protected static ?string $model = SalesProspect::class;

    protected static ?string $slug = 'prospects';

    protected static ?string $modelLabel = 'prospect';

    protected static ?string $navigationLabel = 'Prospects';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 4;

    /** Letters of a person's name: no digits and nothing that looks like code. */
    public const NAME_RULE = '/^[^0-9<>;{}\\\\]+$/u';

    /** A company, a place or a note: no < > ; (as in the app). */
    public const TEXT_RULE = '/^[^<>;{}\\\\]*$/u';

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

    /** The newest call of each prospect, in the same query (status, next follow-up, when). */
    public static function getEloquentQuery(): Builder
    {
        $last = fn (string $column) => SalesProspectNote::query()->select($column)->whereColumn('prospect_uuid', 'sales_prospects.uuid')
            ->orderByDesc('noted_at')->orderByDesc('id')->limit(1);

        return parent::getEloquentQuery()->with(['addedBy'])->withCount('people')->addSelect([
            'last_outcome' => $last('outcome'),
            'last_called_at' => $last('noted_at'),
            'last_followup_at' => $last('next_followup_at'),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        $territories = DeskProspects::territories();

        return $schema->components([
            Section::make('Company')->columns(2)->columnSpanFull()->schema([
                TextInput::make('name')->label('Company name')->required()->minLength(2)->maxLength(150)->regex(self::TEXT_RULE),
                TextInput::make('territory')->label('Territory')->required()->minLength(2)->maxLength(100)->regex(self::TEXT_RULE)
                    ->datalist(array_values($territories))
                    ->helperText('For example Bagru or Behror. Pick the team\'s spelling when it is offered.'),
                TextInput::make('address')->label('Address')->maxLength(200)->regex(self::TEXT_RULE),
                Select::make('source')->label('Found through')->options(SalesProspect::SOURCES)->native(false)->placeholder('Not said'),
                Textarea::make('notes')->label('Notes')->rows(3)->maxLength(1000)->columnSpanFull(),
            ]),
            Repeater::make('people')->label('Contact persons')->columnSpanFull()->maxItems(DeskProspects::MAX_PEOPLE)->defaultItems(0)
                ->addActionLabel('Add contact person')->reorderable(false)->collapsible()
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->schema([
                    TextInput::make('name')->label('Name')->required()->minLength(2)->maxLength(150)->regex(self::NAME_RULE)
                        ->validationMessages(['regex' => 'A name has no digits or symbols like < > ;']),
                    TextInput::make('position')->label('Position')->maxLength(100)->regex(self::TEXT_RULE)->datalist(SalesPosition::names()),
                    TextInput::make('phone')->label('Phone')->tel()->maxLength(20)->regex('/^\+?[0-9 ()-]{6,20}$/')
                        ->helperText('10 digits, or with the country code.'),
                    TextInput::make('email')->label('E-mail')->email()->maxLength(150),
                    TextInput::make('linkedin')->label('LinkedIn')->maxLength(300)->placeholder('https://www.linkedin.com/in/...')
                        ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                            if (filled($value) && \App\Sales\Models\SalesProspectPerson::cleanLinkedin((string) $value) === null) {
                                $fail('Only a linkedin.com address.');
                            }
                        }),
                ])->columns(5),
        ]);
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('name')->label('Company')->searchable()->sortable()->weight('medium')->description(fn (SalesProspect $p) => $p->address),
                TextColumn::make('territory')->label('Territory')->searchable()->sortable(),
                TextColumn::make('people_count')->label('People')->alignEnd(),
                TextColumn::make('last_outcome')->label('Status')->badge()
                    ->getStateUsing(fn (SalesProspect $p) => SalesProspectNote::labelOf($p->last_outcome))
                    ->color(fn (SalesProspect $p) => match (true) {
                        $p->last_outcome === null => 'gray',
                        in_array($p->last_outcome, ['interested', 'meeting_fixed', 'converted'], true) => 'success',
                        (bool) (SalesProspectNote::OUTCOMES[$p->last_outcome][1] ?? false) => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('last_followup_at')->label('Next follow-up')->dateTime('d M Y, h:i A', $tz)->placeholder('—')->sortable()
                    ->color(fn (SalesProspect $p) => $p->last_followup_at && \Carbon\Carbon::parse($p->last_followup_at)->isPast() ? 'danger' : null),
                TextColumn::make('added')->label('Added by')
                    ->getStateUsing(fn (SalesProspect $p) => static::mine($p) ? 'You' : ($p->addedBy?->name ?? 'Office')),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('territory')->label('Territory')->options(fn () => DeskProspects::territories())->multiple()->searchable(),
                Filter::make('mine')->label('Added by me')->query(fn (Builder $query) => $query->where('user_id', auth()->id())),
                Filter::make('due')->label('Follow-up due')->query(fn (Builder $query) => $query->whereIn('uuid', SalesProspectNote::query()
                    ->select('prospect_uuid')->whereNotNull('next_followup_at')->where('next_followup_at', '<=', now()))),
            ])
            ->recordUrl(fn (SalesProspect $p) => static::getUrl('view', ['record' => $p]))
            ->recordActions([static::logCallAction()])
            ->emptyStateHeading('No prospects here')
            ->emptyStateDescription('Add a company that does not work with us yet. Prospects added on a phone appear here after its sync.');
    }

    /** Log one call (any rep, also on somebody else's prospect). */
    public static function logCallAction(): Action
    {
        return Action::make('logCall')->label('Log a call')->icon(Heroicon::OutlinedPhone)->color('gray')
            ->modalHeading(fn (SalesProspect $record) => 'Call to ' . $record->name)
            ->modalSubmitActionLabel('Save the call')
            ->schema([
                Select::make('outcome')->label('How did it go?')->required()->native(false)
                    ->options(array_map(fn (array $o) => $o[0], array_diff_key(SalesProspectNote::OUTCOMES, ['converted' => 1]))),
                Textarea::make('remark')->label('Remark (optional)')->rows(2)->maxLength(300),
                DateTimePicker::make('next_followup_at')->label('Next follow-up (optional)')->seconds(false)->native(false)
                    ->timezone(config('sales.timezone'))->minDate(now()->startOfDay())->maxDate(now()->addYears(2)),
            ])
            ->action(fn (SalesProspect $record, array $data) => DeskUi::attempt(
                fn () => app(DeskProspects::class)->logCall(auth()->user(), $record, $data['outcome'], $data['remark'] ?? null, $data['next_followup_at'] ?? null),
                'Call saved',
            ));
    }

    /** Plan a visit to this prospect (only when Meeting plans is open too). */
    public static function planVisitAction(): Action
    {
        return Action::make('planVisit')->label('Plan a visit')->icon(Heroicon::OutlinedCalendarDays)->color('gray')
            ->visible(fn () => RepDesk::allows(auth()->user(), DeskMeetings::AREA))
            ->modalHeading(fn (SalesProspect $record) => 'Visit ' . $record->name)
            ->schema([
                DateTimePicker::make('at')->label('Day and time')->required()->seconds(false)->native(false)->timezone(config('sales.timezone'))
                    ->minDate(now()->startOfDay())->maxDate(now()->addDays(90)),
                Select::make('purpose')->label('Purpose')->options(DeskMeetings::PURPOSES)->native(false)->placeholder('Not said'),
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
