<?php

namespace App\Sales\Rep\Resources\MyEnquiries\Pages;

use App\Sales\Rep\Resources\MyEnquiries\MyEnquiryResource;
use App\Sales\Support\RepEnquiryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMyEnquiry extends CreateRecord
{
    protected static string $resource = MyEnquiryResource::class;

    protected static bool $canCreateAnother = false;

    /** The form's rupees become paise (as on the phone) and the service writes the record. */
    protected function handleRecordCreation(array $data): Model
    {
        $data['items'] = EditMyEnquiry::paise($data['items'] ?? []);

        return app(RepEnquiryService::class)->create(auth()->user(), $data);
    }
}
