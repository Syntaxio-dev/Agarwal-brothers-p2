<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeCapabilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_what_we_do_row_sits_between_the_hero_and_who_we_are(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $order = array_map(fn ($n) => strpos($html, $n), ['No slides added yet', 'What we do', 'One partner,', 'Who We Are']);
        $this->assertNotContains(false, $order);
        $this->assertSame($order, collect($order)->sort()->values()->all());
    }

    public function test_it_shows_three_capabilities_with_the_current_brand_count_and_no_amc_card(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $block = substr($html, strpos($html, 'capabilities-heading'), 7000);

        foreach (['Supply', 'Installation', 'Calibration &amp; Service'] as $title) {
            $this->assertStringContainsString($title, $block);
        }
        $this->assertStringNotContainsString('AMC', $block);
        $this->assertStringContainsString('keep labs accurate', $block);
        $this->assertStringContainsString(\App\Support\Company::plus('brands') . ' global brands', $block);   // from config, never a typed number

        $this->assertSame(3, substr_count($block, 'class="cap-card '));
        $this->assertSame(3, substr_count($block, '<h3 class="relative mt-5 text-lg font-bold text-navy">'));
        $this->assertSame(3, substr_count($block, 'text-7xl font-extrabold'));                  // the ghost number of each card
        $this->assertSame(3, substr_count($block, 'bg-gradient-to-br from-navy to-link'));      // all icon tiles are the same gradient
        $this->assertStringNotContainsString('bg-cyan text-navy', $block);
        $this->assertStringContainsString('md:grid-cols-3', $block);                            // three equal columns, no orphan card

        // the dashed connector is decorative and drawn by the page's reveal system
        $this->assertMatchesRegularExpression('/<div aria-hidden="true" class="cap-line [^"]*h-\[3px\][^"]*repeating-linear-gradient/', $block);   // 3 px, long dashes
        $this->assertStringContainsString('data-reveal="draw"', $block);
        $this->assertStringContainsString('--cap-delay: 150ms', $block);
        $this->assertStringContainsString('--cap-delay: 270ms', $block);
        $this->assertStringContainsString('--cap-delay: 390ms', $block);
    }

    public function test_the_line_and_stagger_animations_exist_and_only_run_when_motion_is_allowed(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $motion = substr($css, strpos($css, '@media (prefers-reduced-motion: no-preference)'));

        $this->assertStringContainsString('.cap-line', $motion);
        $this->assertStringContainsString('.cap-card', $motion);
        $this->assertStringContainsString('@keyframes cap-up', $css);
    }
}
