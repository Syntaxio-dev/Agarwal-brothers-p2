<?php

namespace App\Support;

/**
 * Makes uploaded photos lighter without making them look worse.
 *
 * - PNG (logos, cut-out product shots, anything with transparency) becomes lossless WebP: every pixel identical.
 * - JPEG becomes WebP at quality 90, which is visually the same as the original.
 * - Pictures wider/taller than MAX_SIDE are scaled down (never up) with high-quality resampling; phone photos are rotated upright.
 * - If the result would not be clearly smaller, the original file is kept exactly as uploaded.
 *
 * Needs PHP's GD extension with WebP support; without it every method returns null and the original is stored.
 */
class ImageOptimizer
{
    /** Longest side in pixels. 2400 is still sharp on retina screens at the largest size the site shows. */
    public const MAX_SIDE = 2400;

    public const JPEG_QUALITY = 90;

    /** Only swap in the new file when it is at least this much smaller (or had to be resized). */
    public const MIN_SAVING = 0.15;

    public static function available(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromstring');
    }

    /** WebP bytes for the file, or null when the original should be kept (GIF, WebP, no GD, no real saving). */
    public static function webp(string $path, ?string $mime = null): ?string
    {
        return static::encode($path, $mime, 'webp');
    }

    /** Same picture, same format (JPEG/PNG), re-saved lighter. For tidying up files that are already stored. */
    public static function sameFormat(string $path, ?string $mime = null): ?string
    {
        $mime ??= is_file($path) ? (string) mime_content_type($path) : '';

        return static::encode($path, $mime, $mime === 'image/png' ? 'png' : 'jpeg');
    }

    private static function encode(string $path, ?string $mime, string $format): ?string
    {
        if (! static::available() || ! is_file($path)) {
            return null;
        }

        $mime ??= (string) mime_content_type($path);

        // Animated GIFs would lose their animation; WebP is already the target format.
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
            $resized = false;
            if (max($w, $h) > self::MAX_SIDE) {
                $image = static::scaleDown($image, $w, $h);
                $resized = true;
            }

            imagealphablending($image, false);
            imagesavealpha($image, true);

            ob_start();
            $ok = match ($format) {
                // PNG sources are graphics/transparent cut-outs: keep them pixel-perfect.
                'webp' => imagewebp($image, null, $mime === 'image/png' && defined('IMG_WEBP_LOSSLESS') ? IMG_WEBP_LOSSLESS : self::JPEG_QUALITY),
                'png' => imagepng($image, null, 9),
                default => imagejpeg($image, null, self::JPEG_QUALITY),
            };
            $bytes = ob_get_clean();

            if (! $ok || $bytes === '' || $bytes === false) {
                return null;
            }

            // Not clearly smaller and no resize needed: leave the original untouched (no needless re-encoding).
            if (! $resized && strlen($bytes) > filesize($path) * (1 - self::MIN_SAVING)) {
                return null;
            }

            return $bytes;
        } finally {
            imagedestroy($image);
        }
    }

    /** High-quality downscale that keeps transparency. */
    private static function scaleDown(\GdImage $image, int $w, int $h): \GdImage
    {
        $scale = self::MAX_SIDE / max($w, $h);
        [$nw, $nh] = [max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale))];

        $out = imagecreatetruecolor($nw, $nh);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        imagecopyresampled($out, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($image);

        return $out;
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
