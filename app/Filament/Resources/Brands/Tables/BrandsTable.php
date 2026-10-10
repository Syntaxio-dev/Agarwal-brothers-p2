<?php

namespace App\Filament\Resources\Brands\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public')
                    ->height(36)
                    ->extraImgAttributes(['class' => 'object-contain']),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('country.name')
                    ->label('Country')
                    ->sortable()
                    ->badge()
                    ->placeholder('Not set')
                    ->color(fn ($state) => $state ? 'info' : 'danger'),
                TextColumn::make('categories_count')
                    ->counts('categories')
                    ->label('Categories')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'name'),
                TernaryFilter::make('is_active')->label('Active'),
                // "To do" filters (the dashboard cards link here)
                Filter::make('no_country')->label('Without a country')->toggle()
                    ->query(fn ($query) => $query->withoutCountry()),
                Filter::make('no_logo')->label('Without a logo')->toggle()
                    ->query(fn ($query) => $query->withoutLogo()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription('Brands that still have product lines are skipped, so no products are deleted by accident.')
                        ->failureNotificationTitle(fn (int $successCount, int $totalCount): string => $successCount
                            ? "Deleted {$successCount} of {$totalCount}. The rest still have product lines."
                            : 'Nothing deleted: the selected brands still have product lines.'),
                ]),
            ]);
    }
}
