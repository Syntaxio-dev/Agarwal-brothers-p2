<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Support\RepAccount;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Add a rep, step 1 of 2. The password is made here (RepAccount::newPassword) and handed to step 2 through the session of this
 * manager only: never in the URL, never in a log, never stored readable. Step 2 is RepSignInReady.
 */
class CreateSalesRep extends CreateRecord
{
    protected static string $resource = SalesRepResource::class;

    protected static bool $canCreateAnother = false;

    /** The session key step 2 reads once. */
    public static function sessionKey(int $profileId): string
    {
        return 'sales_new_rep.' . $profileId;
    }

    /** A rep is a login (users row) plus a sales profile: RepAccount makes both together. */
    protected function handleRecordCreation(array $data): Model
    {
        $password = RepAccount::newPassword();

        $profile = RepAccount::create([
            'name' => $data['name'],
            'employee_code' => $data['employee_code'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $password,
            'is_active' => true,
        ]);

        session()->put(static::sessionKey($profile->id), Crypt::encryptString($password));

        return $profile;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rep added';
    }

    protected function getRedirectUrl(): string
    {
        return SalesRepResource::getUrl('ready', ['record' => $this->record]);
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->label('Create rep'),
            $this->getCancelFormAction(),
        ];
    }

    public function getTitle(): string
    {
        return 'Add a rep';
    }
}
