<?php

namespace App\Sales\Auth;

use App\Models\User;
use App\Sales\Models\SalesProfile;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Auth\Authenticatable;
use SensitiveParameter;

/**
 * The sign-in of the Sales sub-domain, for managers AND field reps: a manager types the e-mail, a rep types the employee code (or the phone
 * number) he uses in the phone app, with the same password. A rep who signs in here is sent to his own desk (/rep) and never sees a manager
 * screen (User::canAccessPanel is not changed: the office stays staff-only). Underneath it is the normal Filament sign-in (rate limit,
 * equal timing and the same message for a wrong code and a wrong password).
 */
class SalesLogin extends Login
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email, employee code or phone')
            ->required()
            ->maxLength(190)
            ->autocomplete('username')
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        $typed = trim((string) $data['email']);

        if (! str_contains($typed, '@')) {
            // a code or a phone number: turn it into the account's own (placeholder) address; an unknown one gets an address of nobody, so it
            // fails like a wrong password does
            $typed = SalesProfile::findByLogin($typed)?->user?->email ?? 'no-such-rep@unknown.invalid';
        }

        return ['email' => $typed, 'password' => $data['password']];
    }

    /** A rep may pass this sign-in, but only to reach his own desk. */
    protected function isUserAllowedToAccessPanel(Authenticatable $user): bool
    {
        return parent::isUserAllowedToAccessPanel($user) || $this->isRep($user);
    }

    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();
        $user = Filament::auth()->user();

        return $response && $user && $this->isRep($user) && ! $user->isStaff() ? $this->toDesk() : $response;
    }

    /** Already signed in as a rep and opening the sign-in again: straight to the desk. */
    public function mount(): void
    {
        $user = Filament::auth()->user();

        if ($user && $this->isRep($user) && ! $user->isStaff()) {
            redirect()->to(Filament::getPanel('rep')->getUrl());

            return;
        }

        parent::mount();
    }

    private function isRep(Authenticatable $user): bool
    {
        return $user instanceof User && $user->canAccessPanel(Filament::getPanel('rep'));
    }

    private function toDesk(): LoginResponse
    {
        return new class implements LoginResponse
        {
            public function toResponse($request)
            {
                return redirect()->to(Filament::getPanel('rep')->getUrl());
            }
        };
    }
}
