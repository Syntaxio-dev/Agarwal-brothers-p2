<?php

namespace App\Sales\Filament\Resources\SalesContacts;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesContacts\Pages\ManageSalesContacts;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesPosition;
use App\Sales\Support\ClientImporter;
use App\Sales\Support\PhoneNumber;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Every person in the pool, with the company they work at (or none: a customer who buys on their own) and which rep added them.
 * The people of one company are also on that client's own page.
 */
class SalesContactResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-clients';

    protected static ?string $model = SalesContact::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'People';

    protected static ?string $modelLabel = 'person';

    protected static ?string $pluralModelLabel = 'people';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        // One number, one person: a number that is already saved somewhere in the pool is refused.
        $numberRule = fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record) {
            if (! filled($value)) {
                return;
            }

            $normal = PhoneNumber::normalize((string) $value);

            if ($normal === null) {
                $fail('Enter a valid phone number (10 digits, or with the country code).');

                return;
            }

            if ($owner = ClientImporter::numberOwner($normal, $record?->uuid)) {
                $fail("This number is already saved under $owner.");
            }
        };

        return $schema->components([
            TextInput::make('name')->label('Name')->required()->minLength(2)->maxLength(150),
            Select::make('client_uuid')->label('Company')
                ->options(fn () => SalesClient::query()->orderBy('name')->pluck('name', 'uuid')->all())
                ->searchable()->native(false)
                ->placeholder('No company (buys on their own)'),
            Select::make('position')
                ->options(fn (?Model $record) => collect(SalesPosition::names())->when($record?->position, fn ($c, $p) => $c->push($p))->unique()->mapWithKeys(fn ($n) => [$n => $n])->all())
                ->searchable()->native(false),
            TextInput::make('phone')->tel()->maxLength(30)->rule(fn (?Model $record) => $numberRule($record)),
            TextInput::make('email')->email()->maxLength(150),
            TextInput::make('notes')->maxLength(1000)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['client', 'addedBy']))
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('client.name')->label('Company')->placeholder('No company')->searchable(),
                TextColumn::make('position')->placeholder('—')->badge()->color('gray'),
                TextColumn::make('phone')->placeholder('—')->copyable()->searchable(),
                TextColumn::make('addedBy.name')->label('Added by'),
                TextColumn::make('created_at')->label('Added')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('company')->label('Company')
                    ->placeholder('All people')->trueLabel('Works at a company')->falseLabel('No company')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('client_uuid'),
                        false: fn ($q) => $q->whereNull('client_uuid'),
                        blank: fn ($q) => $q,
                    ),
                \Filament\Tables\Filters\SelectFilter::make('user_id')->label('Added by')
                    ->options(fn () => \App\Models\User::query()->whereIn('id', SalesContact::query()->select('user_id'))->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No people saved yet')
            ->emptyStateDescription('The reps add the people they talk to from the phone.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageSalesContacts::route('/')];
    }
}
