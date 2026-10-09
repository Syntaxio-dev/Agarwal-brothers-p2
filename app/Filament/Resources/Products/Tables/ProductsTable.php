<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
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
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
