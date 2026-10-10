<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/** Width / height attributes for uploaded images, so the browser reserves the space before the picture loads. */
class Img
{
    /** Attribute string like `width="800" height="600"`, or '' when the size cannot be read. */
    public static function attrs(?string $storagePath, string $disk = 'public'): string
    {
        if (blank($storagePath)) {
            return '';
        }

        $size = static::size($storagePath, $disk);

        return $size ? 'width="' . $size[0] . '" height="' . $size[1] . '"' : '';
    }

    /** Same, for a file in public/ (logos and other bundled images). */
    public static function publicAttrs(string $file): string
    {
        $full = public_path($file);

        if (! is_file($full)) {
            return '';
        }

        $size = Cache::rememberForever('imgsize:public:' . md5($file) . ':' . filemtime($full), function () use ($full) {
            $info = @getimagesize($full);

            return $info && $info[0] > 0 ? [(int) $info[0], (int) $info[1]] : [];
        });

        return $size ? 'width="' . $size[0] . '" height="' . $size[1] . '"' : '';
    }

    /** @return array{0: int, 1: int}|null */
    public static function size(string $storagePath, string $disk = 'public'): ?array
    {
        $full = Storage::disk($disk)->path($storagePath);

        if (! is_file($full)) {
            return null;
        }

        // Re-read only when the file changes (the key includes its modification time).
        $key = 'imgsize:' . md5($disk . $storagePath) . ':' . filemtime($full);

        $size = Cache::rememberForever($key, function () use ($full) {
            $info = @getimagesize($full);

            return $info && $info[0] > 0 && $info[1] > 0 ? [(int) $info[0], (int) $info[1]] : [];
        });

        return $size ?: null;
    }
}
