<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\Actions\DuplicateProductAction;
use App\Models\Category;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('category.brand'))
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->category?->brand?->name . ' · ' . $record->category?->name),
                ToggleColumn::make('is_top_pick')
                    ->label('Top pick'),
                TextColumn::make('top_pick_order')
                    ->label('Order')
                    ->placeholder('—')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('brand')
                    ->label('Brand')
                    ->options(fn () => \App\Models\Brand::orderBy('name')->pluck('name', 'id'))
                    ->query(fn ($query, array $data) => $data['value']
                        ? $query->whereHas('category', fn ($q) => $q->where('brand_id', $data['value']))
                        : $query),
                TernaryFilter::make('is_top_pick')
                    ->label('Top picks'),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                // "To do" filters (the dashboard cards link here)
                Filter::make('no_image')->label('Without a photo')->toggle()
                    ->query(fn ($query) => $query->withoutImage()),
                Filter::make('no_specs')->label('Without specifications')->toggle()
                    ->query(fn ($query) => $query->withoutSpecs()),
                Filter::make('no_seo')->label('Without Google title/description')->toggle()
                    ->query(fn ($query) => $query->withoutSeo()),
            ])
            ->recordActions([
                EditAction::make(),
                DuplicateProductAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate selected')
                        ->icon(Heroicon::OutlinedEye)
                        ->requiresConfirmation()
                        ->modalDescription('The selected products will become visible on the website (if their brand is active).')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => true]);
                            Notification::make()->title($records->count() . ' ' . str('product')->plural($records->count()) . ' activated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate selected')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->requiresConfirmation()
                        ->modalDescription('The selected products will be hidden from the website. Nothing is deleted.')
                        ->action(function ($records) {
                            $records->each->update(['is_active' => false]);
                            Notification::make()->title($records->count() . ' ' . str('product')->plural($records->count()) . ' deactivated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('set_category')
                        ->label('Set category / brand')
                        ->icon(Heroicon::OutlinedTag)
                        ->modalHeading('Move the selected products')
                        ->modalDescription('Choose the Brand and product line they should belong to. Everything else about the products stays the same.')
                        ->modalSubmitActionLabel('Move products')
                        ->schema([
                            Select::make('category_id')
                                ->label('Brand · Category')
                                ->options(fn () => Category::with('brand')->get()
                                    ->sortBy(fn ($c) => $c->brand?->name . ' ' . $c->name)
                                    ->mapWithKeys(fn ($c) => [$c->id => $c->brand?->name . ' - ' . $c->name])
                                    ->all())
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update(['category_id' => (int) $data['category_id']]);
                            Notification::make()->title($records->count() . ' ' . str('product')->plural($records->count()) . ' moved')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ])->label('Bulk actions'),
            ]);
    }
}
