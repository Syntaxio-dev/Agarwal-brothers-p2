<?php

namespace Tests\Feature\Sales;

use App\Sales\Support\MapLinkResolver;
use Illuminate\Support\Facades\Http;

/** A rep copies a place from Google Maps; the office reads the exact spot out of the link. */
class MapLinkTest extends SalesTestCase
{
    private function ask(array $headers, string $text)
    {
        return $this->postJson('/api/sales/v1/tools/resolve-map-link', ['text' => $text], $headers);
    }

    public function test_numbers_and_long_links_need_no_trip_to_google(): void
    {
        Http::fake();

        $this->assertSame(['lat' => 26.9124, 'lng' => 75.7873, 'name' => null], MapLinkResolver::resolve('26.9124, 75.7873'));
        $long = 'https://www.google.com/maps/place/Agarwal+Brothers/@26.9100,75.7800,17z/data=!3m1!4b1!4m6!3m5!1s0x0:0x0!8m2!3d26.9124!4d75.7873';
        $found = MapLinkResolver::resolve($long);

        $this->assertSame(26.9124, $found['lat'], 'the exact place (!3d!4d) beats the centre of the screen (@)');
        $this->assertSame('Agarwal Brothers', $found['name']);
        Http::assertNothingSent();
    }

    /** What Google really answers for a business share link: a redirect to the place page (no numbers in it), then the embed page with the position. */
    private function fakeGoogle(string $ftidInEmbed = '0x396db3c9049b73ad:0x15aeb384d982bc97'): void
    {
        $place = 'https://www.google.com/maps/place/XQ4P%2B7VJ+Welham+Senior+Secondary+School,+Sector+4,+Jaipur+302039/data=!4m2!3m1!1s0x396db3c9049b73ad:0x15aeb384d982bc97!18m1!1e1?entry=gps';

        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => $place]),
            'www.google.com/maps/place/*' => Http::response('<html>the page shell, no numbers</html>', 200),
            'www.google.com/maps/embed*' => Http::response('x[[[3556.35,75.7871329,26.9557035],[0,0,0],null,13.1],null,null,[["' . $ftidInEmbed . '","XQ4P+7VJ Welham Senior Secondary School, Sector 4, Jaipur",null]]]', 200),
        ]);
    }

    public function test_a_short_share_link_of_a_business_is_followed_and_the_place_is_read(): void
    {
        $this->fakeGoogle();
        $headers = $this->headers($this->rep());

        $answer = $this->ask($headers, "Welham Senior Secondary School\nSector 4, Jaipur\nhttps://maps.app.goo.gl/2anvFoV913eV3ik97?g_st=ic")->assertOk();

        $this->assertSame(26.955704, $answer->json('lat'));
        $this->assertSame(75.787133, $answer->json('lng'));
        $this->assertSame('Welham Senior Secondary School', $answer->json('name'));
    }

    public function test_a_place_with_another_internal_id_is_not_taken(): void
    {
        $this->fakeGoogle('0x111111111111111:0x2222222222222222');

        $this->ask($this->headers($this->rep()), 'https://maps.app.goo.gl/2anvFoV913eV3ik97')->assertNotFound();
    }

    public function test_a_short_link_whose_redirect_carries_the_numbers_needs_no_second_trip(): void
    {
        Http::fake([
            'maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'https://www.google.com/maps/place/Agarwal+Brothers/@26.91,75.78,17z/data=!3d26.9124!4d75.7873']),
        ]);

        $answer = $this->ask($this->headers($this->rep()), 'https://maps.app.goo.gl/abc123XYZ')->assertOk();

        $this->assertSame(26.9124, $answer->json('lat'));
        Http::assertSentCount(1);
    }

    public function test_the_office_opens_google_maps_addresses_only(): void
    {
        Http::fake();
        $headers = $this->headers($this->rep());

        foreach ([
            'https://evil.example/maps/place/x',
            'https://maps.app.goo.gl.evil.example/abc',
            'http://maps.app.goo.gl/abc',
            'https://www.google.com/search?q=cats',
            'https://169.254.169.254/latest/meta-data',
            'https://user:pw@maps.app.goo.gl/abc',
        ] as $bad) {
            $this->ask($headers, $bad)->assertNotFound();
        }

        Http::assertNothingSent();
    }

    public function test_a_redirect_to_another_host_is_not_followed(): void
    {
        Http::fake(['maps.app.goo.gl/*' => Http::response('', 302, ['Location' => 'https://evil.example/steal'])]);

        $this->ask($this->headers($this->rep()), 'https://maps.app.goo.gl/abc')->assertNotFound();

        Http::assertSentCount(1);
    }

    public function test_only_signed_in_reps_can_ask(): void
    {
        $this->postJson('/api/sales/v1/tools/resolve-map-link', ['text' => '26.9124, 75.7873'])->assertUnauthorized();
        $this->ask($this->headers($this->rep()), 'ab')->assertStatus(422);
    }
}
