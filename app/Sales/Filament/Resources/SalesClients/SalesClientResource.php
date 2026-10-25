<?php

namespace App\Sales\Filament\Resources\SalesClients;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesClients\Pages\CreateSalesClient;
use App\Sales\Filament\Resources\SalesClients\Pages\EditSalesClient;
use App\Sales\Filament\Resources\SalesClients\Pages\ListSalesClients;
use App\Sales\Filament\Resources\SalesClients\RelationManagers\ContactsRelationManager;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\ClientImporter;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\SalesExporter;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * THE client pool of the company: the manager fills it (by hand or from a CSV), gives clients to reps, and the reps improve it
 * from their phones (new numbers, new people). Every change reaches all phones on their next sync.
 */
class SalesClientResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-clients';

    protected static ?string $model = SalesClient::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'client';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['assignee', 'owner'])->withCount('contacts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Client')->required()->minLength(2)->maxLength(150)
                ->helperText('The company or customer, for example "JK Cement". The people who work there are added below, after saving.'),
            TextInput::make('city')->label('Place')->maxLength(100)->helperText('Town or city: the team filters on it.'),
            TextInput::make('company')->label('Group / company (optional)')->maxLength(150),
            TextInput::make('phone')->label('Main number (optional)')->tel()->maxLength(30)
                ->rule(fn (?Model $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                    if (! filled($value)) {
                        return;
                    }
                    $normal = PhoneNumber::normalize((string) $value);
                    if ($normal === null) {
                        $fail('Enter a valid phone number (10 digits, or with the country code).');
                    } elseif ($owner = ClientImporter::numberOwner($normal, null, $record?->uuid)) {
                        $fail("This number is already saved under $owner.");
                    }
                }),
            Select::make('assigned_to')->label('Works with (rep)')
                ->options(fn () => self::repOptions())
                ->searchable()->native(false)
                ->placeholder('Nobody yet')
                ->helperText('The rep who calls and visits this client. Everybody can still see it.'),
            Textarea::make('notes')->rows(3)->maxLength(1000)->columnSpanFull(),
        ])->columns(2);
    }

    /** @return array<int, string> user id => "Name (CODE)" for active reps */
    public static function repOptions(): array
    {
        return SalesProfile::active()->with('user')->get()
            ->mapWithKeys(fn (SalesProfile $p) => [$p->user_id => $p->user->name . ' (' . $p->employee_code . ')'])
            ->sort()->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Client')->searchable()->weight('medium')->description(fn (SalesClient $r) => $r->company),
                TextColumn::make('city')->label('Place')->placeholder('—')->searchable()->sortable(),
                TextColumn::make('contacts_count')->label('People')->alignCenter()->sortable(),
                TextColumn::make('phone')->label('Main number')->placeholder('—')->searchable()->copyable(),
                TextColumn::make('assignee.name')->label('Works with')->placeholder('Nobody')->searchable(),
                TextColumn::make('owner.name')->label('Added by')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')->limit(60)->tooltip(fn (SalesClient $r) => $r->notes)->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')->label('Added')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('city')->label('Place')
                    ->options(fn () => SalesClient::query()->whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city', 'city')->all()),
                SelectFilter::make('assigned_to')->label('Works with')->options(fn () => self::repOptions()),
                Filter::make('nobody')->label('Not given to a rep')->query(fn (Builder $q) => $q->whereNull('assigned_to')),
                Filter::make('no_people')->label('No people saved')->query(fn (Builder $q) => $q->whereDoesntHave('contacts')),
            ])
            ->headerActions([
                self::importAction(),
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Export clients')
                    ->modalDescription('Downloads every client that matches the filters and search you have set now.')
                    ->modalSubmitActionLabel('Download')
                    ->fillForm(['format' => 'xlsx'])
                    ->schema([
                        Select::make('format')->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => SalesExporter::clients($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->modalDescription('The client and the people saved under it disappear from every phone on its next sync.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign')
                        ->label('Give to a rep')
                        ->icon(Heroicon::OutlinedUserPlus)
                        ->schema([
                            Select::make('rep')->label('Rep')->options(fn () => self::repOptions())->required()->searchable()->native(false),
                        ])
                        ->action(function (Collection $records, array $data) {
                            abort_unless(auth()->user()?->canManage('sales-clients'), 403);
                            $records->each(fn (SalesClient $c) => $c->update(['assigned_to' => (int) $data['rep']]));
                            Notification::make()->title($records->count() . ' ' . str('client')->plural($records->count()) . ' given to the rep')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateHeading('No clients yet')
            ->emptyStateDescription('Add one, or import your list of clients and numbers (CSV). The reps can add more from the phone.');
    }

    /** One CSV with the numbers the manager already has: client, place, contact, position, phone. */
    private static function importAction(): Action
    {
        return Action::make('import')
            ->label('Import from CSV')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->color('gray')
            ->modalHeading('Import clients and numbers')
            ->modalDescription('Save your sheet as CSV (Excel: Save As > CSV UTF-8). The first row names the columns: Client, Place, Contact, Position, Phone, Email, Notes. Only Client is needed. One row is one person; a number that is already saved is skipped.')
            ->modalSubmitActionLabel('Import')
            ->schema([
                FileUpload::make('file')->label('CSV file')
                    ->disk('local')->directory('sales-imports')->visibility('private')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                    ->maxSize(2048)->required(),
                Select::make('rep')->label('Give all new clients to (optional)')->options(fn () => self::repOptions())->searchable()->native(false),
            ])
            ->action(function (array $data) {
                abort_unless(auth()->user()?->canManage('sales-clients'), 403);

                $path = $data['file'];
                $summary = ClientImporter::fromCsv(Storage::disk('local')->path($path), filled($data['rep'] ?? null) ? (int) $data['rep'] : null, auth()->user());
                Storage::disk('local')->delete($path);

                $problems = array_merge($summary['duplicates'], $summary['skipped']);

                Notification::make()
                    ->title("{$summary['clients_created']} new clients, {$summary['contacts_created']} people added")
                    ->body(
                        "{$summary['rows']} rows read, {$summary['clients_reused']} matched a client that was already saved."
                        . ($problems ? "\n\nNot imported:\n" . implode("\n", array_slice($problems, 0, 8)) . (count($problems) > 8 ? "\n... and " . (count($problems) - 8) . ' more.' : '') : '')
                    )
                    ->color($problems ? 'warning' : 'success')
                    ->persistent()
                    ->send();
            });
    }

    public static function getRelations(): array
    {
        return [ContactsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesClients::route('/'),
            'create' => CreateSalesClient::route('/create'),
            'edit' => EditSalesClient::route('/{record}/edit'),
        ];
    }
}
