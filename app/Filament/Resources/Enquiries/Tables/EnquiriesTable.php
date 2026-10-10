<?php

namespace App\Filament\Resources\Enquiries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('items')->with('items'))
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->description(fn ($record) => $record->created_at->format('d M, h:i A'))
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->email),
                TextColumn::make('phone')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('General enquiry')
                    ->state(fn ($record) => $record->items_count
                        ? $record->items_count . ' products (group)'
                        : $record->product?->name)
                    ->description(fn ($record) => $record->items_count ? $record->items->pluck('product_name')->take(2)->implode(', ') . ($record->items_count > 2 ? '…' : '') : null)
                    ->badge(fn ($record) => (bool) $record->items_count)
                    ->color(fn ($record) => $record->items_count ? 'info' : 'gray')
                    ->limit(30),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'contacted' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'closed' => 'Closed',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('View / update'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
