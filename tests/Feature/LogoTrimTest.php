<?php

namespace Tests\Feature;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Models\Brand;
use App\Models\Client;
use App\Models\User;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Needs PHP GD with WebP (off in the local XAMPP: run with `php -d extension=gd`). */
class LogoTrimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! ImageOptimizer::available()) {
            $this->markTestSkipped('PHP GD with WebP is not enabled.');
        }
    }

    /** A logo-like picture: a dark block on a white (or transparent) canvas with a big empty border. */
    private function logo(int $w, int $h, array $block, bool $transparent = false): string
    {
        $im = imagecreatetruecolor($w, $h);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, $transparent ? imagecolorallocatealpha($im, 0, 0, 0, 127) : imagecolorallocate($im, 255, 255, 255));
        [$x1, $y1, $x2, $y2] = $block;
        imagefilledrectangle($im, $x1, $y1, $x2, $y2, imagecolorallocate($im, 11, 37, 69));

        $path = tempnam(sys_get_temp_dir(), 'logo') . '.png';
        imagepng($im, $path);
        imagedestroy($im);

        return $path;
    }

    private function dims(string $bytes): array
    {
        $s = getimagesizefromstring($bytes);

        return [$s[0], $s[1]];
    }

    public function test_the_empty_white_border_is_cut_away_and_a_small_margin_stays(): void
    {
        // 900 x 300 canvas, block 200 x 60 in the middle
        $bytes = ImageOptimizer::trimmedLogoWebp($this->logo(900, 300, [350, 120, 549, 179]));

        [$w, $h] = $this->dims($bytes);
        $this->assertSame(220, $w);   // 200 + 2 x 10 (5% margin)
        $this->assertSame(80, $h);    // 60 + 2 x 10
    }

    public function test_a_transparent_border_is_cut_away_too_and_stays_transparent(): void
    {
        $bytes = ImageOptimizer::trimmedLogoWebp($this->logo(900, 300, [350, 120, 549, 179], transparent: true));

        $this->assertSame([220, 80], $this->dims($bytes));
        $im = imagecreatefromstring($bytes);
        $this->assertSame(127, (imagecolorat($im, 0, 0) >> 24) & 0x7F, 'the margin is still transparent');
        imagedestroy($im);
    }

    public function test_the_content_is_kept_pixel_for_pixel(): void
    {
        $bytes = ImageOptimizer::trimmedLogoWebp($this->logo(900, 300, [350, 120, 549, 179]));
        $im = imagecreatefromstring($bytes);
        $c = imagecolorat($im, 110, 40); // inside the block
        $this->assertSame([11, 37, 69], [($c >> 16) & 0xFF, ($c >> 8) & 0xFF, $c & 0xFF]);
        imagedestroy($im);
    }

    public function test_a_logo_that_is_already_tight_is_left_alone(): void
    {
        $this->assertNull(ImageOptimizer::trimmedLogoWebp($this->logo(200, 60, [5, 5, 194, 54])));
    }

    public function test_a_blank_picture_or_a_non_picture_is_left_alone(): void
    {
        $blank = $this->logo(300, 100, [0, 0, 0, 0]);
        // a 1 pixel block is "content", so make it truly blank
        $im = imagecreatetruecolor(300, 100);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));
        $path = tempnam(sys_get_temp_dir(), 'blank') . '.png';
        imagepng($im, $path);

        $this->assertNull(ImageOptimizer::trimmedLogoWebp($path));
        $txt = tempnam(sys_get_temp_dir(), 'txt');
        file_put_contents($txt, 'not a picture');
        $this->assertNull(ImageOptimizer::trimmedLogoWebp($txt));
        $this->assertNull(ImageOptimizer::trimmedLogoWebp('/no/such/file.png'));
    }

    public function test_the_brand_form_trims_an_uploaded_logo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $file = UploadedFile::fake()->createWithContent('aczet.png', file_get_contents($this->logo(900, 300, [350, 120, 549, 179])));

        Livewire::test(CreateBrand::class)
            ->fillForm(['name' => 'Aczet', 'logo' => $file])
            ->call('create')
            ->assertHasNoFormErrors();

        $logo = Brand::where('name', 'Aczet')->firstOrFail()->logo;
        $this->assertStringEndsWith('.webp', $logo);
        $this->assertSame([220, 80], $this->dims(Storage::disk('public')->get($logo)));
    }

    public function test_the_command_reports_first_and_only_changes_with_apply(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('brands/a.png', file_get_contents($this->logo(900, 300, [350, 120, 549, 179])));
        Storage::disk('public')->put('brands/tight.png', file_get_contents($this->logo(200, 60, [5, 5, 194, 54])));
        Storage::disk('public')->put('brands/x.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');
        Storage::disk('public')->put('clients/c.png', file_get_contents($this->logo(600, 200, [250, 80, 349, 119])));
        Brand::create(['name' => 'Aczet', 'slug' => 'aczet', 'logo' => 'brands/a.png', 'is_active' => true]);
        Brand::create(['name' => 'Tight', 'slug' => 'tight', 'logo' => 'brands/tight.png', 'is_active' => true]);
        Brand::create(['name' => 'Vector', 'slug' => 'vector', 'logo' => 'brands/x.svg', 'is_active' => true]);
        Client::create(['name' => 'Some Client', 'logo' => 'clients/c.png', 'is_active' => true]);

        $this->artisan('logos:trim')->assertSuccessful();
        $this->assertSame('brands/a.png', Brand::where('slug', 'aczet')->value('logo'), 'a dry run changes nothing');
        Storage::disk('public')->assertExists('brands/a.png');

        $this->artisan('logos:trim', ['--apply' => true])->assertSuccessful();

        $aczet = Brand::where('slug', 'aczet')->value('logo');
        $this->assertStringEndsWith('.webp', $aczet);
        $this->assertSame([220, 80], $this->dims(Storage::disk('public')->get($aczet)));
        Storage::disk('public')->assertMissing('brands/a.png');
        $this->assertSame('brands/tight.png', Brand::where('slug', 'tight')->value('logo'));
        $this->assertSame('brands/x.svg', Brand::where('slug', 'vector')->value('logo'));
        $client = Client::where('name', 'Some Client')->value('logo');
        $this->assertStringEndsWith('.webp', $client);
        $this->assertStringStartsWith('clients/', $client);
    }
}
