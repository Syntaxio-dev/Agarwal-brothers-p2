<?php

namespace App\Sales\Filament\Resources\SalesTasks\Pages;

use App\Sales\Filament\Resources\SalesTasks\SalesTaskResource;
use App\Sales\Models\SalesTaskFile;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSalesTask extends EditRecord
{
    protected static string $resource = SalesTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->before(fn () => $this->getRecord()->files->each->delete())];
    }

    protected function afterSave(): void
    {
        SalesTaskResource::attachUploads($this->getRecord(), $this->data);
        $this->data['attachments'] = [];
        $this->data['attachment_names'] = [];
    }

    /** The "Remove" button of the list of files above the upload box. */
    public function removeFile(int $id): void
    {
        abort_unless(auth()->user()?->canManage('sales-tasks'), 403);

        $file = SalesTaskFile::where('task_id', $this->getRecord()->id)->where('id', $id)->first();
        if ($file) {
            $file->delete();
            $this->getRecord()->touch();
            $this->getRecord()->forceFill(['version' => (int) $this->getRecord()->version + 1])->saveQuietly();
            $this->getRecord()->unsetRelation('files');
        }
    }
}
