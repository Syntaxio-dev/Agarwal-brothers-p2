<?php

namespace App\Filament\Support;

use App\Support\ImageOptimizer;
use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Upload whitelists. FileUpload::image() alone accepts "image/*", which
 * includes SVG; SVG can carry JavaScript and is served from our own origin,
 * so only raster formats are allowed.
 */
class Uploads
{
    public const IMAGES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public const IMAGE_MAX_KB = 8192;

    /**
     * Save an uploaded image as a resized WebP when possible (much smaller, faster pages).
     * Falls back to storing the original file under its normal name.
     * Use with ->saveUploadedFileUsing(fn ($component, $file) => Uploads::save($component, $file)).
     */
    public static function save(BaseFileUpload $component, TemporaryUploadedFile $file): ?string
    {
        $bytes = ImageOptimizer::webp($file->getRealPath(), $file->getMimeType());

        if ($bytes !== null) {
            $path = trim($component->getDirectory() . '/' . Str::ulid() . '.webp', '/');
            Storage::disk($component->getDiskName())->put($path, $bytes, $component->getVisibility());

            return $path;
        }

        $path = $file->storeAs($component->getDirectory(), $component->getUploadedFileNameForStorage($file), [
            'disk' => $component->getDiskName(),
            'mimetype' => $file->getMimeType(),
        ]);

        if ($component->getVisibility() === 'public') {
            rescue(fn () => $component->getDisk()->setVisibility($path, 'public'), report: false);
        }

        return $path;
    }
}
