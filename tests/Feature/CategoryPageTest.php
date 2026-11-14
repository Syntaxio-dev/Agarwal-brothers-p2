<?php

namespace Tests\Feature;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryPageTest extends TestCase
{
    use RefreshDatabase;

    private function line(array $brand = [], int $products = 1, array $productExtra = []): array
    {
        $brand = Brand::create($brand + ['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Analytical Lab Balances', 'slug' => 'analytical', 'sort_order' => 1]);
        for ($i = 1; $i <= $products; $i++) {
            Product::create($productExtra + ['category_id' => $cat->id, 'name' => "Model $i", 'slug' => "model-$i", 'is_active' => true]);
        }

        return [$brand, $cat];
    }

    private function page(Brand $brand, Category $cat): string
    {
        return $this->get(route('category.show', [$brand->slug, $cat->slug]))->assertOk()->getContent();
    }

    public function test_the_toolbar_is_always_there_even_for_one_model(): void
    {
        [$brand, $cat] = $this->line();
        $html = $this->page($brand, $cat);

        $this->assertStringContainsString('Search these models', $html);
        $this->assertStringContainsString('Name A to Z', $html);
        $this->assertStringContainsString('in enquiry list', $html);
        $this->assertStringContainsString('to compare', $html);
    }

    public function test_the_brochure_card_shows_only_when_the_brand_has_a_file(): void
    {
        [$brand, $cat] = $this->line();
        $this->assertStringNotContainsString('Brochure &middot; PDF', $this->page($brand, $cat));

        $brand->update(['brochure' => 'brands/brochures/sartorius.pdf']);
        $html = $this->page($brand, $cat);
        $this->assertStringContainsString('storage/brands/brochures/sartorius.pdf', $html);
        $this->assertStringContainsString('Read the Sartorius brochure to find your best fit', $html);
        $this->assertMatchesRegularExpression('/href="[^"]*sartorius\.pdf" target="_blank" rel="noopener noreferrer"/', $html);
    }

    public function test_more_from_lists_the_other_lines_and_not_the_current_one(): void
    {
        [$brand, $cat] = $this->line();
        Category::create(['brand_id' => $brand->id, 'name' => 'Micro Lab Balances', 'slug' => 'micro']);
        $other = Brand::create(['name' => 'Ohaus', 'slug' => 'ohaus', 'is_active' => true]);
        Category::create(['brand_id' => $other->id, 'name' => 'Ohaus Line', 'slug' => 'ohaus-line']);

        $html = $this->page($brand, $cat);
        $this->assertStringContainsString('More from Sartorius', $html);
        // look only inside the rail (the canonical link in the head also names the current line)
        $rail = substr($html, strpos($html, 'More from Sartorius'));
        $rail = substr($rail, 0, strpos($rail, '</aside>'));
        $this->assertStringContainsString('/sartorius/micro', $rail);
        $this->assertStringNotContainsString('/sartorius/analytical', $rail);
        $this->assertStringNotContainsString('Ohaus Line', $html);
    }

    public function test_more_from_is_hidden_when_the_brand_has_no_other_line(): void
    {
        [$brand, $cat] = $this->line();
        $this->assertStringNotContainsString('More from Sartorius', $this->page($brand, $cat));
    }

    public function test_the_model_group_card_needs_more_than_one_group(): void
    {
        [$brand, $cat] = $this->line();
        $this->assertStringNotContainsString('Model group</p>', $this->page($brand, $cat));

        Product::where('slug', 'model-1')->update(['model_group' => 'Standard']);
        Product::create(['category_id' => $cat->id, 'name' => 'Model 2', 'slug' => 'model-2', 'is_active' => true, 'model_group' => 'Premium']);
        $html = $this->page($brand, $cat);
        $this->assertStringContainsString('Model group</p>', $html);
        $this->assertStringContainsString('Premium', $html);
    }

    public function test_the_card_shows_two_specs_but_not_the_model_row_and_not_with_fewer_than_two(): void
    {
        [$brand, $cat] = $this->line(products: 1, productExtra: ['specs' => ['Model' => 'BSA224S', 'Readability (g)' => '0.0001', 'Capacity (g)' => '220', 'Pan size (mm)' => '90']]);
        $html = $this->page($brand, $cat);
        $this->assertStringContainsString('Readability (g)', $html);
        $this->assertStringContainsString('Capacity (g)', $html);
        $this->assertStringNotContainsString('Pan size (mm)', $html);
        $this->assertStringNotContainsString('>BSA224S<', $html);

        Product::query()->update(['specs' => ['Model' => 'BSA224S', 'Capacity (g)' => '220']]);
        $this->assertStringNotContainsString('Capacity (g)', $this->page($brand, $cat));
    }

    public function test_the_card_has_the_labelled_add_button_and_view_details(): void
    {
        [$brand, $cat] = $this->line();
        $html = $this->page($brand, $cat);

        $this->assertStringContainsString('View details', $html);
        $this->assertStringContainsString('+ Add to list', $html);
        $this->assertStringContainsString('sm:bottom-[18px]', $html);
    }

    public function test_the_intro_is_always_shown_in_full_in_the_banner(): void
    {
        [$brand, $cat] = $this->line();
        $cat->update(['heading' => 'Best Analytical Balance', 'description' => 'A long intro paragraph.']);
        $html = $this->page($brand, $cat);

        $this->assertStringNotContainsString('Read more', $html);
        $this->assertStringNotContainsString('Show less', $html);
        $this->assertStringNotContainsString('introOpen', $html);
        $this->assertStringContainsString('Best Analytical Balance', $html);
        $this->assertStringContainsString('A long intro paragraph.', $html);
        $this->assertStringContainsString('text-navy/80', $html);
    }

    public function test_the_brand_logo_box_in_the_banner_scales_with_the_screen(): void
    {
        [$brand, $cat] = $this->line();
        $html = $this->page($brand, $cat);

        $this->assertStringContainsString('width: clamp(9rem, 9vw + 6.5rem, 16rem)', $html);
        $this->assertStringContainsString('aspect-ratio: 5 / 2', $html);
    }

    public function test_the_empty_state_has_the_new_copy_phone_and_hours_from_config(): void
    {
        [$brand, $cat] = $this->line(products: 0);
        Category::create(['brand_id' => $brand->id, 'name' => 'Micro Lab Balances', 'slug' => 'micro']);
        $html = $this->page($brand, $cat);

        $this->assertStringContainsString('Models for this range are coming soon.', $html);
        $this->assertStringContainsString('Tell us what you need and our team will reply with the right options.', $html);
        $this->assertStringContainsString(config('contact.call'), $html);
        $this->assertStringContainsString(config('contact.working_days.0'), $html);
        $this->assertStringContainsString('More from Sartorius', $html);
        $this->assertStringNotContainsString('Model group</p>', $html);
        $this->assertStringNotContainsString('Search these models', $html);
    }

    public function test_the_brand_form_has_a_brochure_pdf_upload(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateBrand::class)->assertFormFieldExists('brochure');
    }
}
