<?php

namespace App\Sales\Rep\Resources\Notes;

use App\Sales\Models\SalesMemo;
use App\Sales\Rep\Resources\Notes\Pages\ManageNotes;
use App\Sales\Rep\Support\DeskUi;
use App\Sales\Rep\Support\DeskWork;
use App\Sales\Rep\Support\RepDesk;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** The rep's own notes (private; the same notes as on the phone). Writes go through DeskWork. */
class NoteResource extends Resource
{
    protected static ?string $model = SalesMemo::class;

    protected static ?string $slug = 'notes';

    protected static ?string $modelLabel = 'note';

    protected static ?string $navigationLabel = 'Notes';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static ?int $navigationSort = 8;

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
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    /** @return list<\Filament\Forms\Components\Field> */
    public static function fields(): array
    {
        return [
            TextInput::make('title')->label('Title (optional)')->maxLength(120)->regex('/^[^<>;{}\\\\]*$/u'),
            Textarea::make('body')->label('Note')->required()->rows(10)->maxLength(5000),
        ];
    }

    public static function addAction(): Action
    {
        return Action::make('add')->label('New note')->icon(Heroicon::OutlinedPlus)->modalWidth('2xl')
            ->schema(static::fields())
            ->action(fn (array $data) => DeskUi::attempt(fn () => app(DeskWork::class)->saveMemo(auth()->user(), null, $data['title'] ?? null, $data['body']), 'Note saved'));
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('title')->label('Note')->searchable(['title', 'body'])->weight('medium')
                    ->getStateUsing(fn (SalesMemo $m) => $m->title ?: \Illuminate\Support\Str::limit(strtok($m->body, "\n") ?: $m->body, 60))
                    ->description(fn (SalesMemo $m) => \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', $m->body), 120)),
                TextColumn::make('updated_at')->label('Changed')->dateTime('j M Y, h:i A', $tz)->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                Action::make('edit')->label('Open')->icon(Heroicon::OutlinedPencilSquare)->modalWidth('2xl')
                    ->fillForm(fn (SalesMemo $record) => ['title' => $record->title, 'body' => $record->body])
                    ->schema(static::fields())
                    ->action(fn (SalesMemo $record, array $data) => DeskUi::attempt(fn () => app(DeskWork::class)->saveMemo(auth()->user(), $record, $data['title'] ?? null, $data['body']), 'Note saved')),
                Action::make('delete')->label('Delete')->icon(Heroicon::OutlinedTrash)->color('danger')->requiresConfirmation()
                    ->action(fn (SalesMemo $record) => DeskUi::attempt(fn () => app(DeskWork::class)->deleteMemo(auth()->user(), $record), 'Note deleted')),
            ])
            ->emptyStateHeading('No notes yet')
            ->emptyStateDescription('Only you see your notes. They are the same as the Notes on your phone.');
    }

    public static function getPages(): array
    {
        return ['index' => ManageNotes::route('/')];
    }
}
