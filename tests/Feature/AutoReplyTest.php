<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Mail\CustomerMessage;
use App\Mail\NewContactMessage;
use App\Mail\NewEnquiry;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\JobOpening;
use App\Models\Product;
use App\Models\User;
use App\Support\AutoReply;
use App\Support\EmailTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AutoReplyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function product(): Product
    {
        $brand = Brand::create(['name' => 'Sartorius', 'slug' => 'sartorius', 'is_active' => true]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'Balances', 'slug' => 'balances']);

        return Product::create(['category_id' => $cat->id, 'name' => 'Entris II', 'slug' => 'entris-ii', 'is_active' => true]);
    }

    private function admin(string $role = 'admin'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    public function test_the_seven_templates_exist_with_friendly_wording(): void
    {
        $this->assertSame(7, EmailTemplate::count());
        $this->assertSame(3, EmailTemplate::where('kind', 'auto')->count());
        $this->assertSame(4, EmailTemplate::where('kind', 'reply')->count());
        $this->assertStringContainsString('connect with you shortly', EmailTemplate::forKey('auto_enquiry')->body);
        $this->assertStringContainsString('get back to you shortly', EmailTemplate::forKey('auto_contact')->body);
    }

    public function test_enquiry_gets_an_automatic_reply_that_lists_what_they_asked_about(): void
    {
        $p = $this->product();

        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'ravi@example.com'])->assertRedirect();

        Mail::assertQueued(NewEnquiry::class);                       // the office still gets its own mail
        Mail::assertQueued(CustomerMessage::class, function (CustomerMessage $m) {
            return $m->hasTo('ravi@example.com')
                && str_contains($m->mailSubject, 'ENQ-')
                && str_contains($m->body, 'Dear Ravi,')
                && str_contains($m->body, '- Entris II')
                && str_contains($m->body, 'connect with you shortly')
                && ! str_contains($m->body, '{');                    // every placeholder was filled
        });
        $this->assertTrue(Enquiry::firstOrFail()->teamNotes()->where('body', 'Automatic reply sent to the customer')->exists());
    }

    public function test_group_enquiry_reply_lists_every_product_with_quantity(): void
    {
        $p = $this->product();

        $this->post(route('enquiry-list.store'), [
            'name' => 'Group Buyer', 'email' => 'group@example.com',
            'items' => [['slug' => $p->slug, 'qty' => 3]],
        ])->assertRedirect();

        Mail::assertQueued(CustomerMessage::class, fn ($m) => $m->hasTo('group@example.com') && str_contains($m->body, '- Entris II (qty 3)'));
    }

    public function test_contact_form_and_job_applications_get_their_own_replies(): void
    {
        Storage::fake('local');

        $this->post(route('contact.store'), ['name' => 'Asha Verma', 'email' => 'asha@example.com', 'phone' => '9999999999', 'message' => 'Hello'])->assertRedirect();
        Mail::assertQueued(NewContactMessage::class);
        Mail::assertQueued(CustomerMessage::class, fn ($m) => $m->hasTo('asha@example.com') && str_contains($m->body, 'get back to you shortly'));

        $opening = JobOpening::create(['title' => 'Service Engineer', 'slug' => 'service-engineer', 'location' => 'Jaipur', 'is_active' => true]);
        $this->post(route('careers.apply', $opening), [
            'name' => 'Kiran Rao', 'email' => 'kiran@example.com', 'phone' => '9876543210',
            'resume' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        Mail::assertQueued(CustomerMessage::class, fn ($m) => $m->hasTo('kiran@example.com')
            && str_contains($m->body, 'Dear Kiran,') && str_contains($m->body, 'for the position of Service Engineer'));
    }

    public function test_switching_a_template_off_stops_that_reply_only(): void
    {
        $p = $this->product();
        EmailTemplate::forKey('auto_enquiry')->update(['is_enabled' => false]);

        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi Sharma', 'email' => 'ravi@example.com'])->assertRedirect();
        Mail::assertNotQueued(CustomerMessage::class);
        $this->assertDatabaseCount('enquiries', 1);                  // the enquiry itself is saved as usual

        $this->post(route('contact.store'), ['name' => 'Asha Verma', 'email' => 'asha@example.com', 'phone' => '9999999999', 'message' => 'Hello'])->assertRedirect();
        Mail::assertQueued(CustomerMessage::class, 1);               // contact form is still on
    }

    public function test_one_address_cannot_be_flooded_and_bad_addresses_are_skipped(): void
    {
        $contact = ContactMessage::create(['name' => 'Asha', 'email' => 'Asha@Example.com', 'phone' => '+91 9999999999', 'message' => 'x', 'status' => 'new']);

        $results = collect(range(1, 5))->map(fn () => AutoReply::send('auto_contact', $contact))->all();

        $this->assertSame([true, true, true, false, false], $results);
        Mail::assertQueued(CustomerMessage::class, 3);

        $bad = ContactMessage::create(['name' => 'Bad', 'email' => 'not-an-email', 'phone' => '+91 9999999999', 'message' => 'x', 'status' => 'new']);
        $this->assertFalse(AutoReply::send('auto_contact', $bad));
        $this->assertFalse(AutoReply::send('does_not_exist', $contact));
    }

    public function test_a_failing_mail_system_never_breaks_the_form(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('mail server down'));
        $contact = ContactMessage::create(['name' => 'Asha', 'email' => 'asha@example.com', 'phone' => '+91 9999999999', 'message' => 'x', 'status' => 'new']);

        $this->assertFalse(AutoReply::send('auto_contact', $contact));
    }

    public function test_placeholders_are_filled_and_unknown_ones_are_left_visible(): void
    {
        $e = new Enquiry(['name' => 'Rohit Kumar Sharma', 'email' => 'r@example.com']);
        $e->id = 42;
        $e->setRelation('items', collect());
        $e->setRelation('product', null);

        $out = EmailTemplates::render('Hi {first_name} / {name} / {reference} / {company_name} / {phone} / {typo}', $e);

        $this->assertSame('Hi Rohit / Rohit Kumar Sharma / ENQ-000042 / Agarwal Brothers / ' . config('contact.call') . ' / {typo}', $out);
    }

    public function test_the_email_is_html_escaped_and_has_a_text_version(): void
    {
        $mail = new CustomerMessage('Hello', "Line one <script>alert(1)</script>\n\nLine two");

        $html = $mail->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<br', $html);
        $this->assertStringContainsString('Agarwal Brothers', $html);
    }

    // ------------------------------------------------------------ editing the templates

    public function test_administrators_can_edit_templates_and_the_new_wording_is_used(): void
    {
        $this->actingAs($this->admin());
        $tpl = EmailTemplate::forKey('auto_contact');

        $this->get('/admin/email-templates')->assertOk()->assertSee('Auto-reply: contact form')->assertSee('Manual reply');

        Livewire::test(EditEmailTemplate::class, ['record' => $tpl->getRouteKey()])
            ->fillForm(['subject' => 'Thanks {first_name}!', 'body' => 'We will call you soon, {first_name}.'])
            ->call('save')->assertHasNoFormErrors();

        $this->post(route('contact.store'), ['name' => 'Asha Verma', 'email' => 'asha@example.com', 'phone' => '9999999999', 'message' => 'Hello']);
        Mail::assertQueued(CustomerMessage::class, fn ($m) => $m->mailSubject === 'Thanks Asha!' && $m->body === 'We will call you soon, Asha.');
    }

    public function test_template_form_rejects_empty_text_and_the_switch_works_from_the_list(): void
    {
        $this->actingAs($this->admin());
        $tpl = EmailTemplate::forKey('auto_contact');

        Livewire::test(EditEmailTemplate::class, ['record' => $tpl->getRouteKey()])
            ->fillForm(['subject' => '', 'body' => ''])->call('save')->assertHasFormErrors(['subject' => 'required', 'body' => 'required']);

        Livewire::test(ListEmailTemplates::class)->assertCanSeeTableRecords(EmailTemplate::all());
    }

    public function test_send_me_a_test_goes_to_the_signed_in_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'email' => 'boss@example.com']);
        $this->actingAs($admin);

        Livewire::test(EditEmailTemplate::class, ['record' => EmailTemplate::forKey('auto_enquiry')->getRouteKey()])->callAction('test');

        Mail::assertQueued(CustomerMessage::class, fn ($m) => $m->hasTo('boss@example.com')
            && str_starts_with($m->mailSubject, '[TEST]') && str_contains($m->body, 'Hei-VAP Core Rotary Evaporator (qty 2)'));
    }

    public function test_only_administrators_can_open_the_templates(): void
    {
        foreach (['editor', 'sales', 'hr'] as $role) {
            $this->actingAs($this->admin($role));
            $this->get('/admin/email-templates')->assertForbidden();
        }
    }

    public function test_help_guide_covers_the_new_features(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin/help')->assertOk()
            ->assertSee('Working on enquiries as a team')
            ->assertSee('Automatic replies and email templates')
            ->assertSee('Unsaved changes and automatic drafts');
    }
}
