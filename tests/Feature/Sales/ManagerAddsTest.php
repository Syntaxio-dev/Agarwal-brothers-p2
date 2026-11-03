<?php

namespace Tests\Feature\Sales;

use App\Sales\Filament\Resources\SalesClients\Pages\CreateSalesClient;
use App\Sales\Filament\Resources\SalesClients\Pages\ListSalesClients;
use App\Sales\Filament\Resources\SalesContacts\Pages\ManageSalesContacts;
use App\Sales\Models\SalesClient;
use App\Sales\Models\SalesContact;
use Livewire\Livewire;

/** A manager adds clients (with the exact spot) and people from the Contacts page. */
class ManagerAddsTest extends SalesTestCase
{
    public function test_a_manager_adds_a_client_with_its_spot_from_the_contacts_page(): void
    {
        $this->actingAs($this->manager());

        Livewire::test(ManageSalesContacts::class)
            ->callAction('addClient', ['name' => 'IIT Jodhpur', 'city' => 'Jodhpur', 'lat' => 26.4719, 'lng' => 73.1153])
            ->assertHasNoActionErrors();

        $client = SalesClient::firstWhere('name', 'IIT Jodhpur');
        $this->assertNotNull($client);
        $this->assertNotNull($client->uuid);
        $this->assertSame(26.4719, $client->lat);
        $this->assertSame('office', $client->location_source, 'a spot set on the website is the office\'s');
    }

    public function test_the_place_and_the_spot_are_required(): void
    {
        $this->actingAs($this->manager());

        foreach ([['name' => 'No Place Co', 'lat' => 26.9, 'lng' => 75.8], ['name' => 'No Spot Co', 'city' => 'Jaipur'], ['name' => 'Bad Spot Co', 'city' => 'Jaipur', 'lat' => 200, 'lng' => 75.8]] as $data) {
            Livewire::test(ManageSalesContacts::class)->callAction('addClient', $data)->assertHasActionErrors();
        }

        $this->assertSame(0, SalesClient::count());
    }

    public function test_a_manager_adds_a_person_to_a_client(): void
    {
        $this->actingAs($this->manager());
        $client = SalesClient::create(['name' => 'IIT Jodhpur', 'city' => 'Jodhpur', 'lat' => 26.47, 'lng' => 73.11]);

        Livewire::test(ManageSalesContacts::class)
            ->callAction('create', ['name' => 'Prof Meena', 'client_uuid' => $client->uuid, 'phone' => '9829011223'])
            ->assertHasNoActionErrors();

        $person = SalesContact::firstWhere('name', 'Prof Meena');
        $this->assertSame($client->uuid, $person->client_uuid);
        $this->assertSame(auth()->id(), $person->user_id);
    }

    public function test_a_client_a_manager_adds_reaches_the_rep_it_is_given_to(): void
    {
        $this->actingAs($this->manager());
        $profile = $this->rep();
        $headers = $this->headers($profile);

        Livewire::test(ManageSalesContacts::class)
            ->callAction('addClient', ['name' => 'IIT Jodhpur', 'city' => 'Jodhpur', 'lat' => 26.4719, 'lng' => 73.1153, 'assigned_to' => $profile->user_id])
            ->assertHasNoActionErrors();

        $clients = $this->getJson('/api/sales/v1/sync/pull', $headers)->assertOk()->json('clients');
        $this->assertSame('IIT Jodhpur', $clients[0]['name']);
        $this->assertSame('office', $clients[0]['location_source']);
    }

    public function test_the_map_picker_fills_the_latitude_and_longitude(): void
    {
        $this->actingAs($this->manager());

        $page = Livewire::test(CreateSalesClient::class)
            ->assertFormFieldExists('map_spot')
            ->assertSee('Search the shop, college or address')
            ->set('data.map_spot', 'https://www.google.com/maps/place/Jodhpur/@26.4719,73.1153,17z')
            ->assertSet('data.lat', 26.4719)
            ->assertSet('data.lng', 73.1153);

        $page->set('data.map_spot', '26.9124, 75.7873')->assertSet('data.lat', 26.9124);

        $page->fillForm(['name' => 'IIT Jodhpur', 'city' => 'Jodhpur'])->call('create')->assertHasNoFormErrors();
        $client = SalesClient::firstWhere('name', 'IIT Jodhpur');
        $this->assertSame(26.9124, $client->lat);
        $this->assertSame('office', $client->location_source);
    }

    public function test_the_clients_page_has_add_client_and_add_contact_person(): void
    {
        $this->actingAs($this->manager());
        $client = SalesClient::create(['name' => 'IIT Jodhpur', 'city' => 'Jodhpur', 'lat' => 26.47, 'lng' => 73.11]);

        Livewire::test(ListSalesClients::class)
            ->assertActionExists('create')
            ->assertActionExists('addContactPerson')
            ->callAction('addContactPerson', ['name' => 'Prof Meena', 'client_uuid' => $client->uuid, 'phone' => '9829011223'])
            ->assertHasNoActionErrors();

        $this->assertSame($client->uuid, SalesContact::firstWhere('name', 'Prof Meena')->client_uuid);

        Livewire::test(ListSalesClients::class)
            ->callAction('addContactPerson', ['name' => 'Walk In Buyer', 'phone' => '9829011224'])
            ->assertHasNoActionErrors();
        $this->assertNull(SalesContact::firstWhere('name', 'Walk In Buyer')->client_uuid, 'a person without a company');
    }
}
