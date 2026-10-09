<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, bool $active = true): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => $active]);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/users')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_the_main_areas(): void
    {
        $this->actingAs($this->user('admin'));
        foreach (['/admin', '/admin/products', '/admin/enquiries', '/admin/users', '/admin/site-settings', '/admin/backups', '/admin/help'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_roles_see_only_their_areas(): void
    {
        $matrix = [
            'editor' => ['ok' => ['/admin/products', '/admin/insights', '/admin/help'], 'no' => ['/admin/users', '/admin/backups', '/admin/enquiries', '/admin/job-applications']],
            'sales' => ['ok' => ['/admin/enquiries', '/admin/contact-messages', '/admin/help'], 'no' => ['/admin/users', '/admin/job-openings', '/admin/brands']],
            'hr' => ['ok' => ['/admin/job-openings', '/admin/job-applications', '/admin/help'], 'no' => ['/admin/users', '/admin/products', '/admin/enquiries']],
        ];

        foreach ($matrix as $role => $paths) {
            $this->actingAs($this->user($role));
            foreach ($paths['ok'] as $url) {
                $this->get($url)->assertOk();
            }
            foreach ($paths['no'] as $url) {
                $this->get($url)->assertForbidden();
            }
        }
    }

    public function test_inactive_user_cannot_enter_the_panel(): void
    {
        $this->actingAs($this->user('admin', active: false));
        $this->get('/admin')->assertForbidden();
    }

    public function test_unknown_role_cannot_enter(): void
    {
        $this->actingAs($this->user('ghost'));
        $this->get('/admin')->assertForbidden();
    }

    public function test_last_active_admin_cannot_be_deleted(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);
        $this->assertFalse($admin->delete());

        $other = $this->user('admin');
        $this->assertTrue($other->delete());
        $this->assertNotNull($admin->fresh());
    }
}
