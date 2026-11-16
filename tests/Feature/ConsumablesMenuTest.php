<?php

namespace Tests\Feature;

use App\Filament\Resources\Verticals\Pages\CreateVertical;
use App\Models\User;
use App\Models\Vertical;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConsumablesMenuTest extends TestCase
{
    use RefreshDatabase;

    private function vertical(string $name, string $kind = 'equipment', bool $active = true): Vertical
    {
        return Vertical::create(['name' => $name, 'slug' => str($name)->slug(), 'kind' => $kind, 'is_active' => $active]);
    }

    public function test_a_new_vertical_is_equipment_by_default(): void
    {
        $v = Vertical::create(['name' => 'Weighing', 'slug' => 'weighing', 'is_active' => true]);

        $this->assertSame('equipment', $v->fresh()->kind);
    }

    public function test_the_menu_has_equipments_above_consumables(): void
    {
        $html = $this->get('/our-story')->assertOk()->getContent();

        $eq = strpos($html, '>Equipments</a>');
        $co = strpos($html, '>Consumables</a>');
        $this->assertNotFalse($eq);
        $this->assertNotFalse($co);
        $this->assertLessThan($co, $eq);
        $this->assertStringContainsString(route('consumables.index'), $html);
    }

    private function main(string $url): string
    {
        $html = $this->get($url)->assertOk()->getContent();

        return substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));
    }

    public function test_the_equipment_page_shows_only_equipment_and_consumables_only_consumables(): void
    {
        $this->vertical('Weighing Solutions');
        $this->vertical('Pipette Tips', 'consumables');

        $eq = $this->main('/verticals');
        $this->assertStringContainsString('Weighing Solutions', $eq);
        $this->assertStringNotContainsString('Pipette Tips', $eq);

        $co = $this->main('/consumables');
        $this->assertStringContainsString('Pipette Tips', $co);
        $this->assertStringNotContainsString('Weighing Solutions', $co);

        $home = $this->main('/');
        $this->assertStringContainsString('Weighing Solutions', $home);
        $this->assertStringNotContainsString('Pipette Tips', $home);
    }

    public function test_an_empty_consumables_page_says_coming_soon_and_is_not_indexed_or_in_the_sitemap(): void
    {
        $this->vertical('Weighing Solutions');

        $this->get('/consumables')->assertOk()->assertSee('coming soon')->assertSee('noindex', false);
        $this->assertStringNotContainsString('/consumables', $this->get('/sitemap.xml')->getContent());

        $this->vertical('Pipette Tips', 'consumables');
        $this->get('/sitemap.xml')->assertSee('/consumables', false);
    }

    public function test_a_consumables_vertical_page_has_the_consumables_breadcrumb(): void
    {
        $this->vertical('Pipette Tips', 'consumables');

        $this->get(route('vertical.show', 'pipette-tips'))->assertOk()->assertSee(route('consumables.index'), false);
    }

    public function test_the_admin_can_choose_equipments_or_consumables(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateVertical::class)
            ->fillForm(['name' => 'Glassware', 'slug' => 'glassware', 'kind' => 'consumables', 'is_active' => true])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('consumables', Vertical::where('slug', 'glassware')->value('kind'));
    }
}
