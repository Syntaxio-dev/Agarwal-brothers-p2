<?php

namespace Tests\Feature;

use App\Mail\NewContactMessage;
use App\Mail\NewEnquiry;
use App\Models\Brand;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Enquiry;
use App\Models\Insight;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    private function product(bool $brandActive = true, bool $active = true): Product
    {
        $brand = Brand::create(['name' => 'Acme', 'slug' => 'acme-' . uniqid(), 'is_active' => $brandActive]);
        $cat = Category::create(['brand_id' => $brand->id, 'name' => 'HPLC', 'slug' => 'hplc-' . uniqid()]);

        return Product::create(['category_id' => $cat->id, 'name' => 'LC-1', 'slug' => 'lc-' . uniqid(), 'is_active' => $active]);
    }

    public function test_main_pages_load(): void
    {
        $urls = ['/', '/our-story', '/contact-us', '/careers', '/verticals', '/application-resources',
            '/insights/blogs', '/insights/news-events', '/insights/webinars', '/search?q=hplc', '/sitemap.xml', '/robots.txt'];
        foreach ($urls as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_unknown_page_is_404(): void
    {
        $this->get('/nope-nothing-here')->assertNotFound();
    }

    public function test_inactive_brand_product_is_hidden(): void
    {
        $this->get(route('product.show', $this->product()))->assertOk();
        $this->get(route('product.show', $this->product(brandActive: false)))->assertNotFound();
    }

    public function test_unpublished_insight_is_not_public(): void
    {
        $i = Insight::create(['type' => 'blog', 'title' => 'Hidden', 'slug' => 'hidden', 'is_active' => false]);
        $this->get(route('insights.show', $i))->assertNotFound();
    }

    public function test_enquiry_is_saved_and_queued_for_email(): void
    {
        $p = $this->product();
        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Ravi', 'email' => 'ravi@example.com'])->assertRedirect();

        $this->assertDatabaseHas('enquiries', ['name' => 'Ravi', 'product_id' => $p->id]);
        Mail::assertQueued(NewEnquiry::class);
    }

    public function test_enquiry_rejects_honeypot_and_unlisted_products(): void
    {
        $p = $this->product();
        $this->post(route('enquiry.store'), ['product_id' => $p->id, 'name' => 'Bot', 'email' => 'b@example.com', 'website' => 'spam'])->assertSessionHasErrors('website');

        $hidden = $this->product(brandActive: false);
        $this->post(route('enquiry.store'), ['product_id' => $hidden->id, 'name' => 'X', 'email' => 'x@example.com'])->assertSessionHasErrors('product_id');

        $this->assertSame(0, Enquiry::count());
        Mail::assertNothingQueued();
    }

    public function test_contact_form_saves_and_queues_mail(): void
    {
        $this->post(route('contact.store'), ['name' => 'Asha', 'email' => 'a@example.com', 'phone' => '9999999999', 'message' => 'Hello'])->assertRedirect();

        $this->assertSame(1, ContactMessage::count());
        Mail::assertQueued(NewContactMessage::class);
    }

    public function test_contact_form_requires_fields(): void
    {
        $this->post(route('contact.store'), [])->assertSessionHasErrors(['name', 'email', 'phone', 'message']);
    }

    public function test_search_ignores_inactive_brand_products(): void
    {
        $hidden = $this->product(brandActive: false);
        $shown = $this->product();

        $this->get('/search?q=LC-1')->assertOk()
            ->assertSee(route('product.show', $shown), false)
            ->assertDontSee(route('product.show', $hidden), false);
    }
}
