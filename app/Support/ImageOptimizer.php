<?php

namespace App\Support;

/**
 * Turns an uploaded photo into a smaller WebP (resized, rotated upright, transparency kept).
 * Needs PHP's GD extension with WebP support; without it every method quietly returns null
 * and the original file is stored as it was.
 */
class ImageOptimizer
{
    public const MAX_SIDE = 2000;

    public const QUALITY = 82;

    public static function available(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    /** WebP bytes for the file, or null when it should be stored untouched (GIF, WebP, no GD, unreadable). */
    public static function webp(string $path, ?string $mime = null): ?string
    {
        if (! static::available() || ! is_file($path)) {
            return null;
        }

        $mime ??= (string) mime_content_type($path);

        // Animated GIFs would lose their animation; WebP uploads are already in the target format.
        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            return null;
        }

        $raw = @file_get_contents($path);
        $image = $raw === false ? false : @imagecreatefromstring($raw);
        if (! $image) {
            return null;
        }

        try {
            if ($mime === 'image/jpeg') {
                $image = static::upright($image, $path);
            }

            [$w, $h] = [imagesx($image), imagesy($image)];
            if (max($w, $h) > self::MAX_SIDE) {
                $scale = self::MAX_SIDE / max($w, $h);
                $resized = imagescale($image, max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
                if ($resized) {
                    imagedestroy($image);
                    $image = $resized;
                }
            }

            if (! imageistruecolor($image)) {
                imagepalettetotruecolor($image);
            }
            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            $ok = imagewebp($image, null, self::QUALITY);
            $bytes = ob_get_clean();

            return $ok && $bytes !== '' && $bytes !== false ? $bytes : null;
        } finally {
            imagedestroy($image);
        }
    }

    /** Phone photos carry a rotation flag; apply it so the picture is the right way up. */
    private static function upright(\GdImage $image, string $path): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($path)['Orientation'] ?? 1;
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;

        if ($angle) {
            $rotated = imagerotate($image, $angle, 0);
            if ($rotated) {
                imagedestroy($image);

                return $rotated;
            }
        }

        return $image;
    }
}
