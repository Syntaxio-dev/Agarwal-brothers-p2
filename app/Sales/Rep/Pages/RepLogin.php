<?php

namespace App\Sales\Rep\Pages;

use App\Sales\Models\SalesProfile;
use Filament\Auth\Pages\Login;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use SensitiveParameter;

/**
 * The reps' sign-in: the employee code (or the phone number) and the password the manager gave, exactly as in the phone app. Underneath it is
 * the normal Filament sign-in (rate limit, equal timing for a wrong rep and a wrong password, same answer for both): the typed code is only
 * turned into the account's e-mail (a placeholder nobody types) before the password is checked.
 */
class RepLogin extends Login
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Employee code or phone')
            ->required()
            ->maxLength(100)
            ->autocomplete('username')
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $profile = SalesProfile::findByLogin((string) $data['email']);

        return [
            // an unknown rep gets an address that belongs to nobody: the sign-in fails like a wrong password does
            'email' => $profile?->user?->email ?? 'no-such-rep@unknown.invalid',
            'password' => $data['password'],
        ];
    }
}
