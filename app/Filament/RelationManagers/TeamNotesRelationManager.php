<?php

namespace App\Filament\RelationManagers;

use App\Models\Note;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Internal notes and activity log on an enquiry, message or application. Customers never see it. */
class TeamNotesRelationManager extends RelationManager
{
    protected static string $relationship = 'teamNotes';

    protected static ?string $title = 'Team notes & activity';

    protected static ?string $recordTitleAttribute = 'body';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->teamNotes()->where('kind', Note::KIND_NOTE)->count();

        return $count ? (string) $count : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('body')
                ->label('Note')
                ->placeholder('For your team only: what was discussed, what to do next...')
                ->required()
                ->rows(4)
                ->maxLength(2000)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->description('Visible to your team only. Status changes, assignments and e-mails are logged here automatically.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->description(fn (Note $n) => $n->created_at->format('d M Y, h:i A'))
                    ->width('11rem'),
                TextColumn::make('user.name')
                    ->label('By')
                    ->placeholder('Automatic')
                    ->width('9rem'),
                TextColumn::make('kind')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === Note::KIND_NOTE ? 'Note' : 'Activity')
                    ->color(fn (string $state) => $state === Note::KIND_NOTE ? 'primary' : 'gray')
                    ->width('6rem'),
                TextColumn::make('body')
                    ->label('Details')
                    ->wrap()
                    ->limit(600)
                    ->color(fn (Note $n) => $n->isSystem() ? 'gray' : null),
            ])
            ->defaultSort('id', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Add note')
                    ->modalHeading('Add a note for your team')
                    ->mutateDataUsing(fn (array $data) => $data + ['user_id' => auth()->id(), 'kind' => Note::KIND_NOTE]),
            ])
            ->recordActions([
                // Only the author (or an administrator) may remove a written note. Automatic log lines stay.
                DeleteAction::make()
                    ->visible(fn (Note $n) => ! $n->isSystem() && ($n->user_id === auth()->id() || auth()->user()?->isAdmin())),
            ])
            ->emptyStateHeading('No notes yet')
            ->emptyStateDescription('Add a note to keep your team in the loop.');
    }
}
