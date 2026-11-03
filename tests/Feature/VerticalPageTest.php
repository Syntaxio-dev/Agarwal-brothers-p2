<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Models\Vertical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VerticalPageTest extends TestCase
{
    use RefreshDatabase;

    private function vertical(int $brands, array $categoryExtra = []): Vertical
    {
        $vertical = Vertical::create(['name' => 'Lab Weighing', 'slug' => 'lab-weighing', 'is_active' => true]);
        for ($i = 1; $i <= $brands; $i++) {
            $brand = Brand::create(['name' => "Brand $i", 'slug' => "brand-$i", 'is_active' => true]);
            $cat = Category::create(['brand_id' => $brand->id, 'name' => "Line $i", 'slug' => "line-$i"] + $categoryExtra);
            $vertical->categories()->attach($cat);
        }

        return $vertical;
    }

    public function test_jump_chips_show_only_with_two_or_more_brands(): void
    {
        $one = $this->get(route('vertical.show', $this->vertical(1)->slug))->assertOk()->getContent();
        $this->assertStringNotContainsString('Jump to', $one);
        $this->assertStringContainsString('id="brand-brand-1"', $one);
    }

    public function test_jump_chips_link_to_each_brand_row(): void
    {
        $html = $this->get(route('vertical.show', $this->vertical(3)->slug))->assertOk()->getContent();

        $this->assertStringContainsString('Jump to', $html);
        foreach ([1, 2, 3] as $i) {
            $this->assertStringContainsString('href="#brand-brand-' . $i . '"', $html);
            $this->assertStringContainsString('id="brand-brand-' . $i . '"', $html);
        }
    }

    public function test_tile_shows_the_short_description_and_falls_back_to_the_trimmed_intro(): void
    {
        $vertical = $this->vertical(1, ['short_description' => 'Reads to 0.1 microgram.', 'description' => 'Long intro that must not win.']);
        $html = $this->get(route('vertical.show', $vertical->slug))->assertOk()->getContent();
        $this->assertStringContainsString('Reads to 0.1 microgram.', $html);
        $this->assertStringNotContainsString('Long intro that must not win.', $html);

        Category::query()->update(['short_description' => null, 'description' => '<b>' . str_repeat('word ', 40) . '</b>']);
        $html = $this->get(route('vertical.show', $vertical->slug))->getContent();
        $this->assertStringContainsString('word word', $html);
        $this->assertStringNotContainsString('<b>word', $html);
        $this->assertStringContainsString('...', $html);
    }

    public function test_tile_without_text_or_image_still_has_the_link_and_no_brand_01_label(): void
    {
        $html = $this->get(route('vertical.show', $this->vertical(2)->slug))->assertOk()->getContent();

        $this->assertStringContainsString('View category', $html);
        $this->assertStringContainsString('line-pic-empty', $html);
        $this->assertStringNotContainsString('Brand 01', $html);
        $this->assertStringContainsString('?v=lab-weighing', $html);
    }

    public function test_category_image_is_used_in_the_open_card(): void
    {
        $vertical = $this->vertical(1, ['image' => 'categories/card.webp']);
        $this->get(route('vertical.show', $vertical->slug))->assertSee('storage/categories/card.webp', false);
    }

    public function test_the_category_form_has_the_card_image_and_summary_and_saves_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);

        Livewire::actingAs($admin)->test(CreateCategory::class)
            ->assertFormFieldExists('image')
            ->assertFormFieldExists('short_description')
            ->fillForm(['brand_id' => $brand->id, 'name' => 'Micro Lab Balances', 'slug' => 'micro-lab-balances', 'short_description' => 'Seven-place readability.'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('Seven-place readability.', Category::where('slug', 'micro-lab-balances')->value('short_description'));

        Livewire::actingAs($admin)->test(CreateCategory::class)
            ->fillForm(['brand_id' => $brand->id, 'name' => 'X', 'slug' => 'x', 'short_description' => str_repeat('a', 161)])
            ->call('create')
            ->assertHasFormErrors(['short_description']);
    }
}
