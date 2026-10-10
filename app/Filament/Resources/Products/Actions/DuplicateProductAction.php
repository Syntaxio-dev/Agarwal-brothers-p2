<?php

namespace App\Filament\Resources\Products\Actions;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Support\ProductDuplicator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/** "Duplicate" for products: pick the new name and brand, get a draft copy to finish on the edit page. */
class DuplicateProductAction
{
    public static function make(): Action
    {
        return Action::make('duplicate')
            ->label('Duplicate')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->color('gray')
            ->visible(fn () => ProductResource::canCreate())
            ->modalHeading(fn (Product $record) => 'Duplicate "' . $record->name . '"')
            ->modalDescription('Copies the specifications, features, advantages and FAQs so you do not have to type them again. On the next page you can change, add or remove anything, including the brand and the number of specifications. The copy stays inactive until you switch it on.')
            ->modalSubmitActionLabel('Create copy')
            ->fillForm(fn (Product $record) => [
                'name' => $record->name . ' (copy)',
                'category_id' => $record->category_id,
                'copy_images' => false,
            ])
            ->schema([
                TextInput::make('name')
                    ->label('Name of the new product')
                    ->required()
                    ->maxLength(255),
                Select::make('category_id')
                    ->label('Brand · Category')
                    ->options(fn () => Category::with('brand')->get()
                        ->sortBy(fn ($c) => $c->brand?->name . ' ' . $c->name)
                        ->mapWithKeys(fn ($c) => [$c->id => $c->brand?->name . ' - ' . $c->name])
                        ->all())
                    ->searchable()
                    ->required()
                    ->helperText('Choose the brand and product line of the new product.'),
                Toggle::make('copy_images')
                    ->label('Also copy the pictures')
                    ->helperText('Leave off when the new product is from another brand; upload its own pictures instead.'),
            ])
            ->action(function (Product $record, array $data, $livewire) {
                $copy = ProductDuplicator::copy($record, $data['name'], (int) $data['category_id'], (bool) ($data['copy_images'] ?? false));

                Notification::make()
                    ->title('Copy created')
                    ->body('Finish the details, upload pictures and switch it to Active when it is ready.')
                    ->success()
                    ->send();

                $livewire->redirect(ProductResource::getUrl('edit', ['record' => $copy]));
            });
    }
}
