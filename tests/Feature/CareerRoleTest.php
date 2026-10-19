<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerRoleTest extends TestCase
{
    use RefreshDatabase;

    private function apply(array $over = [])
    {
        Storage::fake('local');

        return $this->withoutMiddleware(ThrottleRequests::class)->post('/careers/apply', $over + [
            'name' => 'Rohit Kumar',
            'email' => 'rohit@example.com',
            'phone' => '9876543210',
            'phone_country' => 'IN',
            'position' => 'Sales Representative',
            'preferred_location' => 'Jaipur',
            'department' => 'Sales & Marketing',
            'resume' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);
    }

    public function test_the_general_form_has_a_role_dropdown_with_the_office_roles(): void
    {
        $html = $this->get('/careers')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<select id="position" name="position"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input id="position" name="position"/', $html);   // no longer a free text box
        foreach (['Sales Representative', 'Social Media Executive', 'Back Office Executive', 'Telecaller / Tele-sales Executive',
                  'Receptionist / Front Office Executive', 'Accounts Executive', 'Other (type your own role)'] as $role) {
            $this->assertStringContainsString('>' . htmlspecialchars($role, ENT_QUOTES) . '</option>', $html, $role);
        }
        // grouped by department, and a role knows its department so the form can fill it in
        $this->assertStringContainsString('<optgroup label="Sales &amp; Marketing">', $html);
        $this->assertStringContainsString('data-dept="Office &amp; Support"', $html);
        $this->assertStringContainsString('name="position_other"', $html);
    }

    public function test_choosing_a_role_saves_that_role(): void
    {
        $this->apply(['position' => 'Telecaller / Tele-sales Executive'])->assertRedirect();

        $this->assertDatabaseHas('job_applications', ['email' => 'rohit@example.com', 'position' => 'Telecaller / Tele-sales Executive', 'job_opening_id' => null]);
    }

    public function test_other_saves_the_role_the_visitor_typed(): void
    {
        $this->apply(['position' => '__other', 'position_other' => 'Chemist'])->assertRedirect();

        $this->assertDatabaseHas('job_applications', ['email' => 'rohit@example.com', 'position' => 'Chemist']);
        $this->assertDatabaseMissing('job_applications', ['position' => '__other']);
    }

    public function test_other_without_a_typed_role_and_made_up_roles_are_rejected(): void
    {
        $this->apply(['position' => '__other', 'position_other' => ''])->assertSessionHasErrors('position_other');
        $this->apply(['position' => 'Chief Galactic Officer'])->assertSessionHasErrors('position');
        $this->apply(['position' => ''])->assertSessionHasErrors('position');

        $this->assertSame(0, JobApplication::count());
    }

    public function test_a_job_page_has_an_applying_for_dropdown_with_this_job_selected(): void
    {
        $sales = JobOpening::create(['title' => 'Senior Sales Manager', 'slug' => 'senior-sales-manager', 'location' => 'Jaipur', 'is_active' => true, 'sort_order' => 1]);
        $service = JobOpening::create(['title' => 'Service Engineer', 'slug' => 'service-engineer', 'location' => 'Jaipur', 'is_active' => true, 'sort_order' => 2]);
        JobOpening::create(['title' => 'Closed Role', 'slug' => 'closed-role', 'location' => 'Jaipur', 'is_active' => false]);

        $html = $this->get('/careers/' . $sales->slug)->assertOk()->getContent();

        $this->assertStringContainsString('<select id="applying_for"', $html);             // a real dropdown, not a locked box
        $this->assertStringNotContainsString('bg-ice px-4 text-sm font-medium text-navy', $html);
        $this->assertMatchesRegularExpression('/<option value="[^"]*careers\/senior-sales-manager" selected>Senior Sales Manager<\/option>/', $html);   // this job is selected
        $this->assertStringContainsString('careers/service-engineer', $html);              // the other open position can be picked
        $this->assertStringNotContainsString('Closed Role', $html);                         // closed jobs are not offered
        $this->assertStringContainsString('Back Office Executive', $html);                  // and the general office roles
        $this->assertStringContainsString('?role=Back+Office+Executive#apply', $html);
        $this->assertStringNotContainsString('name="position"', $html);                     // the job form still belongs to this job only
    }

    public function test_the_general_form_can_open_with_a_role_already_chosen(): void
    {
        $html = $this->get('/careers?role=' . urlencode('Social Media Executive'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<option value="Social Media Executive" data-dept="Sales &amp; Marketing" selected>/', $html);

        // a role that is not on our list is ignored, nothing is preselected
        $other = $this->get('/careers?role=' . urlencode('Hacker <script>'))->assertOk()->getContent();
        $this->assertDoesNotMatchRegularExpression('/<option value="[^"]*" data-dept="[^"]*" selected/', $other);
        $this->assertStringNotContainsString('<script>alert', $other);
    }

    public function test_the_general_form_stays_the_general_form_when_jobs_are_listed_above_it(): void
    {
        // the job list loops with a variable named $opening; it must not leak into the general form (it used to show a locked "Applying for")
        JobOpening::create(['title' => 'Service Engineer', 'slug' => 'service-engineer', 'location' => 'Jaipur', 'is_active' => true, 'sort_order' => 1]);
        JobOpening::create(['title' => 'Marketing Executive', 'slug' => 'marketing-executive', 'location' => 'Jaipur', 'is_active' => true, 'sort_order' => 2]);

        $html = $this->get('/careers')->assertOk()->getContent();

        $this->assertStringContainsString('<select id="position" name="position"', $html);
        $this->assertStringContainsString('name="preferred_location"', $html);
        $this->assertStringContainsString('name="department"', $html);
        $this->assertStringNotContainsString('id="applying_for"', $html);             // that dropdown belongs to a job page only
        $this->assertStringContainsString('action="' . route('careers.apply.general') . '"', $html);
    }
}
