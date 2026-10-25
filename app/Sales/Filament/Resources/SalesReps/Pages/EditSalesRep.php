<?php

namespace App\Sales\Filament\Resources\SalesReps\Pages;

use App\Sales\Filament\Resources\SalesReps\SalesRepResource;
use App\Sales\Support\PhoneNumber;
use App\Sales\Support\RepAccount;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditSalesRep extends EditRecord
{
    protected static string $resource = SalesRepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetPassword')
                ->label('Reset password')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('A new password is made and every phone of this rep is signed out. Tell the new password to the rep.')
                ->action(function () {
                    $password = RepAccount::newPassword();
                    RepAccount::resetPassword($this->record, $password);

                    Notification::make()
                        ->title('New password for ' . $this->record->user->name)
                        ->body($password . '   (write it down: it is not shown again)')
                        ->success()
                        ->persistent()
                        ->send();
                }),
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
