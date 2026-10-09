<?php

namespace App\Filament\Resources\TeamMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TeamMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->designation),
                ToggleColumn::make('is_leader')
                    ->label('Leadership'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No team members yet')
            ->emptyStateDescription('Add people to show them in "Meet the team" on the Our Story page.')
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
