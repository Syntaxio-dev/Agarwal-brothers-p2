<?php

namespace App\Sales\Support;

/** Tells what a picture really is from its first bytes (a renamed file is not enough). Only JPEG, PNG and WebP are kept. */
class ImageType
{
    /** @return ?array{ext: string, mime: string} */
    public static function detect(string $bytes): ?array
    {
        if (strncmp($bytes, "\xFF\xD8\xFF", 3) === 0) {
            return ['ext' => 'jpg', 'mime' => 'image/jpeg'];
        }

        if (strncmp($bytes, "\x89PNG\r\n\x1A\n", 8) === 0) {
            return ['ext' => 'png', 'mime' => 'image/png'];
        }

        if (strlen($bytes) >= 12 && substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') {
            return ['ext' => 'webp', 'mime' => 'image/webp'];
        }

        return null;
    }
}
