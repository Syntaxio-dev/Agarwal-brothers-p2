<?php

namespace Tests\Feature;

use App\Filament\Resources\Brands\Pages\CreateBrand;
use App\Filament\Resources\Brands\Pages\ListBrands;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Models\Vertical;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrandOrderTest extends TestCase
{
    use RefreshDatabase;

    private function vertical(array $brandNames, array $orders = []): Vertical
    {
        $vertical = Vertical::create(['name' => 'Weighing', 'slug' => 'weighing', 'is_active' => true]);
        foreach ($brandNames as $name) {
            $brand = Brand::create(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'is_active' => true, 'sort_order' => $orders[$name] ?? 0]);
            $vertical->categories()->attach(Category::create(['brand_id' => $brand->id, 'name' => 'Line ' . $name, 'slug' => 'line-' . $brand->id]));
        }

        return $vertical;
    }

    private function order(string $html, array $names): array
    {
        $pos = [];
        foreach ($names as $name) {
            $pos[$name] = strpos($html, 'View ' . $name);
        }
        asort($pos);

        return array_keys($pos);
    }

    public function test_without_an_order_the_brands_are_listed_a_to_z(): void
    {
        $vertical = $this->vertical(['Zeta', 'Aczet', 'Mettler']);
        $html = $this->get(route('vertical.show', $vertical->slug))->assertOk()->getContent();

        $this->assertSame(['Aczet', 'Mettler', 'Zeta'], $this->order($html, ['Aczet', 'Mettler', 'Zeta']));
    }

    public function test_the_owners_order_decides_which_brand_comes_first(): void
    {
        $vertical = $this->vertical(['Aczet', 'Mettler', 'Sartorius'], ['Sartorius' => 1, 'Mettler' => 2, 'Aczet' => 3]);
        $html = $this->get(route('vertical.show', $vertical->slug))->getContent();

        $this->assertSame(['Sartorius', 'Mettler', 'Aczet'], $this->order($html, ['Aczet', 'Mettler', 'Sartorius']));
    }

    public function test_brands_with_the_same_number_fall_back_to_a_to_z(): void
    {
        $vertical = $this->vertical(['Zeta', 'Aczet', 'Sartorius'], ['Sartorius' => 1]);
        $html = $this->get(route('vertical.show', $vertical->slug))->getContent();

        $this->assertSame(['Sartorius', 'Aczet', 'Zeta'], $this->order($html, ['Aczet', 'Zeta', 'Sartorius']));
    }

    public function test_dragging_the_rows_in_the_brands_list_saves_the_new_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $a = Brand::create(['name' => 'Aczet', 'slug' => 'aczet', 'is_active' => true]);
        $s = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);

        Livewire::test(ListBrands::class)->call('reorderTable', [$s->id, $a->id]);

        $this->assertSame(1, $s->fresh()->sort_order);
        $this->assertSame(2, $a->fresh()->sort_order);
    }

    public function test_the_brands_list_starts_in_the_owners_order(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        $a = Brand::create(['name' => 'Aczet', 'slug' => 'aczet', 'is_active' => true, 'sort_order' => 2]);
        $s = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true, 'sort_order' => 1]);

        Livewire::test(ListBrands::class)->assertCanSeeTableRecords([$s, $a], inOrder: true);
    }

    public function test_the_brand_logo_can_be_cropped_in_the_form(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateBrand::class)
            ->assertFormFieldExists('logo', fn (FileUpload $field) => $field->hasImageEditor());
    }
}
