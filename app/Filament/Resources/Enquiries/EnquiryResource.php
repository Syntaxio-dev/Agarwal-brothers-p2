<?php

namespace App\Filament\Resources\Enquiries;

use App\Filament\Resources\Enquiries\Pages\CreateEnquiry;
use App\Filament\Resources\Enquiries\Pages\EditEnquiry;
use App\Filament\Resources\Enquiries\Pages\ListEnquiries;
use App\Filament\Resources\Enquiries\Schemas\EnquiryForm;
use App\Filament\Resources\Enquiries\Tables\EnquiriesTable;
use App\Models\Enquiry;
use App\Filament\Concerns\RestrictedByRole;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EnquiryResource extends Resource
{
    use RestrictedByRole;

    protected static string $accessKey = 'enquiries';

    protected static ?string $model = Enquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Leads';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Enquiries';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EnquiryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnquiriesTable::configure($table);
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
            'index' => ListEnquiries::route('/'),
            'create' => CreateEnquiry::route('/create'),
            'edit' => EditEnquiry::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Enquiry::where('status', 'new')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // ---- Admin search box (top of every admin page)
    protected static int $globalSearchResultsLimit = 6;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'company', 'message', 'product.name', 'items.product_name'];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('product')->withCount('items');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return array_filter([
            'Email' => $record->email,
            'Phone' => $record->phone,
            'About' => $record->items_count ? $record->items_count . ' products (group enquiry)' : $record->product?->name,
            'Status' => ucfirst((string) $record->status),
        ]);
    }
}
