<?php

namespace App\Sales\Filament\Resources\SalesClients\Pages;

use App\Sales\Actions\SetClientAccess;
use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesClient extends CreateRecord
{
    protected static string $resource = SalesClientResource::class;

    /** Who sees the new client is written by SetClientAccess after it exists (one write path), not by the model. */
    private array $access = ['main' => null, 'reps' => []];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->access = ['main' => filled($data['assigned_to'] ?? null) ? (int) $data['assigned_to'] : null, 'reps' => array_map('intval', $data['access_reps'] ?? [])];
        unset($data['assigned_to'], $data['access_reps']);

        return $data + ['user_id' => auth()->id()];
    }

    protected function afterCreate(): void
    {
        if ($this->access['main'] !== null || $this->access['reps'] !== []) {
            app(SetClientAccess::class)->handle($this->record, $this->access['reps'], $this->access['main'], auth()->user());
        }
    }

    protected function getRedirectUrl(): string
    {
        // straight to the people list of the new client
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
