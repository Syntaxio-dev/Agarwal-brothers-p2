<?php

namespace App\Sales\Filament\Resources\SalesReps;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesReps\Pages\CreateSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\EditSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\ListSalesReps;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\Money;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\RepAccount;
use Closure;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** The field reps: who they are, whether they may use the app, when their phone last synced. */
class SalesRepResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-reps';

    protected static ?string $model = SalesProfile::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Reps';

    protected static ?string $modelLabel = 'rep';

    protected static ?string $recordTitleAttribute = 'employee_code';

    // Reps are switched off, never deleted: their expenses and meetings must stay readable.
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $today = now(config('sales.timezone'))->toDateString();
        $monthStart = now(config('sales.timezone'))->startOfMonth()->toDateString();

        return parent::getEloquentQuery()
            ->with('user')
            ->withSum(['expenses as month_spend' => fn ($q) => $q->whereBetween('expense_date', [$monthStart, $today])], 'amount_paise');
    }

    public static function form(Schema $schema): Schema
    {
        // The phone number is stored in one spelling (+91...), so uniqueness is checked on that spelling.
        $phoneRule = fn ($record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record) {
            if (! filled($value)) {
                return;
            }

            $normal = PhoneNumber::normalize((string) $value);

            if ($normal === null) {
                $fail('Enter a valid phone number (10 digits, or with the country code).');
            } elseif (SalesProfile::where('phone', $normal)->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))->exists()) {
                $fail('Another rep already has this phone number.');
            }
        };

        return $schema->components([
            Section::make('Rep')
                ->description('The rep signs in on the phone with the employee code (or the phone number) and the password.')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required()->minLength(2)->maxLength(100),
                    TextInput::make('employee_code')
                        ->required()
                        ->maxLength(30)
                        ->regex('/^[A-Za-z0-9-]{3,30}$/')
                        ->validationMessages(['regex' => 'Use 3 to 30 letters, numbers or "-".'])
                        ->unique(table: 'sales_profiles', column: 'employee_code', ignoreRecord: true)
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->dehydrated(fn (string $operation): bool => $operation === 'create')
                        ->helperText(fn (string $operation): string => $operation === 'create' ? 'For example REP001. It cannot be changed later.' : 'It cannot be changed.'),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(20)
                        ->rule(fn ($record) => $phoneRule($record))
                        ->helperText('Optional. A rep can also sign in with it.'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->helperText('Switch off to block the phone at once (it is signed out on its next sync). Their records stay.'),
                    TextInput::make('password')
                        ->visibleOn('create')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->maxLength(100)
                        ->default(fn () => RepAccount::newPassword())
                        ->helperText('A password is made for you (click the eye to read it). Tell it to the rep: it is not shown again.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Name')->searchable()->weight('medium')->description(fn (SalesProfile $r) => $r->employee_code),
                TextColumn::make('phone')->placeholder('—')->searchable()->copyable(),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->updateStateUsing(function (SalesProfile $record, $state) {
                        RepAccount::setActive($record, (bool) $state);

                        return (bool) $state;
                    }),
                TextColumn::make('last_sync_at')
                    ->label('Last sync')
                    ->since()
                    ->placeholder('Never')
                    ->color(fn (SalesProfile $r) => $r->isSyncStale() ? 'warning' : 'success')
                    ->sortable(),
                TextColumn::make('month_spend')
                    ->label('Spent this month')
                    ->formatStateUsing(fn ($state) => Money::rupees((int) $state))
                    ->alignEnd(),
                TextColumn::make('app_version')->label('App')->placeholder('—')->toggleable(),
                TextColumn::make('device_model')->label('Phone')->placeholder('—')->toggleable(),
            ])
            ->defaultSort('employee_code')
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->emptyStateHeading('No reps yet')
            ->emptyStateDescription('Add a rep, then give them the employee code and the password to sign in on the phone.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesReps::route('/'),
            'create' => CreateSalesRep::route('/create'),
            'edit' => EditSalesRep::route('/{record}/edit'),
        ];
    }
}
