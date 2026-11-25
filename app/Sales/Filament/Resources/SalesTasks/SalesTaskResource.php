<?php

namespace App\Sales\Filament\Resources\SalesTasks;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use App\Sales\Filament\Resources\SalesTasks\Pages\CreateSalesTask;
use App\Sales\Filament\Resources\SalesTasks\Pages\EditSalesTask;
use App\Sales\Filament\Resources\SalesTasks\Pages\ListSalesTasks;
use App\Sales\Models\SalesTask;
use App\Sales\Models\SalesTaskFile;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Tasks for the field reps (app v1.20): the manager gives a rep work with the files they need and an e-mail to send the result to ("make the
 * quotation from these specs and send it to this address"), and checks the status the rep sets on the phone (Started / Done / Cannot do, with a
 * note). A task reaches the phone at its next sync. The files live on the private disk.
 */
class SalesTaskResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-tasks';

    protected static ?string $model = SalesTask::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?string $modelLabel = 'task';

    protected static ?string $recordTitleAttribute = 'title';

    /** The number on the sidebar: tasks that are open and late. */
    public static function getNavigationBadge(): ?string
    {
        $late = SalesTask::query()->whereIn('status', SalesTask::OPEN)->whereNotNull('due_at')->where('due_at', '<', now())->count();

        return $late > 0 ? (string) $late : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['assignee.salesProfile', 'files'])->withCount('files');
    }

    public static function form(Schema $schema): Schema
    {
        $types = array_values(array_unique(array_values(SalesTaskFile::TYPES)));

        return $schema->components([
            Select::make('assigned_to')->label('Give to')
                ->options(fn () => SalesClientResource::repOptions())
                ->required()->searchable()->native(false)
                ->disabled(fn ($livewire) => $livewire instanceof EditSalesTask)
                ->helperText('The rep sees the task on the phone at the next sync.'),
            TextInput::make('title')->label('Task')->required()->maxLength(200)->placeholder('For example: Quotation for Greenlam, 2 muffle furnaces')->columnSpanFull(),
            Textarea::make('description')->label('What to do')->rows(6)->maxLength(5000)->columnSpanFull()
                ->helperText('Write the details: what is needed, quantities, brands, the price to quote, anything the rep must know.'),
            TextInput::make('send_to_email')->label('Send the result to (e-mail)')->email()->maxLength(150)
                ->helperText('The rep sees this address on the task and sends the finished work (for example the quotation) there from the phone.'),
            DateTimePicker::make('due_at')->label('Due')->native(false)->seconds(false)->timezone(config('sales.timezone')),
            Select::make('priority')->options(SalesTask::PRIORITIES)->default('normal')->required()->native(false),
            Select::make('status')->options(SalesTask::STATUSES)->native(false)
                ->visible(fn ($livewire) => $livewire instanceof EditSalesTask)
                ->helperText('The rep moves it from New to Started, Done or Cannot do. You can cancel it or open it again.'),
            View::make('filament.sales.task-files')
                ->visible(fn ($livewire) => $livewire instanceof EditSalesTask)
                ->viewData(fn ($livewire) => ['files' => $livewire->getRecord()?->files ?? collect()])
                ->columnSpanFull(),
            FileUpload::make('attachments')->label('Files for the rep')
                ->multiple()->maxFiles(SalesTask::MAX_FILES)->maxSize(SalesTaskFile::MAX_KB)
                ->disk('local')->directory('sales/task-uploads')->visibility('private')
                ->acceptedFileTypes($types)->storeFileNamesIn('attachment_names')
                ->dehydrated(false)
                ->helperText('Up to ' . SalesTask::MAX_FILES . ' files, 5 MB each: PDF, picture, Excel, Word, CSV or text. The rep opens them on the phone.')
                ->columnSpanFull(),
        ])->columns(2);
    }

    /**
     * Turns what was uploaded in the form into files of the task: each file is judged by its extension AND its first bytes, stored under a random
     * name on the private disk, and the record keeps the name the manager's file had. A file that is not an allowed kind is dropped with a message.
     *
     * @param  array<string, mixed>  $data  the raw form state ('attachments' = stored paths, 'attachment_names' = path => original name)
     */
    public static function attachUploads(SalesTask $task, array $data): void
    {
        $paths = array_values(array_filter((array) ($data['attachments'] ?? []), 'is_string'));
        $names = (array) ($data['attachment_names'] ?? []);
        $room = SalesTask::MAX_FILES - $task->files()->count();

        foreach ($paths as $path) {
            // only what this form put in its own upload folder is ever read
            if (! str_starts_with($path, 'sales/task-uploads/') || str_contains($path, '..') || ! Storage::disk('local')->exists($path)) {
                continue;
            }

            $bytes = Storage::disk('local')->get($path);
            $name = SalesTaskFile::cleanName((string) ($names[$path] ?? basename($path)));
            $mime = SalesTaskFile::typeOf($name, $bytes);

            if ($mime === null || strlen($bytes) > SalesTaskFile::MAX_KB * 1024 || $room <= 0) {
                Storage::disk('local')->delete($path);
                Notification::make()->title($name . ' was not added')->body($room <= 0 ? 'A task holds at most ' . SalesTask::MAX_FILES . ' files.' : 'Only PDF, picture, Excel, Word, CSV or text files up to 5 MB are accepted.')->warning()->send();

                continue;
            }

            $final = 'sales/tasks/' . $task->id . '/' . Str::uuid() . '.' . strtolower(pathinfo($name, PATHINFO_EXTENSION));
            Storage::disk('local')->put($final, $bytes);
            Storage::disk('local')->delete($path);

            $task->files()->create(['name' => $name, 'mime' => $mime, 'size' => strlen($bytes), 'path' => $final]);
            $room--;
        }

        // a new file is a change the phone must hear about
        if ($paths) {
            $task->touch();
            $task->forceFill(['version' => (int) $task->version + 1])->saveQuietly();
        }
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('title')->label('Task')->searchable()->wrap()->weight('medium')
                    ->description(fn (SalesTask $r) => $r->send_to_email ? 'Send to ' . $r->send_to_email : null),
                TextColumn::make('assignee.name')->label('Rep')->searchable()
                    ->description(fn (SalesTask $r) => $r->assignee?->salesProfile?->employee_code),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (string $state) => SalesTask::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'done' => 'success', 'in_progress' => 'info', 'blocked' => 'danger', 'cancelled' => 'gray', default => 'warning' })
                    ->sortable(),
                TextColumn::make('due_at')->label('Due')->dateTime('d M, h:i A', $tz)->placeholder('No date')->sortable()
                    ->color(fn (SalesTask $r) => $r->isOverdue() ? 'danger' : null)
                    ->description(fn (SalesTask $r) => $r->isOverdue() ? 'Late' : null),
                TextColumn::make('priority')->badge()->formatStateUsing(fn (string $state) => SalesTask::PRIORITIES[$state] ?? $state)
                    ->color(fn (string $state) => $state === 'high' ? 'danger' : 'gray')->toggleable(),
                TextColumn::make('rep_note')->label("Rep's note")->limit(60)->tooltip(fn (SalesTask $r) => $r->rep_note)->placeholder('—')->wrap(),
                TextColumn::make('files_count')->label('Files')->alignCenter(),
                TextColumn::make('rep_updated_at')->label('Rep moved it')->since()->placeholder('Not yet')->sortable(),
                TextColumn::make('created_at')->label('Given')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('assigned_to')->label('Rep')->options(fn () => SalesClientResource::repOptions()),
                SelectFilter::make('status')->options(SalesTask::STATUSES)->multiple()->default(SalesTask::OPEN),
                Filter::make('late')->label('Late')->query(fn (Builder $q) => $q->whereIn('status', SalesTask::OPEN)->whereNotNull('due_at')->where('due_at', '<', now())),
            ])
            ->recordAction('details')
            ->recordActions([
                Action::make('details')->label('Open')->icon(Heroicon::OutlinedEye)
                    ->modalHeading(fn (SalesTask $r) => $r->title)
                    ->modalContent(fn (SalesTask $r) => view('filament.sales.task-details', ['task' => $r->load(['assignee.salesProfile', 'creator', 'files'])]))
                    ->modalSubmitAction(false)->modalCancelActionLabel('Close')->slideOver(),
                EditAction::make(),
                Action::make('cancelTask')->label('Cancel task')->icon(Heroicon::OutlinedXMark)->color('gray')->requiresConfirmation()
                    ->modalDescription('The rep sees it as cancelled and can no longer change it.')
                    ->visible(fn (SalesTask $r) => $r->status !== 'cancelled')
                    ->action(fn (SalesTask $r) => $r->update(['status' => 'cancelled'])),
                Action::make('reopen')->label('Open again')->icon(Heroicon::OutlinedArrowUturnLeft)->color('gray')
                    ->visible(fn (SalesTask $r) => in_array($r->status, ['done', 'cancelled'], true))
                    ->action(fn (SalesTask $r) => $r->update(['status' => 'new', 'rep_note' => null])),
                DeleteAction::make()->modalDescription('The task and its files are removed. The rep no longer sees it.')
                    ->before(fn (SalesTask $r) => $r->files->each->delete()),
            ])
            ->emptyStateHeading('No tasks yet')
            ->emptyStateDescription('Give a rep some work with "New task". It reaches their phone at the next sync.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesTasks::route('/'),
            'create' => CreateSalesTask::route('/create'),
            'edit' => EditSalesTask::route('/{record}/edit'),
        ];
    }
}
