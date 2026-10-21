<?php

namespace App\Filament\Resources\EnquiryQuestions\Pages;

use App\Filament\Resources\EnquiryQuestions\EnquiryQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnquiryQuestion extends EditRecord
{
    protected static string $resource = EnquiryQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return EnquiryQuestionResource::cleanOptions($data);
    }
}
