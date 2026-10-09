<?php

namespace App\Filament\Resources\ApplicationResources;

use App\Filament\Resources\ApplicationResources\Pages\CreateApplicationResource;
use App\Filament\Resources\ApplicationResources\Pages\EditApplicationResource;
use App\Filament\Resources\ApplicationResources\Pages\ListApplicationResources;
use App\Filament\Resources\ApplicationResources\Schemas\ApplicationResourceForm;
use App\Filament\Resources\ApplicationResources\Tables\ApplicationResourcesTable;
use App\Models\ApplicationResource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApplicationResourceResource extends Resource
{
    protected static ?string $model = ApplicationResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Application Resources';

    protected static ?string $modelLabel = 'application resource';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return ApplicationResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationResourcesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplicationResources::route('/'),
            'create' => CreateApplicationResource::route('/create'),
            'edit' => EditApplicationResource::route('/{record}/edit'),
        ];
    }
}
