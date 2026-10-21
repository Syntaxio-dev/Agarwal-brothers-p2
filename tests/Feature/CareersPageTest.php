<?php

namespace Tests\Feature;

use App\Filament\Resources\JobOpenings\Pages\CreateJobOpening;
use App\Models\JobOpening;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CareersPageTest extends TestCase
{
    use RefreshDatabase;

    private function role(string $title, ?string $department, array $over = []): JobOpening
    {
        return JobOpening::create($over + [
            'title' => $title, 'slug' => str($title)->slug() . '-' . uniqid(), 'location' => 'Jaipur', 'employment_type' => 'Full Time',
            'department' => $department, 'is_active' => true,
        ]);
    }

    public function test_a_company_facts_strip_sits_between_the_hero_and_the_open_roles(): void
    {
        $html = $this->get(route('careers'))->assertOk()->getContent();

        $order = array_map(fn ($n) => strpos($html, $n), ['We are hiring', 'Years of experience', 'Careers @ Agarwal Brothers']);

        $this->assertNotContains(false, $order);
        $this->assertSame($order, collect($order)->sort()->values()->all());
        $this->assertStringContainsString('30+', $html);
    }

    public function test_experience_and_salary_show_as_tags_only_when_they_are_filled(): void
    {
        $this->role('Sales Executive', 'Sales', ['experience' => '2-4 years', 'salary' => 'Rs 3-5 LPA']);
        $this->role('Office Assistant', 'Office');

        $html = $this->get(route('careers'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '2-4 years'));
        $this->assertSame(1, substr_count($html, 'Rs 3-5 LPA'));
    }

    public function test_the_role_page_shows_experience_and_salary_too(): void
    {
        $r = $this->role('Service Engineer', 'Service', ['experience' => 'Fresher welcome', 'salary' => 'As per industry standards']);

        $this->get(route('careers.show', $r->slug))->assertOk()->assertSee('Fresher welcome')->assertSee('As per industry standards');
    }

    public function test_each_department_gets_its_own_accent_colour_from_the_palette(): void
    {
        $this->role('Sales Executive', 'Sales');
        $this->role('Service Engineer', 'Service');
        $this->role('Accounts Executive', 'Accounts');
        $this->role('Stores Assistant', 'Operations');
        $this->role('Mystery Role', 'Gardening');

        $html = $this->get(route('careers'))->assertOk()->getContent();

        foreach (['border-l-link', 'border-l-success', 'border-l-cyan', 'border-l-navy', 'border-l-slate'] as $edge) {
            $this->assertStringContainsString($edge, $html, $edge);
        }
    }

    public function test_department_chips_appear_only_for_long_lists_with_more_than_one_department(): void
    {
        foreach (range(1, 5) as $i) {
            $this->role("Sales role $i", 'Sales');
        }
        $this->assertStringNotContainsString('Filter roles by department', $this->get(route('careers'))->getContent());

        $this->role('Service Engineer', 'Service');
        $html = $this->get(route('careers'))->assertOk()->getContent();

        $this->assertStringContainsString('Filter roles by department', $html);
        $this->assertStringContainsString('All roles', $html);
        $this->assertStringContainsString('(5)', $html);                       // Sales has 5
        $this->assertSame(6, substr_count($html, "x-show=\"dept === 'all' ||"));   // every card can be filtered
    }

    public function test_six_roles_in_one_department_need_no_chips(): void
    {
        foreach (range(1, 6) as $i) {
            $this->role("Sales role $i", 'Sales');
        }

        $this->assertStringNotContainsString('Filter roles by department', $this->get(route('careers'))->getContent());
    }

    public function test_the_empty_state_has_a_share_your_profile_button(): void
    {
        $html = $this->get(route('careers'))->assertOk()->getContent();

        $this->assertStringContainsString('No open positions right now.', $html);
        $this->assertMatchesRegularExpression('/<a href="#apply"[^>]*>\s*Share your profile/', substr($html, strpos($html, 'No open positions right now.')));
    }

    public function test_the_admin_can_save_experience_and_salary(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));

        Livewire::test(CreateJobOpening::class)
            ->fillForm([
                'title' => 'Sales Executive', 'slug' => 'sales-executive', 'location' => 'Jaipur', 'employment_type' => 'Full Time',
                'department' => 'Sales', 'experience' => '1-3 years', 'salary' => 'Rs 2-4 LPA', 'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_openings', ['slug' => 'sales-executive', 'experience' => '1-3 years', 'salary' => 'Rs 2-4 LPA']);
    }

    public function test_the_page_has_an_employer_intro_with_real_facts_and_a_hiring_process_after_the_roles(): void
    {
        $html = $this->get(route('careers'))->assertOk()->getContent();

        $order = array_map(fn ($n) => strpos($html, $n), [
            'Years of experience', 'Life at Agarwal Brothers', 'Who we are', 'Careers @ Agarwal Brothers', 'What happens after you', "Don't see a perfect role?",
        ]);
        $this->assertNotContains(false, $order);
        $this->assertSame($order, collect($order)->sort()->values()->all());

        // facts are the real ones, never the old 43 / 1981
        $this->assertStringContainsString('since 1993', $html);
        $this->assertStringContainsString('50+ global brands', $html);
        $this->assertStringContainsString('S3 Road No.1', $html);
        $this->assertStringNotContainsString('43', substr($html, strpos($html, 'Who we are'), 2500));
        $this->assertStringNotContainsString('1981', $html);

        // four steps, first to last, drawn by the shared flow
        foreach (['Apply online', 'We review your profile', 'Interview', 'Offer and joining'] as $step) {
            $this->assertStringContainsString($step, $html);
        }
        $this->assertSame(1, substr_count($html, 'data-reveal="draw"'));
        $this->assertStringContainsString('flow-line', $html);
        $this->assertStringNotContainsString('{$', $html);
    }
}
