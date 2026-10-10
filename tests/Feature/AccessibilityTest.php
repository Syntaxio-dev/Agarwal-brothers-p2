<?php

namespace Tests\Feature;

use App\Filament\Resources\Slides\Pages\CreateSlide;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AccessibilityTest extends TestCase
{
    use RefreshDatabase;

    private function category(int $products, ?array $groups = null): Category
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances']);

        foreach (range(1, $products) as $i) {
            Product::create([
                'category_id' => $cat->id, 'name' => "Model {$i}", 'slug' => "model-{$i}", 'is_active' => true,
                'model_group' => $groups ? $groups[$i % count($groups)] : null,
            ]);
        }

        return $cat;
    }

    public function test_every_page_has_a_skip_link_and_a_main_landmark(): void
    {
        foreach (['/', '/contact-us', '/verticals', '/our-story', '/search'] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('href="#main"', false)
                ->assertSee('Skip to main content')
                ->assertSee('<main id="main"', false);
        }
    }

    public function test_cyan_text_uses_the_accessible_shade_on_light_backgrounds(): void
    {
        $html = $this->get('/verticals')->getContent();

        $this->assertStringContainsString('text-cyan-ink', $html);
        // The footer is a dark surface, so its bright cyan links stay as they are.
        $this->assertStringContainsString('hover:text-cyan', $this->get('/')->getContent());
    }

    public function test_long_category_lists_get_a_filter_bar_with_group_chips(): void
    {
        $cat = $this->category(10, ['Standard', 'Advanced']);

        $this->get(route('category.show', [$cat->brand->slug, $cat->slug]))->assertOk()
            ->assertSee('catalogueFilter', false)
            ->assertSee('Search these models')
            ->assertSee('Name A to Z')
            ->assertSee('Standard')
            ->assertSee('Showing');
    }

    public function test_short_category_lists_stay_uncluttered(): void
    {
        $cat = $this->category(4);

        $this->get(route('category.show', [$cat->brand->slug, $cat->slug]))->assertOk()
            ->assertDontSee('catalogueFilter', false)
            ->assertDontSee('Search these models');
    }

    public function test_slide_image_needs_a_description_in_admin(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');

        Livewire::test(CreateSlide::class)
            ->fillForm(['image' => UploadedFile::fake()->createWithContent('hero.png', $png), 'alt_text' => ''])
            ->call('create')
            ->assertHasFormErrors(['alt_text' => 'required']);

        Livewire::test(CreateSlide::class)
            ->fillForm(['image' => UploadedFile::fake()->createWithContent('hero.png', $png), 'alt_text' => 'Scientists working in a modern laboratory'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('Scientists working in a modern laboratory', Slide::firstOrFail()->alt_text);
    }

    public function test_home_slide_uses_its_description_as_alt_text(): void
    {
        Slide::create(['title' => 'Hello', 'image' => 'slides/x.png', 'alt_text' => 'Scientists at work', 'is_active' => true]);

        $this->get('/')->assertOk()->assertSee('alt="Scientists at work"', false);
    }
}
