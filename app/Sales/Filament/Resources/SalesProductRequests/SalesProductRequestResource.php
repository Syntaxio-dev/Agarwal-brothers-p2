<?php

namespace App\Sales\Filament\Resources\SalesProductRequests;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesProductRequests\Pages\ListSalesProductRequests;
use App\Sales\Models\SalesProductRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** What the reps asked to have added or corrected in the product guide. The manager changes the product, then answers here. */
class SalesProductRequestResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-product-requests';

    protected static ?string $model = SalesProductRequest::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Guide requests';

    protected static ?string $modelLabel = 'guide request';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $open = SalesProductRequest::query()->where('status', 'open')->count();

        return $open > 0 ? (string) $open : null;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user.salesProfile', 'product']);
    }

    public static function table(Table $table): Table
    {
        $answer = fn (string $status, string $label, string $color, string $icon) => Action::make($status)
            ->label($label)
            ->color($color)
            ->icon($icon)
            ->visible(fn (SalesProductRequest $r) => $r->status === 'open')
            ->schema([Textarea::make('reply')->label('A short answer for the rep (optional)')->rows(2)->maxLength(500)])
            ->action(fn (SalesProductRequest $r, array $data) => $r->update([
                'status' => $status,
                'reply' => filled($data['reply'] ?? null) ? trim($data['reply']) : null,
                'handled_by' => auth()->id(),
                'handled_at' => now(),
            ]));

        return $table
            ->columns([
                TextColumn::make('created_at')->label('Asked')->since()->sortable(),
                TextColumn::make('user.name')->label('Rep')->description(fn (SalesProductRequest $r) => $r->user?->salesProfile?->employee_code)->searchable(),
                TextColumn::make('product_name')->label('Product')->searchable()->wrap(),
                TextColumn::make('body')->label('Request')->searchable()->wrap(),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (string $state) => SalesProductRequest::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) { 'done' => 'success', 'declined' => 'gray', default => 'warning' }),
                TextColumn::make('reply')->placeholder('—')->wrap()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(SalesProductRequest::STATUSES)->default('open'),
            ])
            ->recordActions([
                $answer('done', 'Done', 'success', 'heroicon-o-check'),
                $answer('declined', 'Decline', 'gray', 'heroicon-o-x-mark'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesProductRequests::route('/')];
    }
}
