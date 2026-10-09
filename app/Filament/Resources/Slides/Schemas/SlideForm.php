<?php

namespace App\Filament\Resources\Slides\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Text (optional)')
                    ->description('Leave empty if your image or video already contains the text.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('title'),
                        TextInput::make('subtitle'),
                        TextInput::make('link_url')
                            ->label('Button link (optional)')
                            ->placeholder('/verticals')
                            ->columnSpanFull(),
                    ]),

                Section::make('Media')
                    ->description('Upload a video to play it in the hero; otherwise the image is used.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->visibility('public')
                            ->directory('slides'),
                        FileUpload::make('video')
                            ->label('Video (MP4 / WebM, max 50 MB)')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('slides')
                            ->acceptedFileTypes(['video/mp4', 'video/webm'])
                            ->maxSize(51200),
                        TextInput::make('video_url')
                            ->label('External video URL (optional)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Visibility')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower number appears first. You can also drag rows in the list.'),
                    ]),
            ]);
    }
}
