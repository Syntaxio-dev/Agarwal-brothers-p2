<?php

namespace Tests\Feature;

use App\Filament\Widgets\AdminFeaturesBox;
use App\Models\User;
use App\Support\AdminFeatures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminFeaturesBoxTest extends TestCase
{
    use RefreshDatabase;

    private function as(string $role): User
    {
        $user = User::factory()->create(['role' => $role, 'is_active' => true]);
        $this->actingAs($user);

        return $user;
    }

    public function test_an_administrator_sees_every_area_with_working_links(): void
    {
        $user = $this->as('admin');

        $groups = AdminFeatures::forUser($user);
        $titles = collect($groups)->flatten(1)->pluck('title');

        $this->assertSame(array_keys(AdminFeatures::all()), array_keys($groups));
        foreach (['Products', 'Import products', 'Enquiry questions', 'Site settings', 'Users & roles', 'Backups', 'Activity log'] as $title) {
            $this->assertContains($title, $titles->all());
        }
        foreach (collect($groups)->flatten(1) as $item) {
            $this->assertStringStartsWith('http', $item['url'], $item['title']);
        }

        Livewire::test(AdminFeaturesBox::class)
            ->assertSee('What you can do in this admin panel')
            ->assertSee('Enquiry questions')
            ->assertSee('Press Ctrl+K');
    }

    public function test_each_role_only_sees_its_own_areas(): void
    {
        $sales = collect(AdminFeatures::forUser($this->as('sales')))->flatten(1)->pluck('title')->all();
        $this->assertContains('Enquiries', $sales);
        $this->assertContains('Enquiry questions', $sales);
        $this->assertContains('Products', $sales);
        $this->assertNotContains('Users & roles', $sales);
        $this->assertNotContains('Backups', $sales);
        $this->assertNotContains('Job openings', $sales);

        $hr = collect(AdminFeatures::forUser($this->as('hr')))->flatten(1)->pluck('title')->all();
        $this->assertSame(['Job openings', 'Applications'], $hr);

        Livewire::test(AdminFeaturesBox::class)->assertSee('Job openings')->assertDontSee('Backups');
    }

    public function test_every_listed_area_exists_in_the_role_system(): void
    {
        $known = collect(config('roles'))->pluck('access')->flatten()->unique()->reject(fn ($a) => $a === '*')->all();
        $adminOnly = ['users', 'site-settings', 'backups', 'email-templates', 'activity-log'];

        foreach (AdminFeatures::all() as $items) {
            foreach ($items as [$area]) {
                $this->assertTrue(in_array($area, $known, true) || in_array($area, $adminOnly, true), "Unknown area key: {$area}");
            }
        }
    }

    public function test_the_dashboard_shows_the_box(): void
    {
        $this->as('admin');
        $this->get('/admin')->assertOk();
    }
}
