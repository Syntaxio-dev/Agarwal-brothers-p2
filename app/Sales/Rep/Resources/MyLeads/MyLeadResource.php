<?php

namespace App\Sales\Rep\Resources\MyLeads;

use App\Sales\Models\SalesEnquiry;
use App\Sales\Rep\Actions\LeadFollowupAction;
use App\Sales\Rep\Resources\MyLeads\Pages\ListMyLeads;
use App\Sales\Support\LeadColumns;
use App\Sales\Support\SalesInsights;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "My leads": the signed-in rep's own leads (his enquiries, with their follow-up status, remark and next reminder). He logs a follow-up
 * from here ("Follow-up"); what he does reaches his phone on its next sync. Only his own rows: the list is scoped and the service checks the owner.
 */
class MyLeadResource extends Resource
{
    protected static ?string $model = SalesEnquiry::class;

    protected static ?string $slug = 'leads';

    protected static ?string $modelLabel = 'lead';

    protected static ?string $navigationLabel = 'Leads';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns(LeadColumns::columns(withRep: false))
            ->defaultSort(fn (Builder $query) => SalesInsights::leadsByRecent($query))
            ->filters([
                SelectFilter::make('lead_status')->label('Lead status')->options(SalesEnquiry::LEAD_STATUSES)->multiple(),
                Filter::make('no_reminder')->label('No reminder set')->query(fn (Builder $query) => $query->whereIn('status', SalesEnquiry::OPEN)->whereNull('next_followup_at')),
            ])
            ->recordAction('leadFollowup')
            ->recordActions([
                LeadFollowupAction::make()->button(),
                ViewAction::make()->label('Details')->modalHeading(fn (SalesEnquiry $record) => $record->client_name)->schema(LeadColumns::infolist(withRep: false)),
            ])
            ->emptyStateHeading('No leads here')
            ->emptyStateDescription('Every enquiry is a lead. Add one from Enquiries, or write one on your phone and sync.');
    }

    public static function getPages(): array
    {
        return ['index' => ListMyLeads::route('/')];
    }
}
