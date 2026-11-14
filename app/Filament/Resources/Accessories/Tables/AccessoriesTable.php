<?php

namespace App\Filament\Resources\Accessories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccessoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->disk('public')->height(40)->extraImgAttributes(['class' => 'object-contain']),
                TextColumn::make('name')->searchable()->weight('medium')->description(fn ($record) => $record->short_description ? str($record->short_description)->limit(80) : null),
                TextColumn::make('brand.name')->label('Brand')->placeholder('General (all brands)')->badge()->color('info')->sortable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                SelectFilter::make('brand_id')->label('Brand')->relationship('brand', 'name'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No accessories yet')
            ->emptyStateDescription('Add calibration test kits and other accessories. Drag rows to set the order.')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
