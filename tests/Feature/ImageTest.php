<?php

namespace Tests\Feature;

use App\Support\Img;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageTest extends TestCase
{
    use RefreshDatabase;

    private function makePng(int $w, int $h): string
    {
        $path = tempnam(sys_get_temp_dir(), 'abimg') . '.png';
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));
        imagefilledellipse($im, intdiv($w, 2), intdiv($h, 2), intdiv($w, 2), intdiv($h, 2), imagecolorallocate($im, 0, 119, 182));
        imagepng($im, $path);
        imagedestroy($im);

        return $path;
    }

    public function test_png_is_converted_to_a_resized_webp_keeping_transparency(): void
    {
        if (! ImageOptimizer::available()) {
            $this->markTestSkipped('PHP GD with WebP support is not enabled here.');
        }

        $png = $this->makePng(3000, 1500);
        $bytes = ImageOptimizer::webp($png, 'image/png');
        @unlink($png);

        $this->assertNotNull($bytes);
        $this->assertSame('RIFF', substr($bytes, 0, 4));
        $this->assertSame('WEBP', substr($bytes, 8, 4));

        $im = imagecreatefromstring($bytes);
        $this->assertSame(2000, imagesx($im));      // longest side capped
        $this->assertSame(1000, imagesy($im));      // aspect ratio kept
        $this->assertGreaterThan(0, (imagecolorat($im, 2, 2) >> 24) & 127);  // corner is still transparent
    }

    public function test_gif_and_unreadable_files_are_left_alone(): void
    {
        $this->assertNull(ImageOptimizer::webp(__FILE__, 'image/gif'));
        $this->assertNull(ImageOptimizer::webp('/no/such/file.png', 'image/png'));
    }

    public function test_image_dimensions_are_read_for_storage_files(): void
    {
        if (! function_exists('imagepng')) {
            $this->markTestSkipped('GD needed to create the sample image.');
        }

        Storage::fake('public');
        $png = $this->makePng(40, 20);
        Storage::disk('public')->put('products/sample.png', file_get_contents($png));
        @unlink($png);

        $this->assertSame('width="40" height="20"', Img::attrs('products/sample.png'));
        $this->assertSame('', Img::attrs('products/missing.png'));
        $this->assertSame('', Img::attrs(null));
    }

    public function test_pages_use_lazy_images_with_placeholder_and_preloaded_fonts(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('rel="preload"', $html);
        $this->assertStringContainsString('.woff2', $html);
        $this->assertStringContainsString("classList.add('js')", $html);
        $this->assertStringContainsString('img-load', $html);
    }
}
