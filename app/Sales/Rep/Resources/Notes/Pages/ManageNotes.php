<?php

namespace App\Sales\Rep\Resources\Notes\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Notes\NoteResource;
use Filament\Resources\Pages\ListRecords;

class ManageNotes extends ListRecords
{
    use ChecksDeskArea;

    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [NoteResource::addAction()];
    }
}
