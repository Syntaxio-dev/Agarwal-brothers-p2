<?php

namespace App\Sales\Filament\Resources\SalesClients\RelationManagers;

use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesPosition;
use App\Sales\Support\ClientImporter;
use App\Sales\Support\PhoneNumber;
use Closure;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** The people at a client: name, position and number. The phones match calls and meetings against these. */
class ContactsRelationManager extends RelationManager
{
    protected static string $relationship = 'contacts';

    protected static ?string $title = 'People at this client';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->contacts()->count();

        return $count ? (string) $count : null;
    }

    public function form(Schema $schema): Schema
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
            Select::make('position')
                // the person's own position stays in the list even if it was renamed or removed from the list since
                ->options(fn (?Model $record) => collect(SalesPosition::names())->when($record?->position, fn ($c, $p) => $c->push($p))->unique()->mapWithKeys(fn ($n) => [$n => $n])->all())
                ->searchable()
                ->native(false)
                ->createOptionForm([TextInput::make('name')->label('New position')->required()->minLength(2)->maxLength(60)])
                ->createOptionUsing(fn (array $data): string => SalesPosition::firstOrCreate(['name' => trim($data['name'])], ['is_preset' => false, 'created_by' => auth()->id()])->name),
            TextInput::make('phone')->tel()->maxLength(30)->rule(fn (?Model $record) => $numberRule($record)),
            TextInput::make('email')->email()->maxLength(150),
            TextInput::make('notes')->maxLength(1000)->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('position')->placeholder('—')->badge()->color('gray'),
                TextColumn::make('phone')->placeholder('—')->copyable()->searchable(),
                TextColumn::make('email')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('addedBy.name')->label('Added by')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->headerActions([
                CreateAction::make()->label('Add a person')->mutateDataUsing(fn (array $data) => $data + ['user_id' => auth()->id()]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('No people saved yet')
            ->emptyStateDescription('Add the people you can call here, or the reps add them from the phone.');
    }
}
