<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    /** Save the product but keep it hidden from the website, whatever the Active switch says. */
    public function createAsDraft(): void
    {
        $this->data['is_active'] = false;

        $this->create();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            Action::make('saveDraft')
                ->label('Save as hidden draft')
                ->icon('heroicon-o-eye-slash')
                ->color('gray')
                ->action(fn () => $this->createAsDraft()),
            $this->getCreateAnotherFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    /** After saving, point to the next useful step instead of a bare "Created". */
    protected function getCreatedNotification(): ?Notification
    {
        $edit = ProductResource::getUrl('edit', ['record' => $this->getRecord()]);
        $product = $this->getRecord();

        $notification = Notification::make()
            ->success()
            ->title('Product saved')
            ->body($product->is_active
                ? 'It is live on the website. Add more to make it stronger:'
                : 'It is hidden from the website for now. Finish it, then switch Active on:')
            ->persistent()
            ->actions([
                Action::make('photos')->label($product->image ? 'More photos' : 'Add photos')->url($edit . '#images')->button()->color('gray'),
                Action::make('specs')->label('Add specs')->url($edit . '#specs')->button()->color('gray'),
                Action::make('visibility')->label($product->is_active ? 'Visibility' : 'Make it live')->url($edit . '#visibility')->button(),
            ]);

        return $notification;
    }
}
