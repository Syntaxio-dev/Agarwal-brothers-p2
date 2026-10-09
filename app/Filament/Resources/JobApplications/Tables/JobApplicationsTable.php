<?php

namespace App\Filament\Resources\JobApplications\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class JobApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('opening'))
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
                TextColumn::make('position')
                    ->label('Applied for')
                    ->searchable()
                    ->description(fn ($record) => $record->opening ? 'Role application' : 'General application')
                    ->limit(40),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'shortlisted', 'interviewed' => 'info',
                        'hired' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'shortlisted' => 'Shortlisted',
                        'interviewed' => 'Interviewed',
                        'hired' => 'Hired',
                        'rejected' => 'Rejected',
                    ]),
                SelectFilter::make('job_opening_id')
                    ->label('Role')
                    ->relationship('opening', 'title'),
            ])
            ->recordActions([
                Action::make('resume')
                    ->label('Resume')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->visible(fn ($record) => filled($record->resume) && Storage::disk('local')->exists($record->resume))
                    ->action(fn ($record) => Storage::disk('local')->download(
                        $record->resume,
                        Str::slug($record->name) . '-resume.' . pathinfo($record->resume, PATHINFO_EXTENSION)
                    )),
                EditAction::make()->label('View / update'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
