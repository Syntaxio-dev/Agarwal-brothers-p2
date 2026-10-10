<?php

namespace Tests\Feature;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Models\Brand;
use App\Models\User;
use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_logo_upload_is_stored_as_webp_when_possible(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateBrand::class)
            ->fillForm([
                'name' => 'Upload Test',
                'logo' => UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==')),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $logo = Brand::where('name', 'Upload Test')->firstOrFail()->logo;

        $this->assertNotEmpty($logo);
        Storage::disk('public')->assertExists($logo);
        // With GD the picture is converted; without it the original is kept (nothing breaks).
        $this->assertStringEndsWith(ImageOptimizer::available() ? '.webp' : '.png', $logo);
    }
}
