<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->email)
                    ->badge(false),
                TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => User::roles()[$state]['label'] ?? ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'admin' => 'danger',
                        'editor' => 'info',
                        'sales' => 'success',
                        'hr' => 'warning',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->disabled(fn ($record) => auth()->id() === $record->id),
                TextColumn::make('created_at')
                    ->label('Added')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('role')
                    ->options(collect(User::roles())->map(fn ($r) => $r['label'])->all()),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription('You cannot delete yourself or the last active administrator; those are skipped.')
                        ->failureNotificationTitle(fn (int $successCount, int $totalCount): string => $successCount
                            ? "Deleted {$successCount} of {$totalCount}. The rest are protected."
                            : 'Nothing deleted: you cannot remove yourself or the last administrator.'),
                ]),
            ]);
    }
}
