<?php

namespace App\Sales\Rep\Resources\MyEnquiries;

use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use App\Sales\Models\SalesEnquiry;
use App\Sales\Rep\Actions\LeadFollowupAction;
use App\Sales\Rep\Resources\MyEnquiries\Pages\CreateMyEnquiry;
use App\Sales\Rep\Resources\MyEnquiries\Pages\EditMyEnquiry;
use App\Sales\Rep\Resources\MyEnquiries\Pages\ListMyEnquiries;
use App\Sales\Support\Money;
use App\Sales\Support\RepEnquiryService;
use App\Sales\Support\SalesInsights;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * "My enquiries": the signed-in rep's own enquiries, the same records as on his phone. He adds an enquiry, changes its stage, sets the next
 * follow-up and writes notes; everything goes through RepEnquiryService (the phone's rules, a newer version for the next sync).
 * A rep can never see or open another rep's enquiry (the list is scoped and the policy checks the owner).
 */
class MyEnquiryResource extends Resource
{
    protected static ?string $model = SalesEnquiry::class;

    protected static ?string $slug = 'enquiries';

    protected static ?string $modelLabel = 'enquiry';

    protected static ?string $pluralModelLabel = 'enquiries';

    protected static ?string $navigationLabel = 'Enquiries';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('client_uuid')->label('Client')->required()->searchable()->native(false)->live()
                ->getSearchResultsUsing(fn (string $search) => static::clients($search))
                ->getOptionLabelUsing(fn ($value) => SalesClient::query()->visibleTo(auth()->user())->where('uuid', $value)->value('name'))
                ->options(fn () => static::clients('')),
            Select::make('contact_uuid')->label('Person (optional)')->searchable()->native(false)->placeholder('Nobody in particular')
                ->options(fn (Get $get) => $get('client_uuid')
                    ? SalesContact::query()->visibleTo(auth()->user())->where('client_uuid', $get('client_uuid'))->orderBy('name')->pluck('name', 'uuid')->all()
                    : []),
            Select::make('status')->label('Stage')->options(SalesEnquiry::STATUSES)->default('qualification')->required()->native(false),
            Select::make('source')->label('Came through')->options(SalesEnquiry::SOURCES)->native(false)->placeholder('Not said'),
            Repeater::make('items')->label('Products asked for')->minItems(1)->maxItems(10)->defaultItems(1)->columnSpanFull()->addActionLabel('Add a product')
                ->schema([
                    TextInput::make('product')->label('Product')->required()->minLength(2)->maxLength(150),
                    TextInput::make('brands')->label('Preferred brands')->maxLength(150),
                    TextInput::make('quantity')->label('Quantity')->numeric()->minValue(1)->maxValue(100000)->default(1)->required(),
                    TextInput::make('budget_rupees')->label('Budget (₹)')->numeric()->minValue(0)->maxValue(1000000000),
                ])->columns(4),
            DateTimePicker::make('next_followup_at')->label('Next follow-up')->seconds(false)->native(false)->timezone(config('sales.timezone'))
                ->helperText('Up to two years ahead. A closed enquiry has no follow-up.'),
            Textarea::make('note')->label('Note (goes on the timeline)')->rows(2)->maxLength(500)->dehydrated(true),
            View::make('filament.sales.rep.timeline')->columnSpanFull()->visible(fn (?Model $record) => $record !== null),
        ])->columns(2);
    }

    /** The clients this rep may see (his own, the ones given to him, the ones opened for him), found by name. @return array<string,string> */
    private static function clients(string $search): array
    {
        return SalesClient::query()->visibleTo(auth()->user())
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            ->orderBy('name')->limit(50)->pluck('name', 'uuid')->all();
    }

    public static function table(Table $table): Table
    {
        $tz = config('sales.timezone');

        return $table
            ->columns([
                TextColumn::make('client_name')->label('Client')->searchable()->weight('medium')->description(fn (SalesEnquiry $r) => $r->itemsSummary(2)),
                TextColumn::make('status')->label('Stage')->badge()
                    ->formatStateUsing(fn (string $state) => SalesEnquiry::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'won' => 'success', 'lost' => 'gray', 'qualification' => 'info', 'need_analysis' => 'warning', default => 'primary' })
                    ->sortable(),
                TextColumn::make('budget')->label('Budget')->alignEnd()
                    ->getStateUsing(fn (SalesEnquiry $r) => $r->budgetPaise() > 0 ? Money::short($r->budgetPaise()) : null)->placeholder('—'),
                TextColumn::make('next_followup_at')->label('Follow-up')->dateTime('d M Y, h:i A', $tz)->placeholder('—')->sortable()
                    ->color(fn (SalesEnquiry $r) => $r->isOpen() && $r->next_followup_at?->isPast() ? 'danger' : null),
                TextColumn::make('source')->label('Came through')->formatStateUsing(fn (?string $s) => SalesEnquiry::SOURCES[$s] ?? null)->placeholder('—')->toggleable(),
                TextColumn::make('opened_at')->label('Opened')->dateTime('d M Y', $tz)->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('next_followup_at')
            ->filters([SelectFilter::make('status')->label('Stage')->options(SalesEnquiry::STATUSES)->multiple()])
            ->recordUrl(fn (SalesEnquiry $r) => static::getUrl('edit', ['record' => $r]))
            ->recordActions([
                Action::make('stage')->label('Stage')->icon(Heroicon::OutlinedArrowsRightLeft)
                    ->modalHeading(fn (SalesEnquiry $r) => 'Move ' . $r->client_name)
                    ->fillForm(fn (SalesEnquiry $r) => ['to' => $r->status])
                    ->schema([
                        Select::make('to')->label('New stage')->options(SalesEnquiry::STATUSES)->required()->native(false),
                        Textarea::make('note')->label('Note (optional)')->rows(2)->maxLength(500),
                    ])
                    ->action(function (SalesEnquiry $record, array $data) {
                        app(RepEnquiryService::class)->move($record, auth()->user(), $data['to'], $data['note'] ?? null);
                        Notification::make()->title('Moved to ' . SalesEnquiry::STATUSES[$data['to']])->success()->send();
                    }),
                // the follow-up (status, remark, next reminder) is one thing, shared with My leads
                LeadFollowupAction::make()->color('gray'),
            ])
            ->emptyStateHeading('No enquiries here')
            ->emptyStateDescription('Add one with "New enquiry". Enquiries written on your phone appear after its next sync.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMyEnquiries::route('/'),
            'create' => CreateMyEnquiry::route('/create'),
            'edit' => EditMyEnquiry::route('/{record}/edit'),
        ];
    }
}
