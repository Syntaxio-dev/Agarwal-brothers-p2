<?php

namespace App\Filament\Resources\JobOpenings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class JobOpeningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->department),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->label('Type')
                    ->badge()
                    ->color('info'),
                TextColumn::make('applications_count')
                    ->counts('applications')
                    ->label('Applications')
                    ->badge()
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),
                ToggleColumn::make('is_active')
                    ->label('Hiring'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No job openings yet')
            ->emptyStateDescription('Add a role and it appears on the Careers page with its own application form.')
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
