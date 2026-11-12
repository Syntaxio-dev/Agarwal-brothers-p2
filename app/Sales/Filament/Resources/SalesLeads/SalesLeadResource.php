<?php

namespace App\Sales\Filament\Resources\SalesLeads;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesLeads\Pages\ListSalesLeads;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Models\SalesProfile;
use App\Sales\Support\LeadColumns;
use App\Sales\Support\SalesExporter;
use App\Sales\Support\SalesInsights;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Leads: every rep's follow-ups in one place for the manager. A lead is an enquiry; the rep writes the follow-up status, the remark
 * and the next reminder on the phone (or on his desk) and it arrives here. Read only; the four chips (Today / Last 7 days / Last month /
 * All) are the same views the reps have.
 */
class SalesLeadResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-leads';

    protected static ?string $model = SalesEnquiry::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Leads';

    protected static ?string $modelLabel = 'lead';

    protected static ?string $slug = 'sales-leads';

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns(LeadColumns::columns(withRep: true))
            ->defaultSort(fn (Builder $query) => SalesInsights::leadsByRecent($query))
            ->filters([
                SelectFilter::make('user_id')->label('Rep')
                    ->options(fn () => SalesProfile::with('user')->get()->pluck('user.name', 'user_id')->sort()->all()),
                SelectFilter::make('lead_status')->label('Lead status')->options(SalesEnquiry::LEAD_STATUSES)->multiple(),
                Filter::make('no_reminder')->label('No reminder set')->query(fn (Builder $query) => $query->whereIn('status', SalesEnquiry::OPEN)->whereNull('next_followup_at')),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->modalHeading('Export leads')
                    ->modalDescription('Downloads every lead that matches the chip, filters and search you have set now.')
                    ->modalSubmitActionLabel('Download')
                    ->fillForm(['format' => 'xlsx'])
                    ->schema([
                        Select::make('format')->label('File type')
                            ->options(['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV (works everywhere)'])
                            ->required()->native(false),
                    ])
                    ->action(fn (array $data, $livewire) => SalesExporter::enquiries($livewire->getFilteredTableQuery(), $data['format'])),
            ])
            ->recordAction('view')
            ->recordActions([
                ViewAction::make()->label('Open')->modalHeading(fn (SalesEnquiry $record) => $record->client_name)->schema(LeadColumns::infolist(withRep: true)),
            ])
            ->emptyStateHeading('No leads yet')
            ->emptyStateDescription('They appear here when a rep writes an enquiry and syncs.');
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesLeads::route('/')];
    }
}
