<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterGemTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_shows_the_gem_logo_with_a_safe_external_link(): void
    {
        $this->assertFileExists(public_path('images/gem-logo.png'));

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Also available on', $html);
        $this->assertStringContainsString('images/gem-logo.png', $html);
        $this->assertMatchesRegularExpression('/href="https:\/\/gem\.gov\.in"[^>]*target="_blank"[^>]*rel="noopener noreferrer"/s', $html);
        $this->assertStringContainsString('alt="GeM - Government e Marketplace"', $html);
    }

    public function test_the_gem_logo_is_in_the_footer_of_other_pages_too(): void
    {
        $this->get('/our-story')->assertOk()->assertSee('images/gem-logo.png', false);
        $this->get('/contact-us')->assertOk()->assertSee('images/gem-logo.png', false);
    }
}
