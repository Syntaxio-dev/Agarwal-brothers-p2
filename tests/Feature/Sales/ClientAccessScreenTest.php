<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesClients\Pages\EditSalesClient;
use App\Sales\Filament\Resources\SalesClients\SalesClientResource;
use App\Sales\Models\SalesClient;
use Livewire\Livewire;

class ClientAccessScreenTest extends SalesTestCase
{
    public function test_the_screen_renders_and_lists_reps(): void
    {
        $this->actingAs($this->manager());
        $this->rep('REP001', '9000000001', name: 'Rahul Verma');
        $client = SalesClient::create(['name' => 'Welham School', 'city' => 'Dehradun', 'lat' => 30.3, 'lng' => 78.0]);

        Livewire::test(EditSalesClient::class, ['record' => $client->getRouteKey()])
            ->assertSee('Who sees this client')->assertSee('Rahul Verma')->assertSee('Nobody will see Welham School except the office.');
    }

    public function test_clients_opens_on_the_screen_and_the_table_is_one_click_away(): void
    {
        $this->actingAs($this->manager());
        $client = SalesClient::create(['name' => 'Welham School', 'city' => 'Dehradun', 'lat' => 30.3, 'lng' => 78.0]);

        $this->assertSame(url('/sales/sales-clients/' . $client->id . '/edit'), SalesClientResource::getNavigationUrl());
        $this->get('/sales/sales-clients')->assertOk()->assertSee('Welham School');   // the table is still there
    }
}
