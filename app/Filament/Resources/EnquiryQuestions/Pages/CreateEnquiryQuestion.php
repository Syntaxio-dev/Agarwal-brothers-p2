<?php

namespace App\Filament\Resources\EnquiryQuestions\Pages;

use App\Filament\Resources\EnquiryQuestions\EnquiryQuestionResource;
use App\Models\EnquiryQuestion;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateEnquiryQuestion extends CreateRecord
{
    protected static string $resource = EnquiryQuestionResource::class;

    /** One form, many questions: each row of the list becomes its own question for the chosen vertical. */
    protected function handleRecordCreation(array $data): Model
    {
        $next = (int) EnquiryQuestion::where('vertical_id', $data['vertical_id'] ?? null)->max('sort_order');
        $first = null;

        foreach (array_values($data['questions'] ?? []) as $row) {
            $row = EnquiryQuestionResource::cleanOptions($row);

            $q = EnquiryQuestion::create([
                'vertical_id' => $data['vertical_id'] ?? null,
                'question' => $row['question'],
                'type' => $row['type'] ?? 'choice',
                'options' => $row['options'] ?? null,
                'sort_order' => ++$next,
                'is_active' => true,
            ]);

            $first ??= $q;
        }

        return $first;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Questions saved';
    }
}
