<?php

namespace App\Filament\Resources\EnquiryQuestions\Pages;

use App\Filament\Resources\EnquiryQuestions\EnquiryQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnquiryQuestions extends ListRecords
{
    protected static string $resource = EnquiryQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
