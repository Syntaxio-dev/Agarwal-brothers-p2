<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_industries_numbers_and_every_active_client_with_details(): void
    {
        Client::create(['name' => 'Amul Dairy', 'industry' => 'dairy', 'city' => 'Anand', 'since_year' => 2011, 'supplied' => 'Milk analysers', 'is_active' => true]);
        Client::create(['name' => 'IIT Delhi', 'industry' => 'academia', 'city' => 'Delhi', 'is_active' => true, 'is_featured' => true]);
        Client::create(['name' => 'Hidden Lab', 'industry' => 'dairy', 'is_active' => false]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('All sectors', $html);
        $this->assertStringContainsString('Dairy &amp; Food', $html);
        $this->assertStringContainsString('Universities &amp; Institutes', $html);
        $this->assertStringNotContainsString('Pharma &amp; Biotech', $html);   // no client there, so no tile
        $this->assertStringContainsString('Amul Dairy', $html);
        $this->assertStringContainsString('Milk analysers', $html);
        $this->assertStringNotContainsString('Hidden Lab', $html);
        // top clients come first on the wall
        $this->assertLessThan(strpos($html, 'Amul Dairy'), strpos($html, 'IIT Delhi'));
    }

    public function test_the_section_is_left_out_when_there_are_no_clients(): void
    {
        $this->get('/')->assertOk()->assertDontSee('All sectors')->assertDontSee('Trusted by');
    }

    public function test_every_industry_has_a_label_blurb_and_icon(): void
    {
        foreach (config('industries') as $key => $industry) {
            $this->assertNotEmpty($industry['label'], $key);
            $this->assertNotEmpty($industry['blurb'], $key);
            $this->assertNotEmpty($industry['icon'], $key);
        }
    }
}
