<?php

namespace App\Filament\Support;

/**
 * Upload whitelists. FileUpload::image() alone accepts "image/*", which
 * includes SVG; SVG can carry JavaScript and is served from our own origin,
 * so only raster formats are allowed.
 */
class Uploads
{
    public const IMAGES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public const IMAGE_MAX_KB = 8192;
}
