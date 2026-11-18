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
                    // the automatic title is shown greyed in the empty box, so nobody has to guess what Google gets
                    ->placeholder(fn ($record) => $record && method_exists($record, 'autoSeoTitle') ? $record->autoSeoTitle() : null)
                    ->helperText('Shown in Google results. Leave empty to use the automatic title shown in grey (brand + model + type). " | Agarwal Brothers" is added automatically.'),
                Textarea::make('seo_description')
                    ->label('SEO description')
                    ->rows(2)
                    ->maxLength(160)
                    ->placeholder(fn ($record) => $record && method_exists($record, 'autoSeoDescription') ? $record->autoSeoDescription() : null)
                    ->helperText('Up to 160 characters. Leave empty to use the automatic text shown in grey.'),
                FileUpload::make('og_image')
                    ->label('Share image (Open Graph)')
                    ->helperText('Shown when the page is shared on WhatsApp, LinkedIn, etc. Best size: 1200 × 630 px (about 1.9:1, landscape). Keep the main content in the centre.')
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
