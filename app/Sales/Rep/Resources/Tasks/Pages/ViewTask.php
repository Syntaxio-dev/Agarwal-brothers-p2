<?php

namespace App\Sales\Rep\Resources\Tasks\Pages;

use App\Sales\Rep\Concerns\ChecksDeskArea;
use App\Sales\Rep\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

/** One task: what to do, by when, where to send the result, the files (downloaded), and the rep's own status and note. */
class ViewTask extends ViewRecord
{
    use ChecksDeskArea;

    protected static string $resource = TaskResource::class;

    public function getTitle(): string
    {
        return $this->record->title;
    }

    protected function getHeaderActions(): array
    {
        return [TaskResource::updateAction()];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.sales.rep.task')->columnSpanFull()->viewData([
                't' => $this->record->load(['files', 'creator']),
                'tz' => config('sales.timezone'),
            ]),
        ]);
    }
}
