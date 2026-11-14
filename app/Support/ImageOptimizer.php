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

    /**
     * A logo with its empty border cut away (transparent or plain background), plus a small even margin, as lossless WebP.
     * Logos come with very different empty borders inside the file, which made one brand look twice as big as another
     * on the same screen. Returns null when nothing should change (no GD, not a picture, already tight, or no clear content).
     */
    public static function trimmedLogoWebp(string $path, ?string $mime = null): ?string
    {
        if (! static::available() || ! is_file($path)) {
            return null;
        }

        $mime ??= (string) mime_content_type($path);
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $raw = @file_get_contents($path);
        $image = $raw === false ? false : @imagecreatefromstring($raw);
        if (! $image) {
            return null;
        }

        try {
            if (! imageistruecolor($image)) {
                imagepalettetotruecolor($image);
            }

            [$w, $h] = [imagesx($image), imagesy($image)];
            $corner = imagecolorat($image, 0, 0);
            $transparentBg = (($corner >> 24) & 0x7F) > 100;
            [$br, $bg, $bb] = [($corner >> 16) & 0xFF, ($corner >> 8) & 0xFF, $corner & 0xFF];

            // a pixel counts as part of the logo when it is opaque enough and clearly different from the background
            $isContent = static function (int $x, int $y) use ($image, $transparentBg, $br, $bg, $bb): bool {
                $c = imagecolorat($image, $x, $y);
                if ((($c >> 24) & 0x7F) > 100) {
                    return false;
                }
                if ($transparentBg) {
                    return true;
                }

                return abs((($c >> 16) & 0xFF) - $br) + abs((($c >> 8) & 0xFF) - $bg) + abs(($c & 0xFF) - $bb) > 60;
            };

            $top = $bottom = $left = $right = null;
            for ($y = 0; $y < $h && $top === null; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    if ($isContent($x, $y)) { $top = $y; break; }
                }
            }
            if ($top === null) {
                return null;
            }
            for ($y = $h - 1; $y >= $top && $bottom === null; $y--) {
                for ($x = 0; $x < $w; $x++) {
                    if ($isContent($x, $y)) { $bottom = $y; break; }
                }
            }
            for ($x = 0; $x < $w && $left === null; $x++) {
                for ($y = $top; $y <= $bottom; $y++) {
                    if ($isContent($x, $y)) { $left = $x; break; }
                }
            }
            for ($x = $w - 1; $x >= $left && $right === null; $x--) {
                for ($y = $top; $y <= $bottom; $y++) {
                    if ($isContent($x, $y)) { $right = $x; break; }
                }
            }

            $cw = $right - $left + 1;
            $ch = $bottom - $top + 1;
            $pad = max(3, (int) round(max($cw, $ch) * 0.05));
            [$nw, $nh] = [$cw + 2 * $pad, $ch + 2 * $pad];

            // already tight: leave the normal upload flow alone
            if ($nw >= $w * 0.96 && $nh >= $h * 0.96) {
                return null;
            }

            $out = imagecreatetruecolor($nw, $nh);
            imagealphablending($out, false);
            imagesavealpha($out, true);
            imagefill($out, 0, 0, $transparentBg ? imagecolorallocatealpha($out, 0, 0, 0, 127) : imagecolorallocate($out, $br, $bg, $bb));
            imagecopy($out, $image, $pad, $pad, $left, $top, $cw, $ch);

            ob_start();
            $ok = imagewebp($out, null, defined('IMG_WEBP_LOSSLESS') ? IMG_WEBP_LOSSLESS : self::JPEG_QUALITY);
            $bytes = ob_get_clean();
            imagedestroy($out);

            return $ok && $bytes !== '' && $bytes !== false ? $bytes : null;
        } finally {
            imagedestroy($image);
        }
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
