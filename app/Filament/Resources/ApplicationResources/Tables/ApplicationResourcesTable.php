<?php

namespace App\Filament\Resources\ApplicationResources\Tables;

use App\Models\ApplicationResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->width(80)
                    ->height(50),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('medium')
                    ->limit(55)
                    ->description(fn ($record) => $record->source),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ApplicationResource::CATEGORIES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'appnote' => 'info',
                        'guide' => 'primary',
                        'video' => 'danger',
                        'brochure' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('pdf')
                    ->label('File')
                    ->state(fn ($record) => filled($record->pdf) ? 'PDF' : (filled($record->link_url) ? 'Link' : null))
                    ->badge()
                    ->color('info')
                    ->placeholder('None'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('category')
                    ->options(ApplicationResource::CATEGORIES),
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
