<?php

namespace App\Filament\Resources\ClientCategories;

use App\Filament\Concerns\RestrictedByRole;
use App\Filament\Resources\ClientCategories\Pages\CreateClientCategory;
use App\Filament\Resources\ClientCategories\Pages\EditClientCategory;
use App\Filament\Resources\ClientCategories\Pages\ListClientCategories;
use App\Models\ClientCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ClientCategoryResource extends Resource
{
    use RestrictedByRole;

    // same access as Clients: whoever manages clients manages their categories
    protected static string $accessKey = 'clients';

    protected static ?string $model = ClientCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Homepage';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Client categories';

    protected static ?string $modelLabel = 'client category';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(80)
                ->unique(ignoreRecord: true)
                ->helperText('For example Pharma, Dairy, Agro. Shown in the rotating list on the home page (only while it has active clients).'),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->helperText('Lower number comes first. You can also drag rows in the list.'),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium'),
                TextColumn::make('clients_count')->counts('clients')->label('Clients'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->emptyStateHeading('No categories yet')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalDescription('Categories that still have clients are skipped.')
                        ->failureNotificationTitle(fn (int $successCount, int $totalCount): string => $successCount
                            ? "Deleted {$successCount} of {$totalCount}. The rest still have clients."
                            : 'Nothing deleted: the selected categories still have clients.'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClientCategories::route('/'),
            'create' => CreateClientCategory::route('/create'),
            'edit' => EditClientCategory::route('/{record}/edit'),
        ];
    }
}
