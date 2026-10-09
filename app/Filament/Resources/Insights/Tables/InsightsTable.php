<?php

namespace App\Filament\Resources\Insights\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InsightsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('medium')
                    ->limit(60),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'blog' => 'Blog',
                        'news' => 'News & Events',
                        'webinar' => 'Webinar',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'blog' => 'info',
                        'news' => 'warning',
                        'webinar' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('event_date')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->sortable(),
                ToggleColumn::make('is_featured')
                    ->label('Featured'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'blog' => 'Blog',
                        'news' => 'News & Events',
                        'webinar' => 'Webinar',
                    ]),
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
