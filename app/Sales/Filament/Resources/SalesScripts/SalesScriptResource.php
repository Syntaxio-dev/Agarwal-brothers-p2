<?php

namespace App\Sales\Filament\Resources\SalesScripts;

use App\Filament\Concerns\RestrictedByRole;
use App\Sales\Filament\Resources\SalesScripts\Pages\ListSalesScripts;
use App\Sales\Models\SalesScript;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** What the reps wrote as their own pitch for a product. Read only: it is the rep's own words (a manager may remove one). */
class SalesScriptResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'sales-products';

    protected static ?string $model = SalesScript::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|\UnitEnum|null $navigationGroup = 'Sales team';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'Rep scripts';

    protected static ?string $modelLabel = 'script';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user.salesProfile']);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')->label('Rep'),
            TextEntry::make('product_name')->label('Product'),
            TextEntry::make('title')->placeholder('—'),
            TextEntry::make('body')->label('Script')->columnSpanFull()->prose(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('updated_at')->label('Written')->since()->sortable(),
                TextColumn::make('user.name')->label('Rep')->description(fn (SalesScript $r) => $r->user?->salesProfile?->employee_code)->searchable(),
                TextColumn::make('product_name')->label('Product')->searchable()->wrap(),
                TextColumn::make('title')->placeholder('—')->wrap()->toggleable(),
                TextColumn::make('body')->label('Script')->limit(120)->wrap()->searchable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()->modalDescription('The script leaves the rep\'s phone at the next sync.'),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListSalesScripts::route('/')];
    }
}
