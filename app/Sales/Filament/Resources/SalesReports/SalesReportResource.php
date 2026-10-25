<?php

namespace App\Sales\Filament\Resources\SalesReports;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesReports\Pages\ListSalesReports;
use App\Sales\Models\SalesDailyReport;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\Money;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
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

/** The reps' daily reports: what each rep did and wrote today, and who has not sent one. Read only. */
class SalesReportResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-reports';

    protected static ?string $model = SalesDailyReport::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Daily reports';

    protected static ?string $modelLabel = 'daily report';

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
        return parent::getEloquentQuery()->with('user')->whereNotNull('submitted_at');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(static::infolistComponents());
    }

    /** What the "Open" window shows. The view action takes these directly (it would otherwise show a disabled form). */
    public static function infolistComponents(): array
    {
        return [
            Section::make()->columns(3)->schema([
                TextEntry::make('report_date')->label('Day')->date('d M Y'),
                TextEntry::make('user.name')->label('Rep'),
                TextEntry::make('submitted_at')->label('Sent')->dateTime('d M, h:i A', config('sales.timezone')),
            ]),
            Section::make('Numbers for the day (from the rep\'s own records)')->columns(3)->schema([
                TextEntry::make('f_calls')->label('Client calls')->state(fn (SalesDailyReport $r) => (int) ($r->figures['calls'] ?? 0) . ' (' . (int) ($r->figures['calls_missed'] ?? 0) . ' missed)'),
                TextEntry::make('f_talk')->label('Talk time')->state(fn (SalesDailyReport $r) => SalesInsights::duration((int) ($r->figures['talk_seconds'] ?? 0))),
                TextEntry::make('f_meetings')->label('Meetings')->state(fn (SalesDailyReport $r) => (int) ($r->figures['meetings'] ?? 0)),
                TextEntry::make('f_new')->label('New enquiries')->state(fn (SalesDailyReport $r) => (int) ($r->figures['enquiries_new'] ?? 0)),
                TextEntry::make('f_worked')->label('Enquiries worked on')->state(fn (SalesDailyReport $r) => (int) ($r->figures['enquiries_worked'] ?? 0)),
                TextEntry::make('f_spent')->label('Expenses')->state(fn (SalesDailyReport $r) => Money::rupees((int) ($r->figures['expenses_paise'] ?? 0))),
            ]),
            Section::make('Enquiries worked on that day')->schema([
                TextEntry::make('worked')->hiddenLabel()->html()->state(function (SalesDailyReport $r) {
                    $rows = SalesInsights::enquiriesWorked($r->user_id, $r->report_date->toDateString())
                        ->map(fn ($e) => '<div style="padding:3px 0"><strong>' . e($e->client_name) . '</strong> - ' . e($e->itemsSummary()) . ' <em>(' . e($e->statusLabel()) . ')</em></div>')->implode('');

                    return new HtmlString($rows ?: '<span style="opacity:.6">None recorded.</span>');
                }),
            ]),
            Section::make('In the rep\'s words')->schema([
                TextEntry::make('notes')->label('What happened')->placeholder('—')->columnSpanFull(),
                TextEntry::make('tomorrow_plan')->label('Plan for tomorrow')->placeholder('—')->columnSpanFull(),
                TextEntry::make('issues')->label('Needs help with')->placeholder('—')->columnSpanFull(),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_date')->label('Day')->date('d M Y')->sortable(),
                TextColumn::make('user.name')->label('Rep')->searchable()->weight('medium'),
                TextColumn::make('calls')->label('Calls')->alignCenter()->getStateUsing(fn (SalesDailyReport $r) => (int) ($r->figures['calls'] ?? 0)),
                TextColumn::make('meetings')->label('Meetings')->alignCenter()->getStateUsing(fn (SalesDailyReport $r) => (int) ($r->figures['meetings'] ?? 0)),
                TextColumn::make('enquiries')->label('Enquiries')->alignCenter()->getStateUsing(fn (SalesDailyReport $r) => (int) ($r->figures['enquiries_new'] ?? 0) . ' new / ' . (int) ($r->figures['enquiries_worked'] ?? 0) . ' worked'),
                TextColumn::make('notes')->limit(70)->tooltip(fn (SalesDailyReport $r) => $r->notes)->placeholder('—')->wrap(),
                TextColumn::make('issues')->label('Needs help')->limit(40)->tooltip(fn (SalesDailyReport $r) => $r->issues)->placeholder('—')->color('warning'),
                TextColumn::make('submitted_at')->label('Sent')->dateTime('h:i A', config('sales.timezone'))->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('report_date', 'desc')
            ->filters([
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
                Filter::make('needs_help')->label('Asks for help')->query(fn (Builder $q) => $q->whereNotNull('issues')->where('issues', '!=', '')),
                Filter::make('date')
                    ->schema([DatePicker::make('from')->label('From'), DatePicker::make('until')->label('Until')])
                    ->columns(2)
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('report_date', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('report_date', '<=', $d))),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Export daily reports')
                    ->modalDescription('Downloads every report that matches the filters and search you have set now.')
                    ->modalSubmitActionLabel('Download')
                    ->fillForm(['format' => 'xlsx'])
                    ->schema([
                        Select::make('format')->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => SalesExporter::reports($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            ->recordActions([ViewAction::make()->label('Open')->schema(static::infolistComponents())])
            ->emptyStateHeading('No reports yet')
            ->emptyStateDescription('A rep sends one from Home > Today\'s report in the app.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesReports::route('/')];
    }
}
