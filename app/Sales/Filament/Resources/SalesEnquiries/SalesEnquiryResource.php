<?php

namespace App\Sales\Filament\Resources\SalesEnquiries;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesEnquiries\Pages\ListSalesEnquiries;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\Money;
use App\Sales\Support\SalesExporter;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * What the clients asked the reps for, and where each enquiry stands. Read only: the rep owns the enquiry and its follow-ups
 * on the phone; the manager looks, filters and exports.
 */
class SalesEnquiryResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-enquiries';

    protected static ?string $model = SalesEnquiry::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Enquiries';

    protected static ?string $modelLabel = 'enquiry';

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
        return parent::getEloquentQuery()->with('user');
    }

    private static function statusColor(string $status): string
    {
        return match ($status) {
            'won' => 'success',
            'lost' => 'gray',
            'new' => 'info',
            'callback', 'meeting_planned' => 'warning',
            default => 'primary',
        };
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(static::infolistComponents());
    }

    /** What the "Open" window shows. The view action takes these directly (it would otherwise show a disabled form). */
    public static function infolistComponents(): array
    {
        $tz = config('sales.timezone');

        return [
            Section::make()->columns(3)->schema([
                TextEntry::make('client_name')->label('Client'),
                TextEntry::make('contact_name')->label('Person')->placeholder('—'),
                TextEntry::make('user.name')->label('Rep'),
                TextEntry::make('status')->badge()->formatStateUsing(fn (string $state) => SalesEnquiry::STATUSES[$state] ?? $state)->color(fn (string $state) => self::statusColor($state)),
                TextEntry::make('source')->label('Came through')->placeholder('—')->formatStateUsing(fn (?string $state) => SalesEnquiry::SOURCES[$state] ?? $state),
                TextEntry::make('opened_at')->label('Opened')->dateTime('d M Y, h:i A', $tz),
                TextEntry::make('next_followup_at')->label('Next follow-up')->dateTime('d M Y, h:i A', $tz)->placeholder('—')
                    ->color(fn (SalesEnquiry $r) => $r->isOverdue() ? 'danger' : null),
            ]),
            Section::make('What they asked for')->schema([
                TextEntry::make('items')->hiddenLabel()->html()->state(function (SalesEnquiry $r) {
                    $rows = collect($r->items ?? [])->map(fn ($i) => '<tr><td style="padding:4px 12px 4px 0"><strong>' . e($i['product'] ?? '') . '</strong></td>'
                        . '<td style="padding:4px 12px">x ' . e((string) ($i['quantity'] ?? 1)) . '</td>'
                        . '<td style="padding:4px 12px">' . e($i['brands'] ?? '') . '</td>'
                        . '<td style="padding:4px 0 4px 12px;text-align:right">' . e(isset($i['budget_paise']) ? Money::rupees((int) $i['budget_paise']) : '') . '</td></tr>')->implode('');

                    return new HtmlString('<table style="width:100%;font-size:.9rem"><thead><tr style="text-align:left;opacity:.6"><th>Product</th><th>Qty</th><th>Preferred brands</th><th style="text-align:right">Budget</th></tr></thead><tbody>' . $rows . '</tbody></table>');
                }),
            ]),
            Section::make('Notes and status changes')->schema([
                TextEntry::make('timeline')->hiddenLabel()->html()->state(function (SalesEnquiry $r) use ($tz) {
                    $lines = collect($r->timeline ?? [])->reverse()->map(function ($t) use ($tz) {
                        $at = CarbonImmutable::parse($t['at'])->timezone($tz)->format('d M, h:i A');
                        $status = ! empty($t['status']) ? ' <em>(' . e(SalesEnquiry::STATUSES[$t['status']] ?? $t['status']) . ')</em>' : '';

                        return '<div style="padding:4px 0"><span style="opacity:.6">' . e($at) . '</span>' . $status . ' ' . e($t['text'] ?? '') . '</div>';
                    })->implode('');

                    return new HtmlString($lines ?: '<span style="opacity:.6">No notes yet.</span>');
                }),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('opened_at')->label('Opened')->dateTime('d M Y', $tz)->sortable(),
                TextColumn::make('user.name')->label('Rep')->searchable()->weight('medium'),
                TextColumn::make('client_name')->label('Client')->searchable()->description(fn (SalesEnquiry $r) => $r->contact_name),
                TextColumn::make('items_summary')->label('Asked for')->getStateUsing(fn (SalesEnquiry $r) => $r->itemsSummary())->wrap(),
                TextColumn::make('budget')->label('Budget')->alignEnd()->getStateUsing(fn (SalesEnquiry $r) => $r->budgetPaise() ?: null)
                    ->formatStateUsing(fn ($state) => Money::rupees((int) $state))->placeholder('—'),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => SalesEnquiry::STATUSES[$state] ?? $state)->color(fn (string $state) => self::statusColor($state))->sortable(),
                TextColumn::make('next_followup_at')->label('Follow-up')->dateTime('d M, h:i A', $tz)->placeholder('—')->sortable()
                    ->color(fn (SalesEnquiry $r) => $r->isOverdue() ? 'danger' : null)
                    ->description(fn (SalesEnquiry $r) => $r->isOverdue() ? 'Overdue' : null),
                TextColumn::make('source')->label('Came through')->badge()->color('gray')->placeholder('—')
                    ->formatStateUsing(fn (?string $state) => SalesEnquiry::SOURCES[$state] ?? $state)->toggleable(),
                TextColumn::make('updated_at')->label('Last change')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('opened_at', 'desc')
            ->filters([
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
                SelectFilter::make('status')->options(SalesEnquiry::STATUSES)->multiple(),
                SelectFilter::make('source')->label('Came through')->options(SalesEnquiry::SOURCES),
                Filter::make('open')->label('Still open')->query(fn (Builder $q) => $q->whereIn('status', SalesEnquiry::OPEN)),
                Filter::make('overdue')->label('Follow-up overdue')->query(fn (Builder $q) => $q->whereIn('status', SalesEnquiry::OPEN)->where('next_followup_at', '<', now())),
                Filter::make('date')
                    ->schema([DatePicker::make('from')->label('Opened from'), DatePicker::make('until')->label('Opened until')])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->where('opened_at', '>=', CarbonImmutable::parse($d, $tz)->startOfDay()->utc()))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->where('opened_at', '<=', CarbonImmutable::parse($d, $tz)->endOfDay()->utc()))),
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
                        Select::make('format')->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => SalesExporter::enquiries($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            // "Open" is the side panel of the board (the same window from the List)
            ->recordActions([Action::make('open')->label('Open')->icon(Heroicon::OutlinedEye)->action(fn (SalesEnquiry $record, $livewire) => $livewire->openEnquiry($record->uuid))])
            ->emptyStateHeading('No enquiries yet')
            ->emptyStateDescription('They appear here when a rep writes one in the app and syncs.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesEnquiries::route('/')];
    }
}
