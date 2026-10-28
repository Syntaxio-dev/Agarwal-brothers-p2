<?php

namespace App\Sales\Filament\Resources\SalesReps;

use App\Filament\Concerns\RestrictedByRole;
use App\Models\User;
use App\Sales\Filament\Resources\SalesReps\Pages\CreateSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\EditSalesRep;
use App\Sales\Filament\Resources\SalesReps\Pages\ListSalesReps;
use App\Sales\Filament\Resources\SalesReps\Pages\RepSignInReady;
use App\Sales\Filament\Resources\SalesReps\Pages\ViewSalesRep;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\RepAccount;
use App\Sales\Support\SalesInsights;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * The field reps: the roster (where is everybody today), one rep's profile, and adding a rep in two steps.
 * A rep is switched off first; a manager may then delete the rep and their records for good (deleteAction). Every rule about accounts lives in RepAccount; the screens only call it.
 */
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

    // Filament's own delete is off: a rep is removed only through deleteAction (switched off first, the code typed, the counts shown).
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
        return parent::getEloquentQuery()->with('user');
    }

    /** "Reset password": a new password is made and shown once in a notification (it is not stored anywhere readable). Used by the profile and the edit page. */
    public static function resetPasswordAction(Closure $record): Action
    {
        return Action::make('resetPassword')
            ->label('Reset password')
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('A new password is made and every phone of this rep is signed out. Tell the new password to the rep.')
            ->action(function () use ($record) {
                abort_unless(static::canViewAny(), 403);

                /** @var SalesProfile $profile */
                $profile = $record();
                $password = RepAccount::newPassword();
                RepAccount::resetPassword($profile, $password);

                Notification::make()
                    ->title('New password for ' . $profile->user->name)
                    ->body($password . '   (write it down: it is not shown again)')
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    /** "Switch off" with the question first: the phone is signed out on its next sync, the records stay. */
    public static function switchOffAction(Closure $record): Action
    {
        return Action::make('switchOff')
            ->label('Switch off')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn () => 'Switch off ' . $record()->user->name . '?')
            ->modalDescription('Their phone is signed out on its next sync. Their records stay.')
            ->modalSubmitActionLabel('Switch off')
            ->action(function () use ($record) {
                abort_unless(static::canViewAny(), 403);

                $profile = $record();
                RepAccount::setActive($profile, false);
                Notification::make()->title($profile->user->name . ' is switched off')->success()->send();
            });
    }

    /**
     * "Delete": the rep AND everything they own, for good. Only for a rep who is switched off; the window says what goes (the counts), and the
     * manager types the employee code to confirm. The shared client pool stays. `$find` gets the action's arguments and returns the profile.
     */
    public static function deleteAction(Closure $find, ?Closure $after = null): Action
    {
        return Action::make('deleteRep')
            ->label('Delete rep')
            ->color('danger')
            ->modalHeading(fn (array $arguments) => 'Delete ' . $find($arguments)->user->name . ' for good?')
            ->modalDescription(function (array $arguments) use ($find) {
                $counts = collect(RepAccount::recordCounts($find($arguments)))->filter()->map(fn ($n, $kind) => $n . ' ' . $kind)->implode(', ');

                return 'This removes the rep and every record they own: ' . ($counts ?: 'nothing else (no records)') . '. It cannot be undone. Clients and people they added stay in the shared pool and become yours. Export their expenses first if you need them.';
            })
            ->modalSubmitActionLabel('Delete for good')
            ->schema(fn (array $arguments) => [
                TextInput::make('confirm')
                    ->label('Type the employee code ' . $find($arguments)->employee_code . ' to confirm')
                    ->required()
                    ->rule(fn () => function (string $attribute, mixed $value, Closure $fail) use ($find, $arguments) {
                        if (Str::upper(trim((string) $value)) !== Str::upper($find($arguments)->employee_code)) {
                            $fail('The code does not match.');
                        }
                    }),
            ])
            ->action(function (array $arguments) use ($find, $after) {
                abort_unless(static::canViewAny(), 403);

                $profile = $find($arguments);

                if ($profile->is_active) {
                    Notification::make()->title('Switch ' . $profile->user->name . ' off first')->warning()->send();

                    return;
                }

                $name = $profile->user->name;
                RepAccount::deleteWithRecords($profile, auth()->user());
                Notification::make()->title($name . ' was deleted')->success()->send();
                $after && $after();
            });
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
            Section::make(fn (string $operation) => $operation === 'create' ? 'Add a rep' : 'Rep')
                ->description(fn (string $operation) => $operation === 'create'
                    ? 'Step 1 of 2. The password is made for you and shown on the next screen.'
                    : 'The rep signs in on the phone with the employee code (or the phone number) and the password.')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('Full name')->required()->minLength(2)->maxLength(100),
                    TextInput::make('employee_code')
                        ->required()
                        ->maxLength(30)
                        ->regex('/^[A-Za-z0-9-]{3,30}$/')
                        ->validationMessages(['regex' => 'Use 3 to 30 letters, numbers or "-".'])
                        ->unique(table: 'sales_profiles', column: 'employee_code', ignoreRecord: true)
                        ->default(fn () => SalesInsights::nextEmployeeCode())
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->dehydrated(fn (string $operation): bool => $operation === 'create')
                        ->helperText(fn (string $operation): string => $operation === 'create' ? 'The next free code. You may change it now; it cannot be changed later.' : 'It cannot be changed.'),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(20)
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->rule(fn ($record) => $phoneRule($record))
                        ->helperText('The rep can also sign in to the app with this number.'),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->maxLength(150)
                        ->rule(fn ($record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record) {
                            if (filled($value) && User::where('email', Str::lower(trim((string) $value)))->when($record, fn ($q) => $q->where('id', '!=', $record->user_id))->exists()) {
                                $fail('This e-mail already belongs to another login.');
                            }
                        })
                        ->helperText('Optional: for your records only. The rep signs in with the employee code (or the phone number) and the password, not with the e-mail.'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->visibleOn('edit')
                        ->helperText('Switch off to block the phone at once (it is signed out on its next sync). Their records stay.'),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesReps::route('/'),
            'create' => CreateSalesRep::route('/create'),
            'ready' => RepSignInReady::route('/{record}/ready'),
            'view' => ViewSalesRep::route('/{record}'),
            'edit' => EditSalesRep::route('/{record}/edit'),
        ];
    }
}
