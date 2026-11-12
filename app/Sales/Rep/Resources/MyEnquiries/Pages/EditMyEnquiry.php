<?php

namespace App\Sales\Rep\Resources\MyEnquiries\Pages;

use App\Sales\Rep\Resources\MyEnquiries\MyEnquiryResource;
use App\Sales\Support\RepEnquiryService;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditMyEnquiry extends EditRecord
{
    protected static string $resource = MyEnquiryResource::class;

    /** Form rupees -> paise. */
    public static function paise(array $items): array
    {
        return array_values(array_map(function (array $i) {
            $rupees = $i['budget_rupees'] ?? null;
            unset($i['budget_rupees']);
            $i['budget_paise'] = filled($rupees) ? (int) round(((float) $rupees) * 100) : null;

            return $i;
        }, $items));
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = array_map(fn (array $i) => $i + ['budget_rupees' => isset($i['budget_paise']) ? $i['budget_paise'] / 100 : null], $data['items'] ?? []);
        $data['note'] = null;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['items'] = static::paise($data['items'] ?? []);

        return app(RepEnquiryService::class)->update($record, auth()->user(), $data);
    }
}
