<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Concerns\RestrictedByRole;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Read-only list of who changed what in the admin panel. Administrators only. */
class ActivityLogResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'activity-log';

    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Activity log';

    protected static ?string $modelLabel = 'activity';

    protected static ?string $pluralModelLabel = 'activity log';

    // Nothing here can be added, changed or removed by hand.
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

    /** The admin edit page of a record, when it still exists and this person may open it. */
    public static function subjectUrl(ActivityLog $log): ?string
    {
        if (! $log->subject_type || ! $log->subject_id || $log->action === 'deleted' || ! class_exists($log->subject_type)) {
            return null;
        }

        $resource = collect(\Filament\Facades\Filament::getResources())
            ->first(fn ($r) => $r::getModel() === $log->subject_type && array_key_exists('edit', $r::getPages()));

        if (! $resource || ! $log->subject_type::whereKey($log->subject_id)->exists()) {
            return null;
        }

        $record = $log->subject_type::find($log->subject_id);

        return $resource::canEdit($record) ? $resource::getUrl('edit', ['record' => $record]) : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->description(fn (ActivityLog $r) => $r->created_at->format('d M Y, h:i A'))
                    ->sortable(),
                TextColumn::make('user_name')
                    ->label('Who')
                    ->weight('medium')
                    ->searchable(),
                TextColumn::make('action')
                    ->label('Did')
                    ->badge()
                    ->formatStateUsing(fn (string $state, ActivityLog $r) => $r->actionLabel())
                    ->color(fn (string $state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'info',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_label')
                    ->label('What')
                    ->placeholder('—')
                    ->searchable()
                    ->description(fn (ActivityLog $r) => $r->typeLabel() ?: null)
                    ->url(fn (ActivityLog $r) => static::subjectUrl($r)),
                TextColumn::make('details')
                    ->label('Details')
                    ->state(fn (ActivityLog $r) => $r->summary())
                    ->color('gray')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Person')
                    ->options(fn () => User::orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('action')
                    ->options(['created' => 'Created', 'updated' => 'Updated', 'deleted' => 'Deleted', 'login' => 'Signed in']),
                SelectFilter::make('subject_type')
                    ->label('Kind of item')
                    ->options(fn () => ActivityLog::query()->whereNotNull('subject_type')->distinct()->pluck('subject_type')
                        ->mapWithKeys(fn ($t) => [$t => Str::headline(class_basename($t))])->sort()->all()),
                Filter::make('date')
                    ->schema([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([
                Action::make('details')
                    ->label('Details')
                    ->icon(Heroicon::OutlinedEye)
                    ->visible(fn (ActivityLog $r) => ! empty($r->details))
                    ->modalHeading(fn (ActivityLog $r) => $r->user_name . ' ' . strtolower($r->actionLabel()) . ' ' . strtolower($r->typeLabel()) . ' "' . $r->subject_label . '"')
                    ->modalContent(fn (ActivityLog $r) => view('filament.activity.changes', ['log' => $r]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->emptyStateHeading('Nothing recorded yet')
            ->emptyStateDescription('When someone on your team adds, changes or deletes something in the admin panel, it appears here.')
            ->poll(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
