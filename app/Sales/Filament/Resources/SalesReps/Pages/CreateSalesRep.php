<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Support\RepAccount;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSalesRep extends CreateRecord
{
    protected static string $resource = SalesRepResource::class;

    /** A rep is a login (users row) plus a sales profile: RepAccount makes both together. */
    protected function handleRecordCreation(array $data): Model
    {
        return RepAccount::create([
            'name' => $data['name'],
            'employee_code' => $data['employee_code'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Rep added. Give them the employee code and the password: the app signs in with these two only.';
    }
}
