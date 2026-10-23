<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientListTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_client_list_file_is_clean(): void
    {
        $data = require database_path('data/clients.php');
        $categories = \App\Models\ClientCategory::pluck('name')->all();

        $this->assertCount(50, $data);
        foreach ($data as $name => [$category, $city, $note]) {
            $this->assertLessThanOrEqual(180, mb_strlen($note), "{$name}: note is longer than the admin limit of 180");
            if ($category !== '') {
                $this->assertContains($category, $categories, "{$name}: unknown category {$category}");
            }
            // nothing hedged ("likely", "verify", "to confirm") may reach the public site
            $this->assertDoesNotMatchRegularExpression('/\b(likely|tentative|verify|to confirm|confirm the)\b/i', $note . ' ' . $name, $name);
        }
    }

    public function test_clients_load_adds_every_client_with_its_category_and_never_touches_existing_ones(): void
    {
        $data = require database_path('data/clients.php');
        Client::create(['name' => 'Amul', 'testimonial' => 'My own text', 'is_active' => false]);

        $this->artisan('clients:load')->assertSuccessful();

        $this->assertSame(count($data), Client::count());
        $amul = Client::where('name', 'Amul')->first();
        $this->assertSame('My own text', $amul->testimonial);      // existing client left alone
        $this->assertFalse($amul->is_active);

        $dabur = Client::where('name', 'Dabur')->with('category')->first();
        $this->assertSame('Food Processing & Packaged Foods', $dabur->category->name);
        $this->assertNotEmpty($dabur->testimonial);
        $this->assertTrue($dabur->is_active);

        $jodhpur = Client::where('name', 'IIT Jodhpur')->with('category')->first();
        $this->assertSame('Universities/Colleges/Schools', $jodhpur->category->name);
        $this->assertSame('Jodhpur', $jodhpur->city);

        // names whose identity is unconfirmed have no description at all
        $this->assertNull(Client::where('name', 'Lotus Dairy')->value('testimonial'));
        $this->assertNull(Client::where('name', 'Devraj Hotels')->value('client_category_id'));

        $this->artisan('clients:load')->assertSuccessful();          // a second run adds nothing
        $this->assertSame(count($data), Client::count());
    }

    public function test_home_shows_the_loaded_clients_with_their_categories(): void
    {
        $this->artisan('clients:load')->assertSuccessful();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Labs that rely', $html);
        $this->assertStringNotContainsString(' more client', $html);           // the '+N more clients' strip was removed
        $this->assertStringContainsString('Dairy &amp; Dairy Products', $html);          // wheel category
        $this->assertStringContainsString('Research Institutes', $html);
        $this->assertStringNotContainsString('Empty Category', $html);
    }
}
