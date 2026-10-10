<?php

namespace App\Filament\Resources\Slides\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SlidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->width(120)
                    ->height(68),
                TextColumn::make('title')
                    ->searchable()
                    ->description(fn ($record) => $record->subtitle)
                    ->placeholder('Untitled slide'),
                TextColumn::make('video')
                    ->label('Media')
                    ->state(fn ($record) => $record->video ? 'Video' : 'Image')
                    ->badge()
                    ->color(fn (string $state) => $state === 'Video' ? 'info' : 'gray'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No slides yet')
            ->emptyStateDescription('Add a slide to show it in the homepage hero carousel. Drag rows to change the order.')
            ->filters([
                \Filament\Tables\Filters\Filter::make('no_alt')->label('Image without description')->toggle()
                    ->query(fn ($query) => $query->withoutAlt()),
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
