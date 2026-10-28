<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\RepAccount;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Edit details: name, e-mail, phone and the active switch (the employee code cannot be changed). The profile page links here. */
class EditSalesRep extends EditRecord
{
    protected static string $resource = SalesRepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('profile')->label('Back to profile')->color('gray')->url(fn () => SalesRepResource::getUrl('view', ['record' => $this->record])),
            SalesRepResource::resetPasswordAction(fn () => $this->record),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['name'] = $this->record->user->name;
        $data['email'] = RepAccount::realEmail($this->record->user->email);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        DB::transaction(function () use ($record, $data) {
            $record->user->update(['name' => $data['name'], 'email' => RepAccount::emailFor($record->employee_code, $data['email'] ?? null)]);
            $record->update(['phone' => PhoneNumber::normalize($data['phone'] ?? null)]);

            if ((bool) $record->is_active !== (bool) ($data['is_active'] ?? true)) {
                RepAccount::setActive($record, (bool) $data['is_active']);
            }
        });

        return $record->refresh();
    }
}
