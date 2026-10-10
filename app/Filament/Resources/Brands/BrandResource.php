<?php

namespace App\Filament\Resources\Brands;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\EditBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Filament\Resources\Brands\Schemas\BrandForm;
use App\Filament\Resources\Brands\Tables\BrandsTable;
use App\Models\Brand;
use App\Filament\Concerns\RestrictedByRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'brands';

    protected static ?string $model = Brand::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Brands';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BrandForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BrandsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBrands::route('/'),
            'create' => CreateBrand::route('/create'),
            'edit' => EditBrand::route('/{record}/edit'),
        ];
    }

    // ---- Admin search box (top of every admin page)
    protected static int $globalSearchResultsLimit = 6;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'country.name'];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('country');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            'Country' => $record->country?->name ?? 'Not set',
            'Status' => $record->is_active ? 'Active' : 'Hidden',
        ]);
    }
}
