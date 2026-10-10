<?php

namespace App\Filament\Resources\JobApplications\Pages;

use App\Filament\Resources\JobApplications\JobApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditJobApplication extends EditRecord
{
    protected static string $resource = JobApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \App\Filament\Support\ReplyByEmailAction::make()->record($this->getRecord()),
            Action::make('resume')
                ->label('Download resume')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->visible(fn () => filled($this->record->resume) && Storage::disk('local')->exists($this->record->resume))
                ->action(fn () => Storage::disk('local')->download(
                    $this->record->resume,
                    Str::slug($this->record->name) . '-resume.' . pathinfo($this->record->resume, PATHINFO_EXTENSION)
                )),
            DeleteAction::make(),
        ];
    }
}
