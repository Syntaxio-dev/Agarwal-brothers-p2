<?php

namespace App\Filament\Resources\Enquiries\Tables;

use App\Filament\Support\ReplyByEmailAction;
use App\Models\Enquiry;
use App\Models\User;
use App\Support\EnquiryExporter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['items', 'teamNotes'])->with(['items', 'assignee']))
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
                TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('General enquiry')
                    ->state(fn ($record) => $record->items_count
                        ? $record->items_count . ' products (group)'
                        : $record->product?->name)
                    ->description(fn ($record) => $record->items_count ? $record->items->pluck('product_name')->take(2)->implode(', ') . ($record->items_count > 2 ? '…' : '') : null)
                    ->badge(fn ($record) => (bool) $record->items_count)
                    ->color(fn ($record) => $record->items_count ? 'info' : 'gray')
                    ->limit(30),
                TextColumn::make('assignee.name')
                    ->label('Assigned to')
                    ->placeholder('Unassigned')
                    ->icon(fn ($record) => $record->assigned_to ? Heroicon::OutlinedUserCircle : null)
                    ->color(fn ($record) => $record->assigned_to ? null : 'gray')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Enquiry::STATUSES[$state] ?? ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'contacted' => 'info',
                        'quoted' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('team_notes_count')
                    ->label('Notes')
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(Enquiry::STATUSES),
                SelectFilter::make('assigned_to')
                    ->label('Assigned to')
                    ->options(fn () => User::assignable('enquiries')),
                // Quick filters (the dashboard cards link here)
                Filter::make('mine')->label('Assigned to me')->toggle()
                    ->query(fn ($query) => $query->where('assigned_to', auth()->id())),
                Filter::make('unassigned')->label('Unassigned')->toggle()
                    ->query(fn ($query) => $query->whereNull('assigned_to')),
                Filter::make('open')->label('Still open (not closed)')->toggle()
                    ->query(fn ($query) => $query->open()),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Export enquiries')
                    ->modalDescription('Downloads every enquiry that matches the filters and search you have set now.')
                    ->modalSubmitActionLabel('Download')
                    ->fillForm(['format' => 'xlsx'])
                    ->schema([
                        Select::make('format')
                            ->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()
                            ->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => EnquiryExporter::download($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            ->recordActions([
                ReplyByEmailAction::make()->iconButton()->tooltip('Reply by email'),
                EditAction::make()->label('View / update'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assign')
                        ->label('Assign to...')
                        ->icon(Heroicon::OutlinedUserPlus)
                        ->modalHeading('Assign the selected enquiries')
                        ->modalSubmitActionLabel('Assign')
                        ->schema([
                            Select::make('assigned_to')
                                ->label('Team member')
                                ->options(fn () => User::assignable('enquiries'))
                                ->placeholder('Nobody (remove assignment)')
                                ->searchable(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update(['assigned_to' => $data['assigned_to'] ?: null]);
                            Notification::make()->title($records->count() . ' ' . str('enquiry')->plural($records->count()) . ' updated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('set_status')
                        ->label('Change status...')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->modalHeading('Change the status of the selected enquiries')
                        ->modalSubmitActionLabel('Change status')
                        ->schema([
                            Select::make('status')->label('New status')->options(Enquiry::STATUSES)->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each->update(['status' => $data['status']]);
                            Notification::make()->title($records->count() . ' ' . str('enquiry')->plural($records->count()) . ' updated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('export_selected')
                        ->label('Export selected')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->schema([
                            Select::make('format')->label('File type')
                                ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                                ->default('xlsx')->required()->native(false),
                        ])
                        ->action(fn ($records, array $data) => EnquiryExporter::download(Enquiry::whereKey($records->modelKeys()), $data['format'])),
                    DeleteBulkAction::make(),
                ])->label('Bulk actions'),
            ]);
    }
}
