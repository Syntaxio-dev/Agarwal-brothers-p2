<?php

namespace App\Filament\Resources\Slides\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title'),
                TextInput::make('subtitle'),
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('slides'),
                TextInput::make('video_url')
                    ->label('Video URL (optional)'),
                 FileUpload::make('video')
                   ->label('Upload Video (optional)')
                   ->disk('public')
                   ->visibility('public')
                   ->directory('slides')
                   ->acceptedFileTypes(['video/mp4', 'video/webm'])
                    ->maxSize(51200),

                
                TextInput::make('link_url')
                    ->label('Link to product/page (optional)'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}