<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Closure;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isSelf = fn ($record): bool => $record && auth()->id() === $record->id;

        // The panel must always keep at least one active administrator.
        $keepOneAdmin = fn ($record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record) {
            if (! $record || $record->role !== 'admin' || ! $record->is_active) {
                return;
            }

            $stillAdmin = $attribute === 'role' ? $value === 'admin' : (bool) $value;

            if (! $stillAdmin && ! User::activeAdmins()->whereKeyNot($record->id)->exists()) {
                $fail('At least one active administrator is required.');
            }
        };

        return $schema
            ->components([
                Section::make('Account')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email (used to sign in)')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            // Blank on edit means "keep the current password".
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                ? 'At least 8 characters. Share it with the person safely and ask them to change it.'
                                : 'Leave empty to keep the current password.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Role & access')
                    ->description('What this person can open and change in the admin panel.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('role')
                            ->options(collect(User::roles())->map(fn ($r) => $r['label'])->all())
                            ->required()
                            ->live()
                            ->default('editor')
                            ->disabled(fn ($record) => $isSelf($record))
                            ->dehydrated()
                            ->rule(fn ($record) => $keepOneAdmin($record))
                            ->helperText(fn ($record) => $isSelf($record)
                                ? 'You cannot change your own role.'
                                : null),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText(fn ($record) => $isSelf($record)
                                ? 'You cannot deactivate yourself.'
                                : 'Turn off to block this person from signing in without deleting the account.')
                            ->default(true)
                            ->inline(false)
                            ->disabled(fn ($record) => $isSelf($record))
                            ->dehydrated()
                            ->rule(fn ($record) => $keepOneAdmin($record)),
                        Placeholder::make('role_help')
                            ->label('What this role can do')
                            ->content(fn ($get) => new HtmlString(
                                e(User::roles()[$get('role')]['description'] ?? 'Choose a role.')
                            ))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
