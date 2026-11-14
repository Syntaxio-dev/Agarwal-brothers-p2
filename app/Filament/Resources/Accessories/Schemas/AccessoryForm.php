<?php

namespace App\Filament\Resources\Accessories\Schemas;

use App\Filament\Support\Uploads;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AccessoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Accessory')
                ->description('Something that goes with the instruments, for example a calibration test kit. Visitors can add it to their enquiry list from the brand page and from a product page.')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set, callable $get, $operation) {
                            if ($operation === 'create' && blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    Select::make('brand_id')
                        ->label('Brand')
                        ->relationship('brand', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('General (offered with every brand)')
                        ->helperText('Pick the brand this accessory is for: it is then shown first on that brand\'s products. Leave empty for an accessory that suits any brand.'),
                    Textarea::make('short_description')
                        ->label('Short text')
                        ->rows(3)
                        ->maxLength(300)
                        ->helperText('One or two lines: what it is and what it is used for. Up to 300 characters.')
                        ->columnSpanFull(),
                    FileUpload::make('image')
                        ->helperText('Best size: 800 × 800 px (1:1, square) on a white or transparent background. It is shown whole, never cropped, so leave a small empty border.')
                        ->image()
                        ->acceptedFileTypes(Uploads::IMAGES)
                        ->saveUploadedFileUsing(fn ($component, $file) => Uploads::save($component, $file))
                        ->maxSize(Uploads::IMAGE_MAX_KB)
                        ->disk('public')
                        ->visibility('public')
                        ->directory('accessories'),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(190)
                        ->unique(ignoreRecord: true)
                        ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                        ->helperText('Used inside the enquiry list. Made from the name for you.'),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->helperText('Switch off to hide it from the website.')
                        ->default(true),
                ]),
        ]);
    }
}
