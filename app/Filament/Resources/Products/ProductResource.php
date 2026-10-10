<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Filament\Resources\Products\Tables\ProductsTable;
use App\Models\Product;
use App\Filament\Concerns\RestrictedByRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'products';

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
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
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    // ---- Admin search box (top of every admin page)
    protected static int $globalSearchResultsLimit = 6;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'short_description', 'category.name', 'category.brand.name'];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('category.brand');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            'Brand' => $record->category?->brand?->name,
            'Category' => $record->category?->name,
            'Status' => $record->is_active ? 'Active' : 'Draft (hidden)',
        ]);
    }
}
