<?php

namespace App\Sales\Filament\Forms;

use App\Sales\Models\SalesClient;
use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Utilities\Set;

/**
 * "Pick the exact location on a map" for a client: a map with a pin fixed in the middle (like choosing a delivery address), a place
 * search, and a box that also takes a pasted Google Maps link or "lat, lng". It fills the `lat` and `lng` boxes of the same form.
 * The map pictures come from OpenStreetMap, the leaflet files are served from /vendor/leaflet (no outside script).
 */
class MapSpotPicker extends Field
{
    protected string $view = 'filament.sales.forms.map-spot-picker';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);

        // an existing client opens the map on its spot
        $this->afterStateHydrated(function (MapSpotPicker $component, $record) {
            if ($record && $record->lat !== null && $record->lng !== null) {
                $component->state(round((float) $record->lat, 6) . ', ' . round((float) $record->lng, 6));
            }
        });

        // moving the pin (or pasting a link) fills the latitude and longitude boxes
        $this->live(onBlur: false, debounce: 400);
        $this->afterStateUpdated(function ($state, Set $set) {
            if ($spot = SalesClient::parseSpot($state)) {
                $set('lat', $spot['lat']);
                $set('lng', $spot['lng']);
            }
        });
    }
}
