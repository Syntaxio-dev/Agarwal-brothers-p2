<?php

namespace Tests\Feature;

use App\Filament\Resources\TeamMembers\Pages\CreateTeamMember;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_team_card_shows_contact_details_that_slide_up_on_hover(): void
    {
        TeamMember::create(['name' => 'Rupa Maheshwari', 'designation' => 'Enquiries & Quotations', 'phone' => '+91 93529 35901', 'email' => 'rupa@example.com', 'linkedin_url' => 'https://www.linkedin.com/in/rupa', 'is_active' => true]);
        TeamMember::create(['name' => 'No Contact Person', 'designation' => 'Dispatch', 'is_active' => true]);

        $html = $this->get(route('our-story'))->assertOk()->getContent();

        $this->assertStringContainsString('href="tel:+919352935901"', $html);
        $this->assertStringContainsString('href="mailto:rupa@example.com"', $html);
        $this->assertStringContainsString('https://www.linkedin.com/in/rupa', $html);
        // hidden below the photo until hover or keyboard focus; always shown where there is no hover (phones)
        $this->assertStringContainsString('group-hover:translate-y-0', $html);
        $this->assertStringContainsString('group-focus-within:translate-y-0', $html);
        $this->assertStringContainsString('[@media(hover:none)]:translate-y-0', $html);

        // a person without any contact detail gets no overlay at all
        $card = substr($html, strpos($html, 'No Contact Person') - 1500, 1500);
        $this->assertStringNotContainsString('team-contact', $card);
        $this->assertSame(1, substr_count($html, 'team-contact absolute'));
    }

    public function test_an_unsafe_linkedin_address_is_not_linked(): void
    {
        TeamMember::create(['name' => 'Bad Link', 'designation' => 'X', 'phone' => '+91 98290 15024', 'linkedin_url' => 'javascript:alert(1)', 'is_active' => true]);

        $html = $this->get(route('our-story'))->getContent();

        $this->assertStringContainsString('tel:+919829015024', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
    }

    public function test_the_admin_form_saves_and_checks_the_contact_fields(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'editor', 'is_active' => true]));

        Livewire::test(CreateTeamMember::class)
            ->fillForm(['name' => 'Teena Sharma', 'designation' => 'Dispatch Executive', 'phone' => 'call me maybe', 'email' => 'not-an-email'])
            ->call('create')
            ->assertHasFormErrors(['phone', 'email']);

        Livewire::test(CreateTeamMember::class)
            ->fillForm(['name' => 'Teena Sharma', 'designation' => 'Dispatch Executive', 'phone' => '+91 98290 15024', 'email' => 'teena@example.com'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(['+91 98290 15024', 'teena@example.com'], [TeamMember::first()->phone, TeamMember::first()->email]);

        // both are optional
        Livewire::test(CreateTeamMember::class)
            ->fillForm(['name' => 'Monu Yogi', 'designation' => 'GeM Portal Executive'])
            ->call('create')
            ->assertHasNoFormErrors();
    }
}
