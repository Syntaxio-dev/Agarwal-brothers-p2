<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/** Optional, collapsed SEO overrides shared by the content forms. */
class SeoSection
{
    public static function make(): Section
    {
        return Section::make('SEO (optional)')
            ->description('Leave these empty and the page title, description and share image are generated automatically from the content above.')
            ->columnSpanFull()
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('seo_title')
                    ->label('SEO title')
                    ->maxLength(70)
                    ->helperText('Shown in Google results. " | Agarwal Brothers" is added automatically.'),
                Textarea::make('seo_description')
                    ->label('SEO description')
                    ->rows(2)
                    ->maxLength(160)
                    ->helperText('Up to 160 characters.'),
                FileUpload::make('og_image')
                    ->label('Share image (Open Graph)')
                    ->helperText('Shown when the page is shared on WhatsApp, LinkedIn, etc. 1200x630 works best.')
                    ->image()
                    ->acceptedFileTypes(\App\Filament\Support\Uploads::IMAGES)
                    ->saveUploadedFileUsing(fn ($component, $file) => \App\Filament\Support\Uploads::save($component, $file))
                    ->maxSize(\App\Filament\Support\Uploads::IMAGE_MAX_KB)
                    ->disk('public')
                    ->visibility('public')
                    ->directory('seo'),
            ]);
    }
}
