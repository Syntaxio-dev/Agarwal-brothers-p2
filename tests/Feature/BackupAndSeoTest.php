<?php

namespace Tests\Feature;

use App\Support\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackupAndSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_dump_contains_tables(): void
    {
        $path = BackupService::database();

        try {
            $sql = file_get_contents($path);
            $this->assertStringContainsString('CREATE TABLE `users`', $sql);
            $this->assertStringContainsString('CREATE TABLE `products`', $sql);
        } finally {
            @unlink($path);
        }
    }

    public function test_backup_names_are_validated(): void
    {
        $this->assertNull(BackupService::path('../.env'));
        $this->assertNull(BackupService::path('anything.zip'));
        $this->assertFalse(BackupService::delete('../../composer.json'));
    }

    public function test_pages_have_title_and_canonical(): void
    {
        $this->get('/contact-us')->assertOk()->assertSee('<link rel="canonical"', false)->assertSee('<title>', false);
    }

    public function test_non_production_is_noindex(): void
    {
        $this->get('/')->assertSee('noindex', false);
    }

    public function test_security_headers_are_set(): void
    {
        $this->get('/')->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
