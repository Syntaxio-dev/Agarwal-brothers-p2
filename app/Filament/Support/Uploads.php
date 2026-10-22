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

    /** File types that may be stored on the public disk, by their real content. */
    public const PUBLIC_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'mp4', 'webm'];

    /**
     * A random file name for an upload. On the public disk the extension comes from the file's real content
     * (a PDF named .html is stored as .pdf, anything unknown as .bin, which the upload folder never serves);
     * private uploads (the product import) keep their own extension, cleaned.
     */
    public static function storageName(\Symfony\Component\HttpFoundation\File\UploadedFile $file, string $disk): string
    {
        if ($disk === 'public') {
            $guess = strtolower((string) $file->guessExtension());
            $ext = in_array($guess, static::PUBLIC_EXTENSIONS, true) ? $guess : 'bin';
        } else {
            $ext = strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $file->getClientOriginalExtension())) ?: 'bin';
        }

        return Str::ulid() . '.' . $ext;
    }
}
