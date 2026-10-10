<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\JobOpening;
use App\Support\DialCodes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormRulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // These tests post many times in a row; the 5-per-minute limit is not what is being tested here.
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function contact(array $over = []): array
    {
        return array_merge(['name' => 'Asha Verma', 'email' => 'a@example.com', 'phone' => '9876543210', 'message' => 'Hello'], $over);
    }

    public function test_valid_names_are_accepted(): void
    {
        foreach (['Asha Verma', "Ravi O'Neil", 'Anne-Marie', 'Dr. R. K. Sharma', 'José Núñez', 'आयुष शर्मा'] as $name) {
            $this->post(route('contact.store'), $this->contact(['name' => $name]))->assertSessionHasNoErrors();
        }
        $this->assertSame(6, ContactMessage::count());
    }

    public function test_names_with_digits_symbols_or_markup_are_rejected(): void
    {
        foreach (['Asha123', '<script>alert(1)</script>', 'Ravi@Labs', 'A', '12345', '   ', 'Ravi; DROP TABLE', 'http://spam.com'] as $name) {
            $this->post(route('contact.store'), $this->contact(['name' => $name]))->assertSessionHasErrors('name');
        }
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_phone_must_be_exactly_ten_digits(): void
    {
        foreach (['12345', '12345678901', '98765abcde', '+919876543210', '98765 4321'] as $phone) {
            $this->post(route('contact.store'), $this->contact(['phone' => $phone]))->assertSessionHasErrors('phone');
        }
        // spaces and dashes typed between digits are tidied away
        $this->post(route('contact.store'), $this->contact(['phone' => '98765-43210']))->assertSessionHasNoErrors();
    }

    public function test_phone_is_stored_with_the_chosen_country_code(): void
    {
        $this->post(route('contact.store'), $this->contact(['phone' => '9876543210']));
        $this->post(route('contact.store'), $this->contact(['phone' => '501234567', 'phone_country' => 'AE']));  // 9 digits: rejected
        $this->post(route('contact.store'), $this->contact(['phone' => '5012345678', 'phone_country' => 'AE']));
        $this->post(route('contact.store'), $this->contact(['phone' => '5012345679', 'phone_country' => 'XX']))->assertSessionHasErrors('phone_country');

        $this->assertSame(['+91 9876543210', '+971 5012345678'], ContactMessage::orderBy('id')->pluck('phone')->all());
    }

    public function test_phone_is_optional_on_product_enquiries_but_checked_when_given(): void
    {
        $brand = \App\Models\Brand::create(['name' => 'B', 'slug' => 'b', 'is_active' => true]);
        $cat = \App\Models\Category::create(['brand_id' => $brand->id, 'name' => 'C', 'slug' => 'c']);
        $p = \App\Models\Product::create(['category_id' => $cat->id, 'name' => 'P', 'slug' => 'p', 'is_active' => true]);

        $ok = ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'r@example.com'];
        $this->post(route('enquiry.store'), $ok)->assertSessionHasNoErrors();
        $this->post(route('enquiry.store'), $ok + ['phone' => '123'])->assertSessionHasErrors('phone');
        $this->post(route('enquiry.store'), $ok + ['phone' => '9876543210', 'phone_country' => 'GB'])->assertSessionHasNoErrors();

        $this->assertSame([null, '+44 9876543210'], Enquiry::orderBy('id')->pluck('phone')->all());
    }

    public function test_failed_submit_returns_to_the_form_with_values_kept(): void
    {
        $this->from('/contact-us')->post(route('contact.store'), $this->contact(['name' => 'Bad123', 'company' => 'Sharma Labs']))
            ->assertRedirect('/contact-us#contact-form')
            ->assertSessionHasErrors('name')
            ->assertSessionHasInput('company', 'Sharma Labs');
    }

    public function test_career_application_checks_name_and_phone_too(): void
    {
        Storage::fake('local');
        $opening = JobOpening::create(['title' => 'Service Engineer', 'slug' => 'service-engineer', 'location' => 'Jaipur', 'is_active' => true]);

        $this->get(route('careers.show', $opening))->assertOk()->assertSee('formGuard', false)->assertSee('countryPicker', false);
        $this->get(route('careers'))->assertOk();

        $file = fn () => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
        $good = ['name' => 'Asha Verma', 'email' => 'a@example.com', 'phone' => '9876543210'];

        $this->post(route('careers.apply', $opening), array_merge($good, ['name' => 'Asha 99', 'resume' => $file()]))->assertSessionHasErrors('name');
        $this->post(route('careers.apply', $opening), array_merge($good, ['phone' => '12', 'resume' => $file()]))->assertSessionHasErrors('phone');
        $this->post(route('careers.apply', $opening), $good + ['resume' => $file()])->assertSessionHasNoErrors();

        $this->assertSame('+91 9876543210', \App\Models\JobApplication::firstOrFail()->phone);
    }

    public function test_forms_render_the_country_picker_and_guard(): void
    {
        $this->get('/contact-us')->assertOk()->assertSee('countryPicker', false)->assertSee('formGuard', false)->assertSee('phone_country', false);
        $this->assertContains('IN', DialCodes::isos());
        $this->assertSame('91', DialCodes::code(null));
        $this->assertSame('971', DialCodes::code('ae'));
    }
}
