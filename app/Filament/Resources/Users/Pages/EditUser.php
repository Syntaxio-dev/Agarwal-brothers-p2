<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn () => auth()->id() === $this->record->id)
                ->modalDescription('This permanently removes the account. To only block sign-in, switch "Active" off instead.')
                ->failureNotificationTitle('Not deleted: the last active administrator cannot be removed.'),
        ];
    }
}
