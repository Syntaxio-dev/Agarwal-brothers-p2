<?php

namespace Tests\Feature;

use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteSettingsDirectTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return tap(User::factory()->create(['role' => 'admin', 'is_active' => true]), fn ($u) => $this->actingAs($u));
    }

    public function test_the_menu_item_and_the_old_list_address_open_the_settings_page_directly(): void
    {
        $this->admin();
        $settings = SiteSetting::create(['founded_year' => 1993]);
        $edit = '/admin/site-settings/' . $settings->id . '/edit';

        $this->assertSame(url($edit), SiteSettingResource::getNavigationUrl());
        $this->get('/admin/site-settings')->assertRedirect($edit);
        $this->get($edit)->assertOk()->assertSee('Site settings');
    }

    public function test_on_a_new_site_the_menu_goes_to_the_create_page(): void
    {
        $this->admin();

        $this->assertSame(url('/admin/site-settings/create'), SiteSettingResource::getNavigationUrl());
        $this->get('/admin/site-settings')->assertRedirect('/admin/site-settings/create');
    }

    public function test_the_settings_page_has_no_delete_and_no_odd_breadcrumb_and_still_saves(): void
    {
        $this->admin();
        $settings = SiteSetting::create(['founded_year' => 1993]);

        $page = Livewire::test(EditSiteSetting::class, ['record' => $settings->getRouteKey()])
            ->assertDontSee('Delete')
            ->assertSee('Catalogue & contact')
            ->fillForm(['whatsapp_number' => '919876543210'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('919876543210', $settings->fresh()->whatsapp_number);
        $this->assertFalse(SiteSettingResource::canDelete($settings));
        $this->assertFalse(SiteSettingResource::canDeleteAny());
        $this->assertSame([], $page->instance()->getBreadcrumbs());
    }

    public function test_only_admins_reach_the_settings_page(): void
    {
        $settings = SiteSetting::create(['founded_year' => 1993]);
        $this->actingAs(User::factory()->create(['role' => 'editor', 'is_active' => true]));

        $this->get('/admin/site-settings/' . $settings->id . '/edit')->assertForbidden();
    }
}
