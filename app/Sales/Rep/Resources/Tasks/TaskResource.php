<?php

namespace App\Sales\Rep\Resources\Tasks;

use App\Sales\Models\SalesTask;
use App\Sales\Rep\Resources\Tasks\Pages\ListTasks;
use App\Sales\Rep\Resources\Tasks\Pages\ViewTask;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\DeskWork;
use App\Sales\Rep\Support\RepDesk;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "My tasks": the work the manager gave this rep (the same tasks as on the phone). The rep reads it, downloads the files and sets
 * Started / Done / Cannot do with a note; the manager's own fields cannot be changed here. Writes go through DeskWork.
 */
class TaskResource extends Resource
{
    protected static ?string $model = SalesTask::class;

    protected static ?string $slug = 'tasks';

    protected static ?string $modelLabel = 'task';

    protected static ?string $navigationLabel = 'Tasks';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 6;

    public static function canAccess(): bool
    {
        return RepDesk::allows(auth()->user(), 'tasks');
    }

    // the office's policies are written for managers; on the desk the area and the list's own scope decide
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView(Model $record): bool
    {
        return static::canAccess() && (int) $record->assigned_to === (int) auth()->id();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('assigned_to', auth()->id())->with(['creator'])->withCount('files');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! static::canAccess()) {
            return null;
        }
        $new = SalesTask::where('assigned_to', auth()->id())->where('status', 'new')->count();

        return $new ? (string) $new : null;
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('title')->label('Task')->searchable()->weight('medium')
                    ->description(fn (SalesTask $t) => 'From ' . ($t->creator?->name ?? 'the office')),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state) => SalesTask::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'new' => 'info', 'in_progress' => 'warning', 'done' => 'success', 'blocked' => 'danger', default => 'gray' }),
                TextColumn::make('priority')->label('Priority')->formatStateUsing(fn (string $state) => SalesTask::PRIORITIES[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'high' ? 'danger' : null),
                TextColumn::make('due_at')->label('Due')->dateTime('D j M Y, h:i A', $tz)->placeholder('—')->sortable()
                    ->color(fn (SalesTask $t) => $t->isOverdue() ? 'danger' : null),
                TextColumn::make('files_count')->label('Files')->alignEnd(),
            ])
            ->defaultSort('due_at')
            ->recordUrl(fn (SalesTask $t) => static::getUrl('view', ['record' => $t]))
            ->recordActions([static::updateAction()])
            ->emptyStateHeading('No tasks here')
            ->emptyStateDescription('When your manager gives you a task it shows here and on your phone.');
    }

    /** The rep's one change: the status and a note for the manager. */
    public static function updateAction(): Action
    {
        return Action::make('update')->label('Update')->icon(Heroicon::OutlinedCheckCircle)
            ->visible(fn (SalesTask $record) => $record->status !== 'cancelled' && ! $record->trashed())
            ->modalHeading(fn (SalesTask $record) => $record->title)
            ->fillForm(fn (SalesTask $record) => [
                'status' => in_array($record->status, SalesTask::REP_STATUSES, true) ? $record->status : 'in_progress',
                'rep_note' => $record->rep_note,
            ])
            ->schema([
                \Filament\Forms\Components\ToggleButtons::make('status')->label('Where is it?')->required()->live()->inline()
                    ->colors(['in_progress' => 'warning', 'done' => 'success', 'blocked' => 'danger'])
                    ->options(array_intersect_key(SalesTask::STATUSES, array_flip(SalesTask::REP_STATUSES))),
                Textarea::make('rep_note')->label(fn (Get $get) => $get('status') === 'blocked' ? 'Why can you not do it?' : 'Note for the manager (optional)')
                    ->rows(3)->maxLength(500)->required(fn (Get $get) => $get('status') === 'blocked'),
            ])
            ->action(fn (SalesTask $record, array $data) => DeskUi::attempt(
                fn () => app(DeskWork::class)->updateTask(auth()->user(), $record, $data['status'], $data['rep_note'] ?? null),
                'Task updated',
            ));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'view' => ViewTask::route('/{record}'),
        ];
    }
}
