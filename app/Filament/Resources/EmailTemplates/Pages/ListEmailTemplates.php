<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    public function getSubheading(): ?string
    {
        return 'Automatic emails go to customers right after they send a form. Manual replies are starting points your team can pick in "Reply by email".';
    }
}
