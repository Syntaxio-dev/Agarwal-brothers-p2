<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrollRevealTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_sections_below_the_first_screen_are_marked_to_reveal(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertGreaterThanOrEqual(7, substr_count($html, 'data-reveal'));          // at least the section headings
        // the hero carousel (first section) is never animated, so the first screen shows at once
        $firstSection = strstr(strstr($html, '<section'), '</section>', true);
        $this->assertStringNotContainsString('data-reveal', $firstSection);
    }

    public function test_the_page_has_a_failsafe_so_nothing_stays_hidden(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('reveal-failsafe', $html);
        $this->assertStringContainsString("classList.add('js')", $html);
    }

    public function test_the_reveal_styles_respect_reduced_motion_and_only_animate_opacity_and_position(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('@media (prefers-reduced-motion: no-preference)', $css);
        $this->assertMatchesRegularExpression('/\.js:not\(\.reveal-failsafe\) \[data-reveal\]:not\(\.is-revealed\)\s*\{\s*opacity:\s*0;/', $css);
        // each entrance animation starts from a faded, slightly moved position and ends at the element's own style
        foreach (['up', 'left', 'right', 'zoom', 'fade'] as $kind) {
            $this->assertStringContainsString("@keyframes reveal-{$kind}", $css);
        }
        $this->assertStringContainsString('600ms', $css);                                   // balanced speed
    }

    public function test_tall_blocks_are_split_up_and_get_a_bigger_entrance(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $js = file_get_contents(resource_path('js/reveal.js'));

        $this->assertStringContainsString('@keyframes reveal-up-lg', $css);
        $this->assertStringContainsString('reveal-lg', $js);
        // an item starts once 200px (or 60% of a small item) is on screen, not at the first visible pixel
        $this->assertStringContainsString('MIN_VISIBLE_PX = 200', $js);

        // blogs and news are not one big block any more: rows, cover, cards and the links under them each reveal
        $template = file_get_contents(resource_path('views/public/home.blade.php'));
        $this->assertGreaterThanOrEqual(25, substr_count($template, 'data-reveal'));
        $this->assertStringContainsString('<button data-reveal="left"', $template);    // blog rows
        $this->assertStringContainsString('<div data-reveal="right" class="order-1 lg:order-2', $template);   // blog cover
    }

    public function test_each_brand_row_stops_on_its_own_when_hovered(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('.brand-marquee-track:hover', $html);
        $this->assertStringContainsString('.brand-marquee-track:focus-within', $html);
        // the old "stop everything" switch on the shared container is gone
        $this->assertStringNotContainsString("'is-paused': paused", $html);
        $this->assertDoesNotMatchRegularExpression('/data-reveal="fade" x-data="\{ paused/', $html);
    }
}
