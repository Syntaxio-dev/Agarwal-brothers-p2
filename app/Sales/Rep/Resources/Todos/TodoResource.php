<?php

namespace App\Sales\Rep\Resources\Todos;

use App\Sales\Models\SalesTodo;
use App\Sales\Rep\Resources\Todos\Pages\ManageTodos;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\DeskWork;
use App\Sales\Rep\Support\RepDesk;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** The rep's own to-do list (private; the same list as on the phone). Writes go through DeskWork. */
class TodoResource extends Resource
{
    protected static ?string $model = SalesTodo::class;

    protected static ?string $slug = 'todo';

    protected static ?string $modelLabel = 'to-do';

    protected static ?string $pluralModelLabel = 'to-do list';

    protected static ?string $navigationLabel = 'To-do';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?int $navigationSort = 7;

    public static function canAccess(): bool
    {
        return RepDesk::allows(auth()->user(), 'notes');
    }

    // the office's policies are written for managers; on the desk the area and the list's own scope decide
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;   // "Add a to-do" is a header action that writes through DeskWork
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
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    /** @return list<\Filament\Forms\Components\Field> */
    public static function fields(): array
    {
        return [
            TextInput::make('title')->label('What to do')->required()->minLength(2)->maxLength(200)->regex('/^[^<>;{}\\\\]+$/u'),
            DatePicker::make('due_date')->label('By (optional)')->native(false)->minDate(now()->subYears(1))->maxDate(now()->addYears(2)),
        ];
    }

    public static function addAction(): Action
    {
        return Action::make('add')->label('Add a to-do')->icon(Heroicon::OutlinedPlus)
            ->schema(static::fields())
            ->action(fn (array $data) => DeskUi::attempt(fn () => app(DeskWork::class)->saveTodo(auth()->user(), null, $data['title'], $data['due_date'] ?? null), 'Added'));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('done')->label('')->boolean()->trueIcon(Heroicon::CheckCircle)->falseIcon(Heroicon::OutlinedStopCircle)
                    ->action(Action::make('toggle')->action(fn (SalesTodo $record) => DeskUi::attempt(
                        fn () => app(DeskWork::class)->setTodoDone(auth()->user(), $record, ! $record->done),
                    ))),
                TextColumn::make('title')->label('To-do')->searchable()->weight(fn (SalesTodo $t) => $t->done ? null : 'medium')
                    ->color(fn (SalesTodo $t) => $t->done ? 'gray' : null),
                TextColumn::make('due_date')->label('By')->date('D j M Y')->placeholder('—')->sortable()
                    ->color(fn (SalesTodo $t) => ! $t->done && $t->due_date && $t->due_date->lt(today()) ? 'danger' : null),
            ])
            ->defaultSort(fn (Builder $query) => $query->orderBy('done')->orderByRaw('due_date IS NULL')->orderBy('due_date')->orderByDesc('id'))
            ->recordActions([
                Action::make('edit')->label('Edit')->icon(Heroicon::OutlinedPencilSquare)
                    ->fillForm(fn (SalesTodo $record) => ['title' => $record->title, 'due_date' => $record->due_date])
                    ->schema(static::fields())
                    ->action(fn (SalesTodo $record, array $data) => DeskUi::attempt(fn () => app(DeskWork::class)->saveTodo(auth()->user(), $record, $data['title'], $data['due_date'] ?? null), 'Saved')),
                Action::make('delete')->label('Delete')->icon(Heroicon::OutlinedTrash)->color('danger')->requiresConfirmation()
                    ->action(fn (SalesTodo $record) => DeskUi::attempt(fn () => app(DeskWork::class)->deleteTodo(auth()->user(), $record), 'Deleted')),
            ])
            ->emptyStateHeading('Nothing on your list')
            ->emptyStateDescription('Only you see this list. It is the same as the To-do on your phone.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageTodos::route('/')];
    }
}
